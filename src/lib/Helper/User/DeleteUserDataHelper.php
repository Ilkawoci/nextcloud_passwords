<?php
/**
 * This file is part of the Passwords App
 * created by Marius David Wieschollek
 * and licensed under the AGPL.
 */

namespace OCA\PasswordFolders\Helper\User;

use Exception;
use OCA\PasswordFolders\Db\EntityInterface;
use OCA\PasswordFolders\Db\SessionMapper;
use OCA\PasswordFolders\Helper\Settings\UserSettingsHelper;
use OCA\PasswordFolders\Services\ConfigurationService;
use OCA\PasswordFolders\Services\EnvironmentService;
use OCA\PasswordFolders\Services\Object\AbstractModelService;
use OCA\PasswordFolders\Services\Object\AbstractService;
use OCA\PasswordFolders\Services\Object\ChallengeService;
use OCA\PasswordFolders\Services\Object\FolderService;
use OCA\PasswordFolders\Services\Object\KeychainService;
use OCA\PasswordFolders\Services\Object\PasswordService;
use OCA\PasswordFolders\Services\Object\ShareService;
use OCA\PasswordFolders\Services\Object\TagService;
use OCP\Lock\ILockingProvider;

/**
 * Class DeleteUserDataHelper
 *
 * @package OCA\PasswordFolders\Helper\User
 */
class DeleteUserDataHelper {

    /**
     * @var null|string
     */
    protected ?string $userId;

    /**
     * @var array
     */
    protected array $userConfigKeys
        = [
            'SSEv1UserKey',
            'client/settings',
            'user/challenge/id',
            'login/attempts/failed'
        ];

    /**
     * DeleteUserDataHelper constructor.
     *
     * @param TagService           $tagService
     * @param ShareService         $shareService
     * @param SessionMapper        $sessionMapper
     * @param UserSettingsHelper   $settings
     * @param FolderService        $folderService
     * @param ConfigurationService $config
     * @param EnvironmentService   $environment
     * @param PasswordService      $passwordService
     * @param KeychainService      $keychainService
     * @param ILockingProvider     $lockingProvider
     * @param ChallengeService     $challengeService
     */
    public function __construct(
        protected TagService $tagService,
        protected ShareService $shareService,
        protected SessionMapper $sessionMapper,
        protected UserSettingsHelper $settings,
        protected FolderService $folderService,
        protected ConfigurationService $config,
        EnvironmentService $environment,
        protected PasswordService $passwordService,
        protected KeychainService $keychainService,
        protected ILockingProvider $lockingProvider,
        protected ChallengeService $challengeService,
    ) {
        $this->userId           = $environment->getUserId();
    }

    /**
     * @param string $userId
     *
     * @throws Exception
     */
    public function deleteUserData(string $userId): void {
        if($this->userId !== null && $this->userId !== $userId) throw new Exception('Invalid user id '.$userId);

        $this->closeSessions($userId);
        $this->deleteObjects($this->challengeService, $userId);
        $this->deleteObjects($this->keychainService, $userId);
        $this->deleteObjects($this->tagService, $userId);
        $this->deleteObjects($this->folderService, $userId);
        $this->deleteObjects($this->passwordService, $userId);
        $this->deleteObjects($this->shareService, $userId);
        $this->deleteUserSettings($userId);
        $this->deleteUserConfig($userId);
        $this->deleteLocks($userId);
    }

    /**
     * @param AbstractModelService|ShareService|AbstractService $service
     * @param string                                            $userId
     *
     * @throws Exception
     */
    protected function deleteObjects(AbstractService $service, string $userId): void {
        $objects = $service->findByUserId($userId);

        foreach($objects as $object) {
            $service->delete($object);
        }
    }

    /**
     * @param string $userId
     *
     * @throws Exception
     */
    protected function deleteUserSettings(string $userId): void {
        $settings = array_keys($this->settings->list($userId));

        foreach($settings as $setting) {
            $this->settings->reset($setting, $userId);
        }
    }

    /**
     * @param string $userId
     *
     * @throws Exception
     */
    protected function deleteUserConfig(string $userId): void {
        foreach($this->userConfigKeys as $key) {
            $this->config->deleteUserValue($key, $userId);
        }
    }

    /**
     * @param string $userId
     *
     * @throws Exception
     */
    protected function closeSessions(string $userId): void {
        $sessions = $this->sessionMapper->findAllByUserId($userId);

        foreach($sessions as $session) {
            $this->sessionMapper->delete($session);
        }
    }

    /**
     * @param string $userId
     *
     * @throws Exception
     */
    protected function deleteLocks(string $userId): void {
        $userKeyLock = 'passwords/SSEv1r1/GenerateUserKey/'.sha1($userId);
        $this->lockingProvider->releaseLock($userKeyLock, ILockingProvider::LOCK_EXCLUSIVE);
    }
}