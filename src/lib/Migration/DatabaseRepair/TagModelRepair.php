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

use OCA\PasswordFolders\Services\Object\TagRevisionService;
use OCA\PasswordFolders\Services\Object\TagService;

/**
 * Class TagModelRepair
 *
 * @package OCA\PasswordFolders\Migration\DatabaseRepair
 */
class TagModelRepair extends AbstractModelRepair {

    /**
     * @var string
     */
    protected string $objectName = 'tag';

    /**
     * TagModelRepair constructor.
     *
     * @param TagService         $modelService
     * @param TagRevisionService $revisionService
     */
    public function __construct(TagService $modelService, TagRevisionService $revisionService) {
        parent::__construct($modelService, $revisionService);
    }
}