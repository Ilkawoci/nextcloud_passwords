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
use OCA\PasswordFolders\Exception\ApiException;
use OCA\PasswordFolders\Helper\Settings\ShareSettingsHelper;
use OCA\PasswordFolders\Services\EncryptionService;
use OCA\PasswordFolders\Services\LoggingService;
use OCA\PasswordFolders\Services\Object\FolderRevisionService;
use OCA\PasswordFolders\Services\Object\FolderService;
use OCA\PasswordFolders\Services\Object\PasswordRevisionService;
use OCA\PasswordFolders\Services\Object\PasswordService;
use OCA\PasswordFolders\Services\EnvironmentService;
use OCA\PasswordFolders\Services\Object\ShareService;
use OCP\AppFramework\Http;

/**
 * Class CreateFolderShareHelper
 *
 * Validates a folder share and materializes the folder tree in the account of
 * the receiver.
 *
 * @package OCA\PasswordFolders\Helper\Sharing
 */
class CreateFolderShareHelper {

    const int MAX_DEPTH = 32;

    /**
     * @var array<string,bool>
     */
    protected array $visited = [];

    /**
     * CreateFolderShareHelper constructor.
     *
     * @param ShareService            $modelService
     * @param ShareSettingsHelper     $shareSettings
     * @param FolderService           $folderService
     * @param FolderRevisionService   $folderRevisionService
     * @param PasswordService         $passwordService
     * @param PasswordRevisionService $passwordRevisionService
     * @param LoggingService          $logger
     */
    public function __construct(
        protected ShareService            $modelService,
        protected ShareSettingsHelper     $shareSettings,
        protected FolderService           $folderService,
        protected FolderRevisionService   $folderRevisionService,
        protected PasswordService         $passwordService,
        protected PasswordRevisionService $passwordRevisionService,
        protected LoggingService          $logger,
        protected EnvironmentService      $environment
    ) {
    }

    /**
     * Share a folder and all of its children with a user
     *
     * @param string   $folderUuid
     * @param string   $receiver
     * @param string   $type
     * @param int|null $expires
     * @param bool     $editable
     * @param bool     $shareable
     *
     * @return Share
     * @throws ApiException
     * @throws Exception
     */
    public function createFolderShare(
        string $folderUuid,
        string $receiver,
        string $type = 'user',
        ?int   $expires = null,
        bool   $editable = false,
        bool   $shareable = false
    ): Share {
        $expires = $this->checkExpires($expires);
        $this->checkType($type);

        /** @var Folder $folder */
        $folder = $this->folderService->findByUuid($folderUuid);
        if($folder->getUuid() === FolderService::BASE_FOLDER_UUID) {
            throw new ApiException('Can not share base folder', Http::STATUS_BAD_REQUEST);
        }
        if($folder->getUserId() !== $this->environment->getUserId()) {
            throw new ApiException('Folder does not belong to current user', Http::STATUS_FORBIDDEN);
        }

        /** @var FolderRevision $revision */
        $revision = $this->folderRevisionService->findCurrentRevisionByModel($folder->getUuid(), false);
        $this->checkFolderShareable($folder, $revision);

        if($folder->getShareId() !== null) {
            [$editable, $expires] = $this->checkSourceShare($folder, $editable, $expires);
        }

        if($receiver === $folder->getUserId()) {
            throw new ApiException('Invalid receiver uid', Http::STATUS_BAD_REQUEST);
        }
        if($this->modelService->findBySourceFolderAndReceiver($folder->getUuid(), $receiver) !== null) {
            throw new ApiException('Folder already shared with user', Http::STATUS_BAD_REQUEST);
        }

        $problems = $this->validateTree($folder->getUuid());
        if(!empty($problems)) {
            throw new ApiException(
                'The folder contains elements which can not be shared: '.implode(', ', array_slice($problems, 0, 5)),
                420
            );
        }

        $share = $this->modelService->createFolderShare($folder->getUuid(), $receiver, $type, $editable, $expires, $shareable);
        $this->modelService->save($share);

        $this->setHasShares($folder->getUuid());

        return $share;
    }

    /**
     * @param int|null $expires
     *
     * @return int|null
     * @throws ApiException
     */
    protected function checkExpires(?int $expires): ?int {
        if(empty($expires)) return null;
        if($expires < time()) throw new ApiException('Invalid expiration date', Http::STATUS_BAD_REQUEST);

        return $expires;
    }

    /**
     * @param string $type
     *
     * @throws ApiException
     */
    protected function checkType(string $type): void {
        if($type !== Share::TYPE_USER) throw new ApiException('Invalid share type', Http::STATUS_BAD_REQUEST);
    }

    /**
     * @param Folder         $folder
     * @param FolderRevision $revision
     *
     * @throws ApiException
     */
    protected function checkFolderShareable(Folder $folder, FolderRevision $revision): void {
        if($revision->isTrashed() || $revision->isDeleted()) {
            throw new ApiException('Trashed folders can not be shared', Http::STATUS_BAD_REQUEST);
        }
        if($revision->isHidden()) {
            throw new ApiException('Hidden folders can not be shared', 420);
        }
        if($revision->getCseType() !== EncryptionService::CSE_ENCRYPTION_NONE) {
            throw new ApiException('End-to-end encrypted folders can not be shared', 420);
        }
    }

    /**
     * Limit the permissions by the parent share when re-sharing
     *
     * @param Folder   $folder
     * @param bool     $editable
     * @param int|null $expires
     *
     * @return array
     * @throws ApiException
     * @throws Exception
     */
    protected function checkSourceShare(Folder $folder, bool $editable, ?int $expires): array {
        /** @var Share $sourceShare */
        $sourceShare = $this->modelService->findByUuid($folder->getShareId());
        if(!$sourceShare->isShareable() || !$this->shareSettings->get('resharing')) {
            throw new ApiException('Folder not shareable', Http::STATUS_FORBIDDEN);
        }
        if(!$sourceShare->isEditable()) $editable = false;
        if($sourceShare->getExpires() !== null) {
            if($expires === null || $expires > $sourceShare->getExpires()) {
                $expires = $sourceShare->getExpires();
            }
        }

        return [$editable, $expires];
    }

    /**
     * Check the whole tree for elements that can not be shared
     *
     * @param string $rootUuid
     *
     * @return string[]
     * @throws Exception
     */
    protected function validateTree(string $rootUuid): array {
        $this->visited = [];
        $problems      = [];
        $queue         = [[$rootUuid, 0]];

        while(!empty($queue)) {
            [$folderUuid, $depth] = array_shift($queue);
            if(isset($this->visited[ $folderUuid ])) continue;
            $this->visited[ $folderUuid ] = true;

            if($depth > self::MAX_DEPTH) {
                $problems[] = 'folder tree too deep';

                continue;
            }

            /** @var FolderRevision $revision */
            $revision = $this->folderRevisionService->findCurrentRevisionByModel($folderUuid, true);
            if($revision->getCseType() !== EncryptionService::CSE_ENCRYPTION_NONE) {
                $problems[] = sprintf('folder "%s" is end-to-end encrypted', $revision->getLabel());
            }
            if($revision->isHidden()) {
                $problems[] = sprintf('folder "%s" is hidden', $revision->getLabel());
            }
            if($revision->isTrashed()) {
                $problems[] = sprintf('folder "%s" is in the trash', $revision->getLabel());
            }

            foreach($this->passwordService->findByFolder($folderUuid) as $password) {
                /** @var Password $password */
                if($password->getShareId() !== null) continue;

                /** @var PasswordRevision $passwordRevision */
                $passwordRevision = $this->passwordRevisionService->findCurrentRevisionByModel($password->getUuid(), false);
                if($passwordRevision->getCseType() !== EncryptionService::CSE_ENCRYPTION_NONE) {
                    $problems[] = sprintf('password "%s" is end-to-end encrypted', $passwordRevision->getLabel());
                } else if($passwordRevision->isHidden()) {
                    $problems[] = sprintf('password "%s" is hidden', $passwordRevision->getLabel());
                } else if($passwordRevision->isTrashed()) {
                    $problems[] = sprintf('password "%s" is in the trash', $passwordRevision->getLabel());
                }
            }

            foreach($this->folderService->findByParent($folderUuid) as $child) {
                $queue[] = [$child->getUuid(), $depth + 1];
            }
        }

        return $problems;
    }

    /**
     * Mark the folder as shared
     *
     * @param string $folderUuid
     *
     * @throws Exception
     */
    protected function setHasShares(string $folderUuid): void {
        /** @var Folder $folder */
        $folder = $this->folderService->findByUuid($folderUuid);
        if(!$folder->hasShares()) {
            $folder->setHasShares(true);
            $this->folderService->save($folder);
        }
    }
}
