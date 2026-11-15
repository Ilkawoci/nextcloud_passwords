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

namespace OCA\Passwords\Helper\Sharing;

use Exception;
use OCA\Passwords\Db\Folder;
use OCA\Passwords\Db\FolderRevision;
use OCA\Passwords\Db\Password;
use OCA\Passwords\Db\PasswordRevision;
use OCA\Passwords\Db\Share;
use OCA\Passwords\Services\LoggingService;
use OCA\Passwords\Services\Object\FolderRevisionService;
use OCA\Passwords\Services\Object\FolderService;
use OCA\Passwords\Services\Object\PasswordRevisionService;
use OCA\Passwords\Services\Object\PasswordService;
use OCA\Passwords\Services\Object\ShareService;
use OCP\AppFramework\Db\DoesNotExistException;

/**
 * Class FolderShareMaterializer
 *
 * Copies a shared folder tree into the account of the receiver. Every copied
 * folder and every copied password gets its own share row so that the existing
 * synchronization cron keeps working.
 *
 * @package OCA\Passwords\Helper\Sharing
 */
class FolderShareMaterializer {

    const int MAX_DEPTH = 32;

    /**
     * @var array<string,bool>
     */
    protected array $visited = [];

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
    public function materialize(Share $rootShare): void {
        if(!$rootShare->isFolderShare()) return;

        $this->visited = [];
        $receiver      = $rootShare->getReceiver();

        $queue = [[
            'source'   => $rootShare->getSourceFolder(),
            'parent'   => FolderService::BASE_FOLDER_UUID,
            'share'    => $rootShare,
            'depth'    => 0,
            'isRoot'   => true,
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

            $targetFolder = $this->createTargetFolder($sourceFolder, $sourceRevision, $item['parent'], $receiver, $folderShare);

            $folderShare->setTargetFolder($targetFolder->getUuid());
            $folderShare->setSourceUpdated(false);
            $this->shareService->save($folderShare);

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

            $this->createPasswordShares($passwords, $targetFolder->getUuid(), $rootShare, $receiver);

            if($item['depth'] >= self::MAX_DEPTH) {
                $this->logger->error("Folder share {$rootShare->getUuid()} exceeds maximum depth, remaining folders were not copied");
                continue;
            }

            foreach($childFolders as $childFolder) {
                /** @var Folder $childFolder */
                $childShare = $this->createChildFolderShare($childFolder->getUuid(), $rootShare, $receiver);

                $queue[] = [
                    'source' => $childFolder->getUuid(),
                    'parent' => $targetFolder->getUuid(),
                    'share'  => $childShare,
                    'depth'  => $item['depth'] + 1,
                    'isRoot' => false,
                ];
            }
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

            try {
                /** @var PasswordRevision $revision */
                $revision = $this->passwordRevisionService->findCurrentRevisionByModel($password->getUuid(), false);
            } catch(DoesNotExistException $e) {
                continue;
            }

            if($revision->getCseType() !== \OCA\Passwords\Services\EncryptionService::CSE_ENCRYPTION_NONE) continue;
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
