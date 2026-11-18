<?php
/**
 * This file is part of the Passwords App
 * created by Marius David Wieschollek
 * and licensed under the AGPL.
 */

namespace OCA\PasswordFolders\Services\Object;

use Exception;
use OCA\PasswordFolders\Db\AbstractMapper;
use OCA\PasswordFolders\Db\ModelInterface;
use OCA\PasswordFolders\Db\Password;
use OCA\PasswordFolders\Db\PasswordMapper;
use OCA\PasswordFolders\Helper\Uuid\UuidHelper;
use OCA\PasswordFolders\Services\EnvironmentService;
use OCP\EventDispatcher\IEventDispatcher;

/**
 * Class PasswordService
 *
 * @package OCA\PasswordFolders\Services
 */
class PasswordService extends AbstractModelService {

    /**
     * @var PasswordMapper|AbstractMapper
     */
    protected AbstractMapper $mapper;

    /**
     * @var string
     */
    protected string $class = Password::class;

    /**
     * PasswordService constructor.
     *
     * @param UuidHelper         $uuidHelper
     * @param IEventDispatcher   $eventDispatcher
     * @param PasswordMapper     $mapper
     * @param EnvironmentService $environment
     */
    public function __construct(UuidHelper $uuidHelper, IEventDispatcher $eventDispatcher, PasswordMapper $mapper, EnvironmentService $environment) {
        parent::__construct($mapper, $uuidHelper, $eventDispatcher, $environment);
    }

    /**
     * @param string $uuid
     *
     * @return Password[]
     */
    public function findByFolder(string $uuid): array {
        return $this->mapper->findAllByFolder($uuid);
    }

    /**
     * @param string $tagUuid
     * @param bool   $includeHidden
     *
     * @return Password[]
     */
    public function findByTag(string $tagUuid, bool $includeHidden): array {
        return $this->mapper->findAllByTag($tagUuid, $includeHidden);
    }

    /**
     * @return Password[]
     * @throws Exception
     */
    public function findShared(): array {
        return $this->mapper->findAllShared();
    }

    /**
     * @return Password[]
     */
    public function findOrphanedTargetPasswords(): array {
        return $this->mapper->findAllOrphanedTargetPasswords();
    }
    
    /**
     * @return Password[]
     */
    public function findSharedPasswordsWithoutShares(): array {
        return $this->mapper->findAllSharedPasswordsWithoutShares();
    }

    /**
     * @return Password
     */
    protected function createModel(): ModelInterface {
        /** @var Password $model */
        $model = parent::createModel();
        $model->setEditable(true);

        return $model;
    }
}