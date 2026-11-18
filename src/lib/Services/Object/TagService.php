<?php
/**
 * This file is part of the Passwords App
 * created by Marius David Wieschollek
 * and licensed under the AGPL.
 */

namespace OCA\PasswordFolders\Services\Object;

use OCA\PasswordFolders\Db\AbstractMapper;
use OCA\PasswordFolders\Db\Tag;
use OCA\PasswordFolders\Db\TagMapper;
use OCA\PasswordFolders\Helper\Uuid\UuidHelper;
use OCA\PasswordFolders\Services\EnvironmentService;
use OCP\EventDispatcher\IEventDispatcher;

/**
 * Class TagService
 *
 * @package OCA\PasswordFolders\Services\Object
 */
class TagService extends AbstractModelService {

    /**
     * @var TagMapper|AbstractMapper
     */
    protected AbstractMapper $mapper;

    /**
     * @var string
     */
    protected string $class = Tag::class;

    /**
     * TagService constructor.
     *
     * @param UuidHelper         $uuidHelper
     * @param IEventDispatcher   $eventDispatcher
     * @param TagMapper          $mapper
     * @param EnvironmentService $environment
     */
    public function __construct(UuidHelper $uuidHelper, IEventDispatcher $eventDispatcher, TagMapper $mapper, EnvironmentService $environment) {
        parent::__construct($mapper, $uuidHelper, $eventDispatcher, $environment);
    }

    /**
     * @param string $passwordUuid
     * @param bool   $includeHidden
     *
     * @return Tag[]
     */
    public function findByPassword(string $passwordUuid, bool $includeHidden = false): array {
        return $this->mapper->findAllByPassword($passwordUuid, $includeHidden);
    }
}