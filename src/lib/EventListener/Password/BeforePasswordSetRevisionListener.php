<?php
/*
 * @copyright 2023 Passwords App
 *
 * @author Marius David Wieschollek
 * @license AGPL-3.0
 *
 * This file is part of the Passwords App
 * created by Marius David Wieschollek.
 */

namespace OCA\PasswordFolders\EventListener\Password;

use Exception;
use OCA\PasswordFolders\Db\PasswordRevision;
use OCA\PasswordFolders\Events\Password\BeforePasswordSetRevisionEvent;
use OCA\PasswordFolders\Services\PasswordSecurityCheckService;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\EventDispatcher\Event;

/**
 * Class BeforePasswordSetRevisionListener
 *
 * @package OCA\PasswordFolders\EventListener\Password
 */
class BeforePasswordSetRevisionListener extends AbstractPasswordListener {

    /**
     * @param Event $event
     *
     * @throws DoesNotExistException
     * @throws MultipleObjectsReturnedException
     * @throws Exception
     */
    public function handle(Event $event): void {
        if(!($event instanceof BeforePasswordSetRevisionEvent)) return;
        $password    = $event->getPassword();
        $newRevision = $event->getRevision();

        if($password->getRevision() === null) {
            $this->checkSecurityStatus($newRevision);
            $this->revisionService->save($newRevision);

            return;
        }

        if($password->isEditable() && ($password->getShareId() || $password->hasShares())) {
            $this->updateShares($password);
        }

        /** @var PasswordRevision $oldRevision */
        $oldRevision = $this->revisionService->findByUuid($password->getRevision());
        if($oldRevision->getHidden() !== $newRevision->getHidden()) {
            $this->updateRelations($password, $newRevision);
        }

        if($newRevision->getStatus() === PasswordSecurityCheckService::STATUS_OK || PasswordSecurityCheckService::STATUS_UNKNOWN) {
            if($newRevision->getHash() === $oldRevision->getHash()) {
                $newRevision->setStatus($oldRevision->getStatus());
                $newRevision->setStatusCode($oldRevision->getStatusCode());
            } else {
                $this->checkSecurityStatus($newRevision);
            }
            $this->revisionService->save($newRevision);
        }

        if($oldRevision->getStatusCode() === PasswordSecurityCheckService::STATUS_CODE_DUPLICATE && $newRevision->getHash() !== $oldRevision->getHash()) {
            $this->updateDuplicateStatus([$oldRevision->getHash()]);
        }
    }
}