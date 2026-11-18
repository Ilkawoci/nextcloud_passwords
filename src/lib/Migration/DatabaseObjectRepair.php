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

namespace OCA\PasswordFolders\Migration;

use Exception;
use OCA\PasswordFolders\Migration\DatabaseRepair\FolderModelRepair;
use OCA\PasswordFolders\Migration\DatabaseRepair\FolderRevisionRepair;
use OCA\PasswordFolders\Migration\DatabaseRepair\PasswordModelRepair;
use OCA\PasswordFolders\Migration\DatabaseRepair\PasswordRevisionRepair;
use OCA\PasswordFolders\Migration\DatabaseRepair\PasswordTagRelationRepair;
use OCA\PasswordFolders\Migration\DatabaseRepair\ShareRepair;
use OCA\PasswordFolders\Migration\DatabaseRepair\TagModelRepair;
use OCA\PasswordFolders\Migration\DatabaseRepair\TagRevisionRepair;
use OCA\PasswordFolders\Services\ConfigurationService;
use OCP\Migration\IOutput;
use OCP\Migration\IRepairStep;

/**
 * Class DatabaseObjectRepair
 *
 * @package OCA\PasswordFolders\Migration
 */
class DatabaseObjectRepair implements IRepairStep {

    /**
     * DatabaseObjectRepair constructor.
     *
     * @param ConfigurationService      $config
     * @param ShareRepair               $shareRepair
     * @param TagModelRepair            $tagModelRepair
     * @param FolderModelRepair         $folderModelRepair
     * @param TagRevisionRepair         $tagRevisionRepair
     * @param PasswordModelRepair       $passwordModelRepair
     * @param FolderRevisionRepair      $folderRevisionRepair
     * @param PasswordRevisionRepair    $passwordRevisionRepair
     * @param PasswordTagRelationRepair $passwordTagRelationRepair
     */
    public function __construct(
        protected ConfigurationService      $config,
        protected ShareRepair               $shareRepair,
        protected TagModelRepair            $tagModelRepair,
        protected FolderModelRepair         $folderModelRepair,
        protected TagRevisionRepair         $tagRevisionRepair,
        protected PasswordModelRepair       $passwordModelRepair,
        protected FolderRevisionRepair      $folderRevisionRepair,
        protected PasswordRevisionRepair    $passwordRevisionRepair,
        protected PasswordTagRelationRepair $passwordTagRelationRepair
    ) {
    }

    /**
     * Returns the step's name
     *
     * @return string
     * @since 9.1.0
     */
    public function getName(): string {
        return 'Repair Passwords Database Objects';
    }

    /**
     * Run repair step.
     * Must throw exception on error.
     *
     * @param IOutput $output
     *
     * @throws Exception in case of failure
     * @since 9.1.0
     */
    public function run(IOutput $output): void {
        $this->tagRevisionRepair->run($output);
        $this->folderRevisionRepair->run($output);
        $this->passwordRevisionRepair->run($output);
        $this->tagModelRepair->run($output);
        $this->folderModelRepair->run($output);
        $this->passwordModelRepair->run($output);
        $this->passwordTagRelationRepair->run($output);
        $this->shareRepair->run($output);
    }
}