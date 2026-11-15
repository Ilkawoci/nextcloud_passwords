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
use OCA\Passwords\Db\Share;
use OCA\Passwords\Exception\ApiException;
use OCA\Passwords\Services\Object\PasswordService;
use OCA\Passwords\Services\Object\ShareService;
use OCP\AppFramework\Http;

/**
 * Class UpdateFolderShareHelper
 *
 * @package OCA\Passwords\Helper\Sharing
 */
class UpdateFolderShareHelper {

    /**
     * UpdateFolderShareHelper constructor.
     *
     * @param ShareService   $shareService
     * @param PasswordService $passwordService
     */
    public function __construct(
        protected ShareService   $shareService,
        protected PasswordService $passwordService
    ) {
    }

    /**
     * Update a folder share and propagate the settings to all child shares
     *
     * @param Share    $share
     * @param int|null $expires
     * @param bool     $editable
     * @param bool     $shareable
     *
     * @return Share
     * @throws ApiException
     * @throws Exception
     */
    public function updateFolderShare(Share $share, ?int $expires, bool $editable, bool $shareable): Share {
        if($expires !== null && $expires < time()) {
            throw new ApiException('Invalid expiration date', Http::STATUS_BAD_REQUEST);
        }

        $share->setExpires($expires);
        $share->setEditable($editable);
        $share->setShareable($shareable);
        $share->setSourceUpdated(false);
        $share->setTargetUpdated(false);
        $this->shareService->save($share);

        foreach($this->shareService->findByParentShare($share->getUuid()) as $childShare) {
            $childShare->setEditable($editable);
            $childShare->setShareable($shareable);
            if($expires !== null && ($childShare->getExpires() === null || $childShare->getExpires() > $expires)) {
                $childShare->setExpires($expires);
            }
            $this->shareService->save($childShare);

            if(!$childShare->isFolderShare() && $childShare->getTargetPassword() !== null) {
                $this->setPasswordEditable($childShare->getTargetPassword(), $editable);
            }
        }

        return $share;
    }

    /**
     * @param string $passwordUuid
     * @param bool   $editable
     */
    protected function setPasswordEditable(string $passwordUuid, bool $editable): void {
        try {
            $password = $this->passwordService->findByUuid($passwordUuid);
            if($password->getEditable() !== $editable) {
                $password->setEditable($editable);
                $this->passwordService->save($password);
            }
        } catch(Exception $e) {
            // password was deleted, the cron will clean up the share
        }
    }
}
