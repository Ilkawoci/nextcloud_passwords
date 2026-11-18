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

use OCA\PasswordFolders\Db\TagMapper;
use OCA\PasswordFolders\Services\EncryptionService;
use OCA\PasswordFolders\Services\EnvironmentService;
use OCA\PasswordFolders\Services\Object\TagRevisionService;

/**
 * Class TagRevisionRepairHelper
 *
 * @package OCA\PasswordFolders\Helper\Repair
 */
class TagRevisionRepair extends AbstractRevisionRepair {

    /**
     * @var string
     */
    protected string $objectName = 'tag';

    /**
     * TagRevisionRepair constructor.
     *
     * @param TagMapper          $modelMapper
     * @param TagRevisionService $revisionService
     * @param EncryptionService  $encryption
     * @param EnvironmentService $environment
     */
    public function __construct(TagMapper $modelMapper, TagRevisionService $revisionService, EncryptionService $encryption, EnvironmentService $environment) {
        parent::__construct($modelMapper, $revisionService, $encryption, $environment);
    }
}