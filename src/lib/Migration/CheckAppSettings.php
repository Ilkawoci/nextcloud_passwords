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

namespace OCA\PasswordFolders\Migration;

use Exception;
use OCA\PasswordFolders\AppInfo\SystemRequirements;
use OCA\PasswordFolders\Helper\AppSettings\ServiceSettingsHelper;
use OCA\PasswordFolders\Helper\User\AdminUserHelper;
use OCA\PasswordFolders\Services\BackgroundJobService;
use OCA\PasswordFolders\Services\ConfigurationService;
use OCA\PasswordFolders\Services\HelperService;
use OCA\PasswordFolders\Services\NotificationService;
use OCP\Migration\IOutput;
use OCP\Migration\IRepairStep;
use OCP\ServerVersion;

/**
 * Class CheckAppSettings
 *
 * @package OCA\PasswordFolders\Migration
 */
class CheckAppSettings implements IRepairStep {


    /**
     * CheckAppSettings constructor.
     *
     * @param AdminUserHelper       $adminHelper
     * @param ConfigurationService  $config
     * @param ServerVersion         $serverVersion
     * @param NotificationService   $notifications
     * @param ServiceSettingsHelper $serviceSettings
     * @param BackgroundJobService  $backgroundJobService
     */
    public function __construct(
        protected AdminUserHelper       $adminHelper,
        protected ConfigurationService  $config,
        protected ServerVersion         $serverVersion,
        protected NotificationService   $notifications,
        protected ServiceSettingsHelper $serviceSettings,
        protected BackgroundJobService  $backgroundJobService
    ) {
    }

    /**
     * Returns the step's name
     *
     * @return string
     * @since 9.1.0
     */
    public function getName() {
        return 'Check app settings';
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
    public function run(IOutput $output) {
        $faviconSetting = $this->serviceSettings->get('favicon');
        $faviconApiSetting = $this->serviceSettings->get('favicon.api');

        if ($faviconSetting['value'] === HelperService::FAVICON_BESTICON) {
            if (str_contains($faviconApiSetting['value'], 'passwords-app-favicons.herokuapp.com')) {
                $this->serviceSettings->reset('favicon.api');
            }
        }

        $previewSetting = $this->serviceSettings->get('preview');
        $previewApiSetting = $this->serviceSettings->get('preview.api');
        if (empty($previewApiSetting['value']) && in_array(
                $previewSetting['value'],
                $previewApiSetting['depends']['service.preview']
            )) {
            $this->sendEmptySettingNotification('preview');
        }

        $ncVersion = $this->serverVersion->getMajorVersion();
        if ($ncVersion < SystemRequirements::NC_NOTIFICATION_ID || PHP_VERSION_ID < SystemRequirements::PHP_NOTIFICATION_ID) {
            $this->sendDeprecatedPlatformNotification($ncVersion, PHP_VERSION_ID);
        }

        if ($this->config->getAppValue('nightly/enabled', '0') === '1') {
            $this->backgroundJobService->addNightlyUpdates();
        }
    }

    /**
     * @param string $setting
     */
    protected function sendEmptySettingNotification(string $setting): void {
        foreach ($this->adminHelper->getAdmins() as $admin) {
            $this->notifications->sendEmptyRequiredSettingNotification($admin->getUID(), $setting);
        }
    }

    /**
     *
     */
    protected function sendDeprecatedPlatformNotification(int $ncVersion, int $phpVersion): void {
        $appVersion = $this->config->getAppValue('installed_version');
        foreach ($this->adminHelper->getAdmins() as $admin) {
            $this->notifications->sendUpgradeRequiredNotification(
                $admin->getUID(),
                $ncVersion,
                $phpVersion,
                $appVersion
            );
        }
    }
}