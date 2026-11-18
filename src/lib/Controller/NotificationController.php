<?php
/**
 * This file is part of the Passwords App
 * created by Marius David Wieschollek
 * and licensed under the AGPL.
 */

namespace OCA\PasswordFolders\Controller;

use OCA\PasswordFolders\AppInfo\Application;
use OCA\PasswordFolders\Helper\Survey\ServerReportHelper;
use OCA\PasswordFolders\Services\ConfigurationService;
use OCA\PasswordFolders\Services\EnvironmentService;
use OCP\AppFramework\Controller;
use OCP\IRequest;
use OCP\Notification\IManager;

/**
 * Class NotificationController
 *
 * @package OCA\PasswordFolders\Controller
 */
class NotificationController extends Controller {

    /**
     * @var ConfigurationService
     */
    protected ConfigurationService $config;

    /**
     * @var EnvironmentService
     */
    protected EnvironmentService $environment;

    /**
     * @var IManager
     */
    protected IManager $notifications;

    /**
     * @var ServerReportHelper
     */
    protected ServerReportHelper $serverReport;

    /**
     * NotificationController constructor.
     *
     * @param string               $appName
     * @param IRequest             $request
     * @param IManager             $notifications
     * @param ConfigurationService $config
     * @param EnvironmentService   $environment
     * @param ServerReportHelper   $serverReport
     */
    public function __construct(
        string $appName,
        IRequest $request,
        IManager $notifications,
        ConfigurationService $config,
        EnvironmentService $environment,
        ServerReportHelper $serverReport
    ) {
        parent::__construct($appName, $request);
        $this->config        = $config;
        $this->environment   = $environment;
        $this->notifications = $notifications;
        $this->serverReport  = $serverReport;
    }

    /**
     * @param string $answer
     */
    public function survey(string $answer = 'yes'): void {
        if($answer === 'yes') {
            $this->config->setAppValue('survey/server/mode', 2);
            $this->removeNotification(true);
            $this->serverReport->sendReport();
        } else {
            $this->removeNotification(false);
        }
    }

    /**
     * @param bool $allUsers
     */
    protected function removeNotification(bool $allUsers): void {
        $notification = $this->notifications->createNotification();
        $notification->setApp(Application::APP_NAME)
                     ->setObject('admin', 'survey');
        if(!$allUsers) $notification->setUser($this->environment->getUserId());

        $this->notifications->markProcessed($notification);
    }
}