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

namespace OCA\Passwords\Helper\ApiObjects;

use Exception;
use OCA\Passwords\Db\EntityInterface;
use OCA\Passwords\Db\Share;
use OCA\Passwords\Services\EnvironmentService;
use OCA\Passwords\Services\Object\FolderService;
use OCA\Passwords\Services\Object\PasswordService;
use OCA\Passwords\Services\UserService;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\IConfig;
use Psr\Container\ContainerInterface;

/**
 * Class ShareObjectHelper
 *
 * @package Helper\ApiObjects
 */
class ShareObjectHelper extends AbstractObjectHelper {

    const string LEVEL_PASSWORD = 'password';
    const string LEVEL_FOLDER   = 'folder';

    /**
     * @var null|string
     */
    protected ?string $userId;

    /**
     * @var PasswordObjectHelper
     */
    protected PasswordObjectHelper $passwordObjectHelper;

    /**
     * @var FolderObjectHelper
     */
    protected FolderObjectHelper $folderObjectHelper;

    /** @noinspection PhpMissingParentConstructorInspection */
    /**
     * ShareObjectHelper constructor.
     *
     * @param IConfig            $config
     * @param UserService        $userService
     * @param ContainerInterface $container
     * @param EnvironmentService $environment
     * @param PasswordService    $passwordService
     * @param FolderService      $folderService
     */
    public function __construct(
        protected IConfig            $config,
        protected UserService        $userService,
        protected ContainerInterface $container,
        EnvironmentService           $environment,
        protected PasswordService    $passwordService,
        protected FolderService      $folderService
    ) {
        $this->userId = $environment->getUserId();
    }

    /**
     * @param EntityInterface|Share $share
     * @param string                $level
     * @param array                 $filter
     *
     * @return array|null
     * @throws Exception
     * @throws DoesNotExistException
     * @throws MultipleObjectsReturnedException
     */
    public function getApiObject(
        EntityInterface $share,
        string          $level = self::LEVEL_MODEL,
                        $filter = []
    ): ?array {

        if(!$this->filter($share, $filter)) return null;

        $detailLevel = explode('+', $level);
        if(in_array(self::LEVEL_MODEL, $detailLevel)) {
            $object = $this->getModel($share);
        } else {
            $object = ['id' => $share->getUuid()];
        }
        if(in_array(self::LEVEL_PASSWORD, $detailLevel) && $share->isPasswordShare()) {
            $object = $this->getPassword($object);
        }
        if(in_array(self::LEVEL_FOLDER, $detailLevel) && $share->isFolderShare()) {
            $object = $this->getFolder($object);
        }

        return $object;
    }

    /**
     * @param Share $share
     *
     * @return array
     */
    protected function getModel(Share $share): array {
        $isFolderShare = $share->isFolderShare();
        if($isFolderShare) {
            $item = $this->userId === $share->getUserId() ? $share->getSourceFolder():$share->getTargetFolder();
        } else {
            $item = $this->userId === $share->getUserId() ? $share->getSourcePassword():$share->getTargetPassword();
        }

        $shareable = $share->isShareable();
        if($this->userId !== $share->getUserId() && !$this->isReShareable()) $shareable = false;

        return [
            'id'            => $share->getUuid(),
            'created'       => $share->getCreated(),
            'updated'       => $share->getUpdated(),
            'expires'       => $share->getExpires(),
            'editable'      => $share->isEditable(),
            'shareable'     => $shareable,
            'sourceType'    => $share->getSourceType(),
            'password'      => $isFolderShare ? null:$item,
            'folder'        => $isFolderShare ? $item:null,
            'parentShare'   => $share->getParentShare(),
            'updatePending' => $share->isSourceUpdated() || $share->isTargetUpdated(),
            'owner'         => [
                'id'   => $share->getUserId(),
                'name' => $this->userService->getUserName($share->getUserId())
            ],
            'receiver'      => [
                'id'   => $share->getReceiver(),
                'name' => $this->userService->getUserName($share->getReceiver())
            ],
            'client'        => $share->getClient(),
        ];
    }

    /**
     * @param array $object
     *
     * @return array
     * @throws Exception
     * @throws DoesNotExistException
     * @throws MultipleObjectsReturnedException
     */
    protected function getPassword(array $object): array {
        $objectHelper       = $this->getPasswordObjectHelper();
        $password           = $this->passwordService->findByUuid($object['password']);
        $object['password'] = $objectHelper->getApiObject($password);

        return $object;
    }

    /**
     * @param array $object
     *
     * @return array
     * @throws Exception
     * @throws DoesNotExistException
     * @throws MultipleObjectsReturnedException
     */
    protected function getFolder(array $object): array {
        if(empty($object['folder'])) return $object;

        $objectHelper     = $this->getFolderObjectHelper();
        $folder           = $this->folderService->findByUuid($object['folder']);
        $object['folder'] = $objectHelper->getApiObject($folder, self::LEVEL_MODEL);

        return $object;
    }

    /**
     * @return PasswordObjectHelper
     */
    protected function getPasswordObjectHelper(): PasswordObjectHelper {
        if(!isset($this->passwordObjectHelper)) {
            $this->passwordObjectHelper = $this->container->get(PasswordObjectHelper::class);
        }

        return $this->passwordObjectHelper;
    }

    /**
     * @return FolderObjectHelper
     */
    protected function getFolderObjectHelper(): FolderObjectHelper {
        if(!isset($this->folderObjectHelper)) {
            $this->folderObjectHelper = $this->container->get(FolderObjectHelper::class);
        }

        return $this->folderObjectHelper;
    }

    /**
     * @return bool
     */
    protected function isReShareable(): bool {
        return $this->config->getAppValue('core', 'shareapi_allow_resharing', 'yes') === 'yes';
    }
}