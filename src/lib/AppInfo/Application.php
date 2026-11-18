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

namespace OCA\PasswordFolders\AppInfo;

use OCA\PasswordFolders\Controller\Actions\RecoverHiddenController;
use OCA\PasswordFolders\Controller\Admin\CacheController;
use OCA\PasswordFolders\Controller\Admin\SettingsController;
use OCA\PasswordFolders\Controller\Api\AccountApiController;
use OCA\PasswordFolders\Controller\Api\FolderApiController;
use OCA\PasswordFolders\Controller\Api\KeychainApiController;
use OCA\PasswordFolders\Controller\Api\PasswordApiController;
use OCA\PasswordFolders\Controller\Api\ServiceApiController;
use OCA\PasswordFolders\Controller\Api\SessionApiController;
use OCA\PasswordFolders\Controller\Api\SettingsApiController;
use OCA\PasswordFolders\Controller\Api\ShareApiController;
use OCA\PasswordFolders\Controller\Api\TagApiController;
use OCA\PasswordFolders\Controller\Link\ConnectController;
use OCA\PasswordFolders\Dashboard\PasswordsWidget;
use OCA\PasswordFolders\EventListener\Challenge\ChallengeActivatedListener;
use OCA\PasswordFolders\EventListener\CSP\AddCSPListener;
use OCA\PasswordFolders\EventListener\Folder\BeforeFolderDeletedListener;
use OCA\PasswordFolders\EventListener\Folder\BeforeFolderSetRevisionListener;
use OCA\PasswordFolders\EventListener\Folder\FolderClonedListener;
use OCA\PasswordFolders\EventListener\Folder\FolderDeletedListener;
use OCA\PasswordFolders\EventListener\Password\BeforePasswordDeletedListener;
use OCA\PasswordFolders\EventListener\Password\BeforePasswordSetRevisionListener;
use OCA\PasswordFolders\EventListener\Password\PasswordClonedListener;
use OCA\PasswordFolders\EventListener\Password\PasswordDeletedListener;
use OCA\PasswordFolders\EventListener\PasswordRevision\BeforePasswordRevisionSavedEventListener;
use OCA\PasswordFolders\EventListener\Share\ShareDeletedListener;
use OCA\PasswordFolders\EventListener\Tag\BeforeTagDeletedListener;
use OCA\PasswordFolders\EventListener\Tag\BeforeTagSetRevisionListener;
use OCA\PasswordFolders\EventListener\Tag\TagClonedListener;
use OCA\PasswordFolders\EventListener\Tag\TagDeletedListener;
use OCA\PasswordFolders\EventListener\User\BeforeUserCreatedListener;
use OCA\PasswordFolders\EventListener\User\UserDeletedListener;
use OCA\PasswordFolders\EventListener\User\UserPasswordChangedListener;
use OCA\PasswordFolders\Events\Challenge\BeforeChallengeActivatedEvent;
use OCA\PasswordFolders\Events\Challenge\ChallengeActivatedEvent;
use OCA\PasswordFolders\Events\Folder\BeforeFolderDeletedEvent;
use OCA\PasswordFolders\Events\Folder\BeforeFolderSetRevisionEvent;
use OCA\PasswordFolders\Events\Folder\FolderClonedEvent;
use OCA\PasswordFolders\Events\Folder\FolderDeletedEvent;
use OCA\PasswordFolders\Events\Password\BeforePasswordDeletedEvent;
use OCA\PasswordFolders\Events\Password\BeforePasswordSetRevisionEvent;
use OCA\PasswordFolders\Events\Password\PasswordClonedEvent;
use OCA\PasswordFolders\Events\Password\PasswordDeletedEvent;
use OCA\PasswordFolders\Events\PasswordRevision\BeforePasswordRevisionCreatedEvent;
use OCA\PasswordFolders\Events\PasswordRevision\BeforePasswordRevisionUpdatedEvent;
use OCA\PasswordFolders\Events\Share\ShareDeletedEvent;
use OCA\PasswordFolders\Events\Tag\BeforeTagDeletedEvent;
use OCA\PasswordFolders\Events\Tag\BeforeTagSetRevisionEvent;
use OCA\PasswordFolders\Events\Tag\TagClonedEvent;
use OCA\PasswordFolders\Events\Tag\TagDeletedEvent;
use OCA\PasswordFolders\Helper\Settings\ShareSettingsHelper;
use OCA\PasswordFolders\Helper\Sharing\RecipientSearchHelper;
use OCA\PasswordFolders\Helper\Words\SpecialCharacterHelper;
use OCA\PasswordFolders\Integrations\GuestsIntegration;
use OCA\PasswordFolders\Middleware\ApiSecurityMiddleware;
use OCA\PasswordFolders\Middleware\ApiSessionMiddleware;
use OCA\PasswordFolders\Provider\Favicon\BestIconProvider;
use OCA\PasswordFolders\Provider\Favicon\DefaultFaviconProvider;
use OCA\PasswordFolders\Provider\Favicon\DuckDuckGoProvider;
use OCA\PasswordFolders\Provider\Favicon\FaviconGrabberProvider;
use OCA\PasswordFolders\Provider\Favicon\FaviconProviderInterface;
use OCA\PasswordFolders\Provider\Favicon\GoogleFaviconProvider;
use OCA\PasswordFolders\Provider\Favicon\LocalFaviconProvider;
use OCA\PasswordFolders\Provider\Preview\BrowshotPreviewProvider;
use OCA\PasswordFolders\Provider\Preview\DefaultPreviewProvider;
use OCA\PasswordFolders\Provider\Preview\PageresCliProvider;
use OCA\PasswordFolders\Provider\Preview\PreviewProviderInterface;
use OCA\PasswordFolders\Provider\Preview\ScreeenlyProvider;
use OCA\PasswordFolders\Provider\Preview\ScreenShotLayerProvider;
use OCA\PasswordFolders\Provider\Preview\ScreenShotMachineProvider;
use OCA\PasswordFolders\Provider\SecurityCheck\BigDbPlusHibpSecurityCheckProvider;
use OCA\PasswordFolders\Provider\SecurityCheck\BigLocalDbSecurityCheckProvider;
use OCA\PasswordFolders\Provider\SecurityCheck\HaveIBeenPwnedProvider;
use OCA\PasswordFolders\Provider\SecurityCheck\SecurityCheckProviderInterface;
use OCA\PasswordFolders\Provider\SecurityCheck\SmallLocalDbSecurityCheckProvider;
use OCA\PasswordFolders\Provider\Words\AutoWordsProvider;
use OCA\PasswordFolders\Provider\Words\LeipzigCorporaProvider;
use OCA\PasswordFolders\Provider\Words\LocalWordsProvider;
use OCA\PasswordFolders\Provider\Words\RandomCharactersProvider;
use OCA\PasswordFolders\Provider\Words\SnakesWordsProvider;
use OCA\PasswordFolders\Provider\Words\WordsProviderInterface;
use OCA\PasswordFolders\Services\ConfigurationService;
use OCA\PasswordFolders\Services\EnvironmentService;
use OCA\PasswordFolders\Services\HelperService;
use OCA\PasswordFolders\Services\NotificationService;
use OCA\PasswordFolders\SetupChecks\BackgroundJobsBreachedPasswordsDatabaseUpdateSuccessSetupCheck;
use OCA\PasswordFolders\SetupChecks\BackgroundJobsExecutedWithCronSetupCheck;
use OCA\PasswordFolders\SetupChecks\BackgroundJobsPhpVersionSetupCheck;
use OCA\PasswordFolders\UserMigration\PasswordsMigrator;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\Http\Client\IClientService;
use OCP\IGroupManager;
use OCP\IUserManager;
use OCP\L10N\IFactory;
use OCP\Notification\IManager;
use OCP\Security\CSP\AddContentSecurityPolicyEvent;
use OCP\Share\IManager as ShareManager;
use OCP\User\Events\BeforeUserCreatedEvent;
use OCP\User\Events\CreateUserEvent;
use OCP\User\Events\PasswordUpdatedEvent;
use OCP\User\Events\UserDeletedEvent;
use Psr\Container\ContainerInterface;
use Random\Randomizer;

/**
 * Class Application
 *
 * @package OCA\PasswordFolders\AppInfo
 */
class Application extends App implements IBootstrap {

    const string APP_NAME = 'passwordfolders';

    /**
     * Application constructor.
     *
     * @param array $urlParams
     */
    public function __construct(array $urlParams = []) {
        parent::__construct(self::APP_NAME, $urlParams);
    }

    /**
     * @param IRegistrationContext $context
     */
    public function register(IRegistrationContext $context): void {
        $this->registerDiClasses($context);
        $this->registerSystemHooks();
        $this->registerMiddleware($context);
        $context->registerUserMigrator(PasswordsMigrator::class);
        $context->registerDashboardWidget(PasswordsWidget::class);
    }

    /**
     * @param IBootContext $context
     */
    public function boot(IBootContext $context): void {
        $this->registerNotificationNotifier();
        $this->registerInternalListeners();
    }

    /**
     * @param IRegistrationContext $context
     */
    protected function registerDiClasses(IRegistrationContext $context): void {
        /**
         * Controllers
         */
        $this->registerController($context);

        /**
         * Helper
         */
        $this->registerHelpers($context);

        /**
         * Providers
         */
        $this->registerProviders($context);

        /**
         * Setup Checks
         */
        $this->registerSetupChecks($context);
    }

    /**
     * @param IRegistrationContext $context
     */
    protected function registerMiddleware(IRegistrationContext $context): void {
        $context->registerServiceAlias('ApiSecurityMiddleware', ApiSecurityMiddleware::class);
        $context->registerMiddleware('ApiSecurityMiddleware');

        $context->registerServiceAlias('ApiSessionMiddleware', ApiSessionMiddleware::class);
        $context->registerMiddleware('ApiSessionMiddleware');
    }

    /**
     * @param IRegistrationContext $context
     */
    protected function registerController(IRegistrationContext $context): void {
        $context->registerServiceAlias('AdminSettingsController', SettingsController::class);
        $context->registerServiceAlias('AdminCachesController', CacheController::class);
        $context->registerServiceAlias('KeychainApiController', KeychainApiController::class);
        $context->registerServiceAlias('PasswordApiController', PasswordApiController::class);
        $context->registerServiceAlias('SettingsApiController', SettingsApiController::class);
        $context->registerServiceAlias('AccountApiController', AccountApiController::class);
        $context->registerServiceAlias('SessionApiController', SessionApiController::class);
        $context->registerServiceAlias('ServiceApiController', ServiceApiController::class);
        $context->registerServiceAlias('FolderApiController', FolderApiController::class);
        $context->registerServiceAlias('ShareApiController', ShareApiController::class);
        $context->registerServiceAlias('ConnectController', ConnectController::class);
        $context->registerServiceAlias('TagApiController', TagApiController::class);
        $context->registerServiceAlias('RecoverHiddenController', RecoverHiddenController::class);
    }

    /**
     *
     */
    protected function registerInternalListeners() {
        /* @var IEventDispatcher $dispatcher */
        $dispatcher = $this->getContainer()->get(IEventDispatcher::class);
        $dispatcher->addServiceListener(BeforePasswordDeletedEvent::class, BeforePasswordDeletedListener::class);
        $dispatcher->addServiceListener(BeforePasswordSetRevisionEvent::class, BeforePasswordSetRevisionListener::class);
        $dispatcher->addServiceListener(PasswordClonedEvent::class, PasswordClonedListener::class);
        $dispatcher->addServiceListener(PasswordDeletedEvent::class, PasswordDeletedListener::class);

        $dispatcher->addServiceListener(BeforePasswordRevisionCreatedEvent::class, BeforePasswordRevisionSavedEventListener::class);
        $dispatcher->addServiceListener(BeforePasswordRevisionUpdatedEvent::class, BeforePasswordRevisionSavedEventListener::class);

        $dispatcher->addServiceListener(BeforeFolderDeletedEvent::class, BeforeFolderDeletedListener::class);
        $dispatcher->addServiceListener(BeforeFolderSetRevisionEvent::class, BeforeFolderSetRevisionListener::class);
        $dispatcher->addServiceListener(FolderClonedEvent::class, FolderClonedListener::class);
        $dispatcher->addServiceListener(FolderDeletedEvent::class, FolderDeletedListener::class);

        $dispatcher->addServiceListener(BeforeTagDeletedEvent::class, BeforeTagDeletedListener::class);
        $dispatcher->addServiceListener(BeforeTagSetRevisionEvent::class, BeforeTagSetRevisionListener::class);
        $dispatcher->addServiceListener(TagClonedEvent::class, TagClonedListener::class);
        $dispatcher->addServiceListener(TagDeletedEvent::class, TagDeletedListener::class);

        $dispatcher->addServiceListener(ShareDeletedEvent::class, ShareDeletedListener::class);

        $dispatcher->addServiceListener(BeforeChallengeActivatedEvent::class, ChallengeActivatedListener::class);
        $dispatcher->addServiceListener(ChallengeActivatedEvent::class, ChallengeActivatedListener::class);
    }

    /**
     *
     */
    protected function registerSystemHooks(): void {
        /* @var IEventDispatcher $dispatcher */
        $dispatcher = $this->getContainer()->get(IEventDispatcher::class);
        $dispatcher->addServiceListener(CreateUserEvent::class, BeforeUserCreatedListener::class);
        $dispatcher->addServiceListener(BeforeUserCreatedEvent::class, BeforeUserCreatedListener::class);
        $dispatcher->addServiceListener(UserDeletedEvent::class, UserDeletedListener::class);
        $dispatcher->addServiceListener(PasswordUpdatedEvent::class, UserPasswordChangedListener::class);
        $dispatcher->addServiceListener(AddContentSecurityPolicyEvent::class, AddCSPListener::class);
    }

    /**
     * Registers the Notification service
     */
    protected function registerNotificationNotifier(): void {
        $this->getContainer()->get(IManager::class)->registerNotifierService(NotificationService::class);
    }

    /**
     * @param IRegistrationContext $context
     *
     * @return void
     */
    protected function registerHelpers(IRegistrationContext $context): void {
        $context->registerService(
            LocalWordsProvider::class,
            function (ContainerInterface $c) {
                return new LocalWordsProvider(
                    $c->get(SpecialCharacterHelper::class),
                    $c->get(IFactory::class)->get('core')->getLanguageCode()
                );
            }
        );

        $context->registerService(
            RandomCharactersProvider::class,
            function (ContainerInterface $c) {
                return new RandomCharactersProvider(
                    $c->get(Randomizer::class),
                    $c->get(IFactory::class)->get('core')->getLanguageCode()
                );
            }
        );

        $context->registerService(
            LeipzigCorporaProvider::class,
            function (ContainerInterface $c) {
                return new LeipzigCorporaProvider(
                    $c->get(SpecialCharacterHelper::class),
                    $c->get(IClientService::class),
                    $c->get(Randomizer::class),
                    $c->get(IFactory::class)->get('core')->getLanguageCode()
                );
            }
        );

        $context->registerService(
            RecipientSearchHelper::class,
            function (ContainerInterface $c) {
                return new RecipientSearchHelper(
                    $c->get(ShareManager::class),
                    $c->get(IUserManager::class),
                    $c->get(IGroupManager::class),
                    $c->get(EnvironmentService::class),
                    $c->get(ShareSettingsHelper::class),
                    $c->get(GuestsIntegration::class)
                );
            }
        );
    }

    /**
     * @param IRegistrationContext $context
     *
     * @return void
     */
    protected function registerProviders(IRegistrationContext $context): void {
        $context->registerService(
            SecurityCheckProviderInterface::class,
            function (ContainerInterface $c) {
                $service = $c->get(ConfigurationService::class)->getAppValue('service/security', HelperService::SECURITY_HIBP);

                return match ($service) {
                    HelperService::SECURITY_BIG_LOCAL => $c->get(BigLocalDbSecurityCheckProvider::class),
                    HelperService::SECURITY_SMALL_LOCAL => $c->get(SmallLocalDbSecurityCheckProvider::class),
                    HelperService::SECURITY_BIGDB_HIBP => $c->get(BigDbPlusHibpSecurityCheckProvider::class),
                    default => $c->get(HaveIBeenPwnedProvider::class),
                };
            }
        );

        $context->registerService(
            WordsProviderInterface::class,
            function (ContainerInterface $c) {
                $service = $c->get(ConfigurationService::class)->getAppValue('service/words', HelperService::WORDS_AUTO);

                return match ($service) {
                    HelperService::WORDS_LOCAL => $c->get(LocalWordsProvider::class),
                    HelperService::WORDS_LEIPZIG => $c->get(LeipzigCorporaProvider::class),
                    HelperService::WORDS_SNAKES => $c->get(SnakesWordsProvider::class),
                    HelperService::WORDS_RANDOM => $c->get(RandomCharactersProvider::class),
                    default => $c->get(AutoWordsProvider::class),
                };
            }
        );

        $context->registerService(
            FaviconProviderInterface::class,
            function (ContainerInterface $c) {
                $service = $c->get(ConfigurationService::class)->getAppValue('service/favicon', HelperService::FAVICON_DEFAULT);

                return match ($service) {
                    HelperService::FAVICON_BESTICON => $c->get(BestIconProvider::class),
                    HelperService::FAVICON_FAVICON_GRABBER => $c->get(FaviconGrabberProvider::class),
                    HelperService::FAVICON_DUCK_DUCK_GO => $c->get(DuckDuckGoProvider::class),
                    HelperService::FAVICON_GOOGLE => $c->get(GoogleFaviconProvider::class),
                    HelperService::FAVICON_LOCAL => $c->get(LocalFaviconProvider::class),
                    default => $c->get(DefaultFaviconProvider::class),
                };
            }
        );

        $context->registerService(
            PreviewProviderInterface::class,
            function (ContainerInterface $c) {
                $service = $c->get(ConfigurationService::class)->getAppValue('service/preview', HelperService::PREVIEW_DEFAULT);

                return match ($service) {
                    HelperService::PREVIEW_PAGERES => $c->get(PageresCliProvider::class),
                    HelperService::PREVIEW_BROW_SHOT => $c->get(BrowshotPreviewProvider::class),
                    HelperService::PREVIEW_SCREEN_SHOT_LAYER => $c->get(ScreenShotLayerProvider::class),
                    HelperService::PREVIEW_SCREEN_SHOT_MACHINE => $c->get(ScreenShotMachineProvider::class),
                    HelperService::PREVIEW_SCREEENLY => $c->get(ScreeenlyProvider::class),
                    default => $c->get(DefaultPreviewProvider::class),
                };
            }
        );
    }

    /**
     * @param IRegistrationContext $context
     *
     * @return void
     */
    protected function registerSetupChecks(IRegistrationContext $context): void {
        $context->registerSetupCheck(BackgroundJobsPhpVersionSetupCheck::class);
        $context->registerSetupCheck(BackgroundJobsExecutedWithCronSetupCheck::class);
        $context->registerSetupCheck(BackgroundJobsBreachedPasswordsDatabaseUpdateSuccessSetupCheck::class);
    }
}