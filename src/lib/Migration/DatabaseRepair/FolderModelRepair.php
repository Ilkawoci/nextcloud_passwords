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

namespace OCA\PasswordFolders\Migration\DatabaseRepair;

use OCA\PasswordFolders\Services\Object\FolderRevisionService;
use OCA\PasswordFolders\Services\Object\FolderService;

/**
 * Class FolderModelRepair
 *
 * @package OCA\PasswordFolders\Migration\DatabaseRepair
 */
class FolderModelRepair extends AbstractModelRepair {

    /**
     * @var string
     */
    protected string $objectName = 'folder';

    /**
     * FolderModelRepair constructor.
     *
     * @param FolderService         $modelService
     * @param FolderRevisionService $revisionService
     */
    public function __construct(FolderService $modelService, FolderRevisionService $revisionService) {
        parent::__construct($modelService, $revisionService);
    }
}