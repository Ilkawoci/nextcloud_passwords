<?php
/*
 * @copyright 2021 Passwords App
 *
 * @author Marius David Wieschollek
 * @license AGPL-3.0
 *
 * This file is part of the Passwords App
 * created by Marius David Wieschollek.
 */

namespace OCA\PasswordFolders\Helper\Sharing;

use OCA\PasswordFolders\Db\Password;
use OCA\PasswordFolders\Db\Share;
use OCA\PasswordFolders\Exception\ApiException;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

/**
 * Class UpdatePasswordShareHelper
 *
 * @package OCA\PasswordFolders\Helper\Sharing
 */
class UpdatePasswordShareHelper extends CreatePasswordShareHelper {

    /**
     * @param Share    $share
     * @param int|null $expires
     * @param bool     $editable
     * @param bool     $shareable
     *
     * @return Share
     * @throws ApiException
     * @throws DoesNotExistException
     * @throws MultipleObjectsReturnedException
     */
    public function updatePasswordShare(Share $share, ?int $expires, bool $editable, bool $shareable): Share {
        /** @var Password $model */
        $model = $this->passwordModelService->findByUuid($share->getSourcePassword());
        if($model->getShareId()) [$editable, $expires] = $this->checkSourceShare($share->getSourcePassword(), $model, $editable, $expires);

        $share->setExpires($expires);
        $share->setEditable($editable);
        $share->setShareable($shareable);
        $share->setSourceUpdated(true);
        return $this->modelService->save($share);
    }
}