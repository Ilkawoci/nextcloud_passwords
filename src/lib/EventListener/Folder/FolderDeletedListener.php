<?php
/*
 * @copyright 2020 Passwords App
 *
 * @author Marius David Wieschollek
 * @license AGPL-3.0
 *
 * This file is part of the Passwords App
 * created by Marius David Wieschollek.
 */

namespace OCA\PasswordFolders\EventListener\Folder;

use Exception;
use OCA\PasswordFolders\Db\FolderRevision;
use OCA\PasswordFolders\Events\Folder\FolderDeletedEvent;
use OCA\PasswordFolders\Services\Object\FolderRevisionService;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;

/**
 * Class FolderDeletedListener
 *
 * @package OCA\PasswordFolders\EventListener\Folder
 */
class FolderDeletedListener implements IEventListener {

    /**
     * @var FolderRevisionService
     */
    protected FolderRevisionService $revisionService;

    /**
     * FolderDeletedListener constructor.
     *
     * @param FolderRevisionService $revisionService
     */
    public function __construct(FolderRevisionService $revisionService) {
        $this->revisionService = $revisionService;
    }

    /**
     * @param Event $event
     *
     * @throws Exception
     */
    public function handle(Event $event): void {
        if(!($event instanceof FolderDeletedEvent)) return;
        $folder = $event->getFolder();

        /** @var FolderRevision[] $revisions */
        $revisions = $this->revisionService->findByModel($folder->getUuid());

        foreach($revisions as $revision) {
            $this->revisionService->delete($revision);
        }
    }
}