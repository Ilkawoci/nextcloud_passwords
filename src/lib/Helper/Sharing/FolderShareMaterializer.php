<?php
/*
 * @copyright 2026 Passwords App
 *
 * @author Marius David Wieschollek
 * @license AGPL-3.0
 *
 * This file is part of the Passwords App
 * created by Marius David Wieschollek.
 */

namespace OCA\PasswordFolders\Helper\Sharing;

use Exception;
use OCA\PasswordFolders\Db\Folder;
use OCA\PasswordFolders\Db\FolderRevision;
use OCA\PasswordFolders\Db\Password;
use OCA\PasswordFolders\Db\PasswordRevision;
use OCA\PasswordFolders\Db\Share;
use OCA\PasswordFolders\Services\LoggingService;
use OCA\PasswordFolders\Services\Object\FolderRevisionService;
use OCA\PasswordFolders\Services\Object\FolderService;
use OCA\PasswordFolders\Services\Object\PasswordRevisionService;
use OCA\PasswordFolders\Services\Object\PasswordService;
use OCA\PasswordFolders\Services\Object\ShareService;
use OCP\AppFramework\Db\DoesNotExistException;

/**
 * Class FolderShareMaterializer
 *
 * Copies a shared folder tree into the account of the receiver. Every copied
 * folder and every copied password gets its own share row so that the existing
 * synchronization cron keeps working.
 *
 * @package OCA\PasswordFolders\Helper\Sharing
 */
class FolderShareMaterializer {

    const int MAX_DEPTH = 32;

    /**
     * @var array<string,bool>
     */
    protected array $visited = [];

    /**
     * Existing child shares of the current tree, indexed by source folder uuid
     *
     * @var array<string,Share>
     */
    protected array $existingFolders = [];

    /**
     * Source password uuids that are already part of the current tree
     *
     * @var array<string,bool>
     */
    protected array $existingPasswords = [];

    /**
     * FolderShareMaterializer constructor.
     *
     * @param ShareService            $shareService
     * @param FolderService           $folderService
     * @param FolderRevisionService   $folderRevisionService
     * @param PasswordService         $passwordService
     * @param PasswordRevisionService $passwordRevisionService
     * @param LoggingService          $logger
     */
    public function __construct(
        protected ShareService            $shareService,
        protected FolderService           $folderService,
        protected FolderRevisionService   $folderRevisionService,
        protected PasswordService         $passwordService,
        protected PasswordRevisionService $passwordRevisionService,
        protected LoggingService          $logger
    ) {
    }

    /**
     * Copy the source tree of a folder share to the receiver
     *
     * @param Share $rootShare
     *
     * @throws Exception
     */
    public function materialize(Share $rootShare, bool $reuseExisting = false): void {
        if(!$rootShare->isFolderShare()) return;

        if(!$reuseExisting) {
            $this->existingFolders   = [];
            $this->existingPasswords = [];
        }

        $this->visited = [];
        $receiver      = $rootShare->getReceiver();

        $queue = [[
            'source'   => $rootShare->getSourceFolder(),
            'parent'   => FolderService::BASE_FOLDER_UUID,
            'share'    => $rootShare,
            'depth'    => 0,
            'isRoot'   => true,
            'target'   => $reuseExisting ? $rootShare->getTargetFolder():null,
        ]];

        while(!empty($queue)) {
            $item = array_shift($queue);
            /** @var Share $folderShare */
            $folderShare = $item['share'];

            try {
                /** @var Folder $sourceFolder */
                $sourceFolder = $this->folderService->findByUuid($item['source']);
                /** @var FolderRevision $sourceRevision */
                $sourceRevision = $this->folderRevisionService->findCurrentRevisionByModel($sourceFolder->getUuid(), true);
            } catch(DoesNotExistException $e) {
                $this->logger->error("Could not materialize folder share {$folderShare->getUuid()}, source folder {$item['source']} does not exist");
                continue;
            }

            if($sourceRevision->isTrashed()) continue;
            if(isset($this->visited[ $sourceFolder->getUuid() ])) continue;
            $this->visited[ $sourceFolder->getUuid() ] = true;

            $targetUuid = $this->resolveTargetFolder($item['target']);

            if($targetUuid === null) {
                $targetFolder = $this->createTargetFolder($sourceFolder, $sourceRevision, $item['parent'], $receiver, $folderShare);
                $targetUuid   = $targetFolder->getUuid();

                $folderShare->setTargetFolder($targetUuid);
                $folderShare->setSourceUpdated(false);
                $this->shareService->save($folderShare);
            }

            $childFolders = $this->folderService->findByParent($sourceFolder->getUuid());
            $passwords    = $this->passwordService->findByFolder($sourceFolder->getUuid());
            $this->logger->info(
                sprintf(
                    'Materializing folder %s for %s: %d folders, %d passwords',
                    $sourceFolder->getUuid(),
                    $receiver,
                    count($childFolders),
                    count($passwords)
                )
            );

            $this->createPasswordShares($passwords, $targetUuid, $rootShare, $receiver);

            if($item['depth'] >= self::MAX_DEPTH) {
                $this->logger->error("Folder share {$rootShare->getUuid()} exceeds maximum depth, remaining folders were not copied");
                continue;
            }

            foreach($childFolders as $childFolder) {
                /** @var Folder $childFolder */
                $childUuid  = $childFolder->getUuid();
                $childShare = $this->existingFolders[$childUuid] ?? null;

                if($childShare === null) {
                    $childShare = $this->createChildFolderShare($childUuid, $rootShare, $receiver);
                }

                $queue[] = [
                    'source' => $childUuid,
                    'parent' => $targetUuid,
                    'share'  => $childShare,
                    'depth'  => $item['depth'] + 1,
                    'isRoot' => false,
                    'target' => $childShare->getTargetFolder(),
                ];
            }
        }
    }

    /**
     * Add elements that were created in the source tree after the folder was shared
     *
     * @param Share $rootShare
     *
     * @throws Exception
     */
    public function syncNewChildren(Share $rootShare): void {
        if(!$rootShare->isFolderShare()) return;
        if($rootShare->getTargetFolder() === null) return;

        $this->existingFolders   = [];
        $this->existingPasswords = [];

        foreach($this->shareService->findByParentShare($rootShare->getUuid()) as $childShare) {
            if($childShare->isFolderShare()) {
                $this->existingFolders[$childShare->getSourceFolder()] = $childShare;
            } else if($childShare->getSourcePassword() !== null) {
                $this->existingPasswords[$childShare->getSourcePassword()] = true;
            }
        }

        $this->materialize($rootShare, true);
    }

    /**
     * Check if a previously created target folder still exists
     *
     * @param string|null $folderUuid
     *
     * @return string|null
     */
    protected function resolveTargetFolder(?string $folderUuid): ?string {
        if($folderUuid === null) return null;

        try {
            $this->folderService->findByUuid($folderUuid);

            return $folderUuid;
        } catch(DoesNotExistException $e) {
            return null;
        }
    }

    /**
     * Create the copy of a folder in the receivers account
     *
     * @param Folder         $sourceFolder
     * @param FolderRevision $sourceRevision
     * @param string         $targetParent
     * @param string         $receiver
     * @param Share          $folderShare
     *
     * @return Folder
     * @throws Exception
     */
    protected function createTargetFolder(
        Folder         $sourceFolder,
        FolderRevision $sourceRevision,
        string         $targetParent,
        string         $receiver,
        Share          $folderShare
    ): Folder {
        /** @var Folder $folder */
        $folder = $this->folderService->create();
        $folder->setUserId($receiver);
        $folder->setShareId($folderShare->getUuid());
        $folder->setHasShares(false);

        /** @var FolderRevision $revision */
        $revision = $this->folderRevisionService->create(
            $folder->getUuid(),
            $sourceRevision->getLabel(),
            $targetParent,
            '',
            $sourceRevision->getCseType(),
            $sourceRevision->getEdited(),
            false,
            false,
            $sourceRevision->isFavorite()
        );
        $revision->setUserId($receiver);
        $this->folderRevisionService->save($revision);
        $this->folderService->setRevision($folder, $revision);

        return $folder;
    }

    /**
     * Create a share row for a sub folder
     *
     * @param string $sourceFolderUuid
     * @param Share  $rootShare
     * @param string $receiver
     *
     * @return Share
     * @throws Exception
     */
    protected function createChildFolderShare(string $sourceFolderUuid, Share $rootShare, string $receiver): Share {
        $share = $this->shareService->createFolderShare(
            $sourceFolderUuid,
            $receiver,
            $rootShare->getType(),
            $rootShare->isEditable(),
            $rootShare->getExpires(),
            $rootShare->isShareable(),
            $rootShare->getUuid(),
            $rootShare->getUserId()
        );
        $this->shareService->save($share);

        return $share;
    }

    /**
     * Create share rows for all passwords inside a folder
     *
     * @param Password[] $passwords
     * @param string     $targetFolderUuid
     * @param Share      $rootShare
     * @param string     $receiver
     *
     * @throws Exception
     */
    protected function createPasswordShares(array $passwords, string $targetFolderUuid, Share $rootShare, string $receiver): void {
        foreach($passwords as $password) {
            /** @var Password $password */
            if($password->getShareId() !== null) continue;
            if($password->getUserId() !== $rootShare->getUserId()) continue;
            if(isset($this->existingPasswords[$password->getUuid()])) continue;

            try {
                /** @var PasswordRevision $revision */
                $revision = $this->passwordRevisionService->findCurrentRevisionByModel($password->getUuid(), false);
            } catch(DoesNotExistException $e) {
                continue;
            }

            if($revision->getCseType() !== \OCA\PasswordFolders\Services\EncryptionService::CSE_ENCRYPTION_NONE) continue;
            if($revision->isHidden() || $revision->isTrashed()) continue;

            $share = $this->shareService->create(
                $password->getUuid(),
                $receiver,
                $rootShare->getType(),
                $rootShare->isEditable(),
                $rootShare->getExpires(),
                $rootShare->isShareable(),
                $rootShare->getUserId()
            );
            $share->setParentShare($rootShare->getUuid());
            $share->setTargetFolder($targetFolderUuid);
            $this->shareService->save($share);

            if(!$password->hasShares()) {
                $password->setHasShares(true);
                $this->passwordService->save($password);
            }
        }
    }

    /**
     * Remove copies of elements that were deleted or moved out of a shared tree
     *
     * @param Share $rootShare
     *
     * @throws Exception
     */
    public function pruneTree(Share $rootShare): void {
        if(!$rootShare->isFolderShare()) return;
        if($rootShare->getTargetFolder() === null) return;

        $folderTargets  = [];
        $childShares    = $this->shareService->findByParentShare($rootShare->getUuid());
        $validFolders   = [];
        $validPasswords = [];

        $folderTargets[ $rootShare->getSourceFolder() ] = $rootShare->getTargetFolder();

        foreach($childShares as $childShare) {
            if($childShare->isFolderShare()) {
                $folderTargets[ $childShare->getSourceFolder() ] = $childShare->getTargetFolder();
            }
        }

        $queue = [$rootShare->getSourceFolder()];
        while(!empty($queue)) {
            $folderUuid = array_shift($queue);

            try {
                $folderRevision = $this->folderRevisionService->findCurrentRevisionByModel($folderUuid, false);
            } catch(DoesNotExistException $e) {
                continue;
            }
            if($folderRevision->isTrashed()) continue;

            $validFolders[$folderUuid] = true;

            foreach($this->folderService->findByParent($folderUuid) as $childFolder) {
                $queue[] = $childFolder->getUuid();
            }

            foreach($this->passwordService->findByFolder($folderUuid) as $password) {
                if($password->getShareId() !== null) continue;

                try {
                    $passwordRevision = $this->passwordRevisionService->findCurrentRevisionByModel($password->getUuid(), false);
                } catch(DoesNotExistException $e) {
                    continue;
                }
                if($passwordRevision->isTrashed() || $passwordRevision->isHidden()) continue;

                $validPasswords[ $password->getUuid() ] = $folderUuid;
            }
        }

        foreach($childShares as $childShare) {
            try {
                if($childShare->isFolderShare()) {
                    $sourceUuid = $childShare->getSourceFolder();
                    if(isset($validFolders[$sourceUuid])) continue;

                    $this->deleteMaterializedTree($childShare);
                } else {
                    $sourceUuid = $childShare->getSourcePassword();
                    if(!isset($validPasswords[$sourceUuid])) {
                        $this->deleteTargetPassword($childShare->getTargetPassword());
                    } else if($this->targetFolderChanged($childShare, $validPasswords[$sourceUuid], $folderTargets)) {
                        $this->deleteTargetPassword($childShare->getTargetPassword());
                    } else {
                        continue;
                    }
                }
            } catch(DoesNotExistException $e) {
                // target already gone
            }

            $this->shareService->delete($childShare);
        }
    }

    /**
     * Check if a shared password moved to another folder inside the tree
     *
     * @param Share  $childShare
     * @param string $sourceFolder
     * @param array  $folderTargets
     *
     * @return bool
     */
    protected function targetFolderChanged(Share $childShare, string $sourceFolder, array $folderTargets): bool {
        $expected = $folderTargets[$sourceFolder] ?? null;

        return $expected !== null && $childShare->getTargetFolder() !== $expected;
    }

    /**
     * Delete the copy of a password in the receivers account
     *
     * @param string|null $passwordUuid
     *
     * @throws Exception
     */
    protected function deleteTargetPassword(?string $passwordUuid): void {
        if($passwordUuid === null) return;

        try {
            $password = $this->passwordService->findByUuid($passwordUuid);
            $this->passwordService->delete($password);
        } catch(DoesNotExistException $e) {
            // already gone
        }
    }

    /**
     * Remove folder copies whose share row was deleted or is gone
     *
     * Needed because a share can be revoked in a request context, where the
     * receiver's folders are not visible to the sharing user.
     *
     * @throws Exception
     */
    public function deleteOrphanedTargets(): void {
        foreach($this->folderService->findOrphanedTargetFolders() as $folder) {
            $targetUuids = $this->collectTargetFolders($folder->getUuid());

            foreach($targetUuids as $targetUuid) {
                foreach($this->shareService->findByTargetFolder($targetUuid) as $share) {
                    $this->shareService->delete($share);
                }
            }

            $this->deleteTargetFolder($folder->getUuid());
        }
    }

    /**
     * @param string $folderUuid
     *
     * @return string[]
     */
    protected function collectTargetFolders(string $folderUuid): array {
        $folders = [$folderUuid];

        foreach($this->folderService->findByParent($folderUuid) as $childFolder) {
            $folders = [...$folders, ...$this->collectTargetFolders($childFolder->getUuid())];
        }

        return $folders;
    }

    /**
     * Delete all copies that belong to a folder share
     *
     * @param Share $rootShare
     *
     * @throws Exception
     */
    public function deleteMaterializedTree(Share $rootShare): void {
        $childShares = $this->shareService->findByParentShare($rootShare->getUuid());

        foreach($childShares as $childShare) {
            try {
                if($childShare->isFolderShare()) {
                    $this->deleteTargetFolder($childShare->getTargetFolder());
                } else if($childShare->getTargetPassword() !== null) {
                    /** @var Password $password */
                    $password = $this->passwordService->findByUuid($childShare->getTargetPassword());
                    $this->passwordService->delete($password);
                }
            } catch(DoesNotExistException $e) {
                // already gone
            }
            $this->shareService->delete($childShare);
        }

        $this->deleteTargetFolder($rootShare->getTargetFolder());
    }

    /**
     * Delete a target folder including all of its children
     *
     * @param string|null $folderUuid
     *
     * @throws Exception
     */
    protected function deleteTargetFolder(?string $folderUuid): void {
        if($folderUuid === null) return;

        try {
            /** @var Folder $folder */
            $folder = $this->folderService->findByUuid($folderUuid);
        } catch(DoesNotExistException $e) {
            return;
        }

        foreach($this->folderService->findByParent($folderUuid) as $child) {
            $this->deleteTargetFolder($child->getUuid());
        }

        foreach($this->passwordService->findByFolder($folderUuid) as $password) {
            $this->passwordService->delete($password);
        }

        $this->folderService->delete($folder);
    }
}
