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
use OCA\PasswordFolders\Events\Folder\BeforeFolderDeletedEvent;
use OCA\PasswordFolders\Services\Object\FolderService;
use OCA\PasswordFolders\Services\Object\PasswordService;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;

/**
 * Class BeforeFolderDeletedListener
 *
 * @package OCA\PasswordFolders\EventListener\Folder
 */
class BeforeFolderDeletedListener implements IEventListener {

    /**
     * @var FolderService
     */
    protected FolderService $folderService;

    /**
     * @var PasswordService
     */
    protected PasswordService $passwordService;

    /**
     * BeforeFolderDeletedListener constructor.
     *
     * @param FolderService   $folderService
     * @param PasswordService $passwordService
     */
    public function __construct(FolderService $folderService, PasswordService $passwordService) {
        $this->folderService = $folderService;
        $this->passwordService = $passwordService;
    }

    /**
     * @param Event $event
     *
     * @throws Exception
     */
    public function handle(Event $event): void {
        if(!($event instanceof BeforeFolderDeletedEvent)) return;
        $folder = $event->getFolder();

        $folders = $this->folderService->findByParent($folder->getUuid());
        foreach($folders as $subFolder) {
            $this->folderService->delete($subFolder);
        }

        $passwords = $this->passwordService->findByFolder($folder->getUuid());
        foreach($passwords as $password) {
            $this->passwordService->delete($password);
        }
    }
}