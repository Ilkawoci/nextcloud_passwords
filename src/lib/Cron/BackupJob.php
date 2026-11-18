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

namespace OCA\PasswordFolders\Cron;

use Exception;
use OCA\PasswordFolders\Services\BackupService;
use OCA\PasswordFolders\Services\ConfigurationService;
use OCA\PasswordFolders\Services\EnvironmentService;
use OCA\PasswordFolders\Services\LoggingService;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\Util;

/**
 * Class BackupJob
 *
 * @package OCA\PasswordFolders\Cron
 */
class BackupJob extends AbstractTimedJob {

    /**
     * BackupJob constructor.
     *
     * @param ITimeFactory         $time
     * @param LoggingService       $logger
     * @param EnvironmentService   $environment
     * @param BackupService        $backupService
     * @param ConfigurationService $config
     */
    public function __construct(
        ITimeFactory            $time,
        LoggingService          $logger,
        EnvironmentService      $environment,
        protected BackupService $backupService,
        ConfigurationService    $config
    ) {
        parent::__construct($time, $logger, $config, $environment);

        $interval = (int) $this->config->getAppValue('backup/interval', 86400);
        $this->setInterval($interval);
    }

    /**
     * @param $argument
     *
     * @throws Exception
     */
    protected function runJob($argument): void {
        $file = $this->backupService->createBackup(null, BackupService::AUTO_BACKUPS);
        $this->logger->info(['Created Backup %s with %s', $file->getName(), Util::humanFileSize($file->getSize())]);
        $this->backupService->removeOldBackups();
    }
}