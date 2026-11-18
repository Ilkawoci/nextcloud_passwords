<?php
/**
 * This file is part of the Passwords App
 * created by Marius David Wieschollek
 * and licensed under the AGPL.
 */

namespace OCA\PasswordFolders\Services\Object;

use OCA\PasswordFolders\Db\AbstractMapper;
use OCA\PasswordFolders\Db\EntityInterface;
use OCA\PasswordFolders\Db\Registration;
use OCA\PasswordFolders\Db\RegistrationMapper;
use OCA\PasswordFolders\Helper\Uuid\UuidHelper;
use OCA\PasswordFolders\Services\EnvironmentService;
use OCP\EventDispatcher\IEventDispatcher;

/**
 * Class RegistrationService
 *
 * @package OCA\PasswordFolders\Services\Object
 */
class RegistrationService extends AbstractService {

    /**
     * @var string
     */
    protected string $class = Registration::class;

    /**
     * @var RegistrationMapper|AbstractMapper
     */
    protected AbstractMapper $mapper;

    /**
     * RegistrationService constructor.
     *
     * @param RegistrationMapper $mapper
     * @param UuidHelper         $uuidHelper
     * @param IEventDispatcher   $eventDispatcher
     * @param EnvironmentService $environment
     */
    public function __construct(RegistrationMapper $mapper, UuidHelper $uuidHelper, IEventDispatcher $eventDispatcher, EnvironmentService $environment) {
        $this->mapper = $mapper;

        parent::__construct($uuidHelper, $eventDispatcher, $environment);
    }

    /**
     * @return Registration
     */
    public function create(): Registration {
        $model = $this->createModel();
        $this->fireEvent('instantiated', $model);

        return $model;
    }

    /**
     *
     */
    public function clearCache(): void {
        $this->mapper->clearEntityCache();
    }

    /**
     * @return Registration
     */
    protected function createModel(): Registration {
        $model = new Registration();
        $model->setDeleted(false);
        $model->setUserId($this->userId);
        $model->setUuid($this->uuidHelper->generateUuid());
        $model->setCreated(time());
        $model->setUpdated(time());

        return $model;
    }

    /**
     * @param Registration|EntityInterface $original
     * @param array                        $overwrites
     *
     * @return Registration
     */
    protected function cloneModel(EntityInterface $original, array $overwrites = []): EntityInterface {

        /** @var Registration $clone */
        $clone = parent::cloneModel($original, $overwrites);
        $clone->setUuid($this->uuidHelper->generateUuid());

        return $clone;
    }
}