<?php
/**
 * This file is part of the Passwords App
 * created by Marius David Wieschollek
 * and licensed under the AGPL.
 */

namespace OCA\Passwords\Services\Object;

use Exception;
use OCA\Passwords\Db\AbstractMapper;
use OCA\Passwords\Db\EntityInterface;
use OCA\Passwords\Db\ModelInterface;
use OCA\Passwords\Db\Share;
use OCA\Passwords\Db\ShareMapper;
use OCA\Passwords\Helper\Uuid\UuidHelper;
use OCA\Passwords\Services\EnvironmentService;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\EventDispatcher\IEventDispatcher;

/**
 * Class ShareService
 *
 * @package OCA\Passwords\Services\Object
 */
class ShareService extends AbstractService {

    /**
     * @var ShareMapper|AbstractMapper
     */
    protected AbstractMapper $mapper;

    /**
     * @var string
     */
    protected string $class = Share::class;

    /**
     * ShareService constructor.
     *
     * @param UuidHelper         $uuidHelper
     * @param IEventDispatcher   $eventDispatcher
     * @param ShareMapper        $mapper
     * @param EnvironmentService $environment
     */
    public function __construct(UuidHelper $uuidHelper, IEventDispatcher $eventDispatcher, ShareMapper $mapper, EnvironmentService $environment) {
        $this->mapper = $mapper;

        parent::__construct($uuidHelper, $eventDispatcher, $environment);
    }

    /**
     * @return ModelInterface[]
     */
    public function findAll(): array {
        return $this->mapper->findAll();
    }

    /**
     * @param string $userId
     *
     * @return Share[]
     * @throws Exception
     */
    public function findWithUserId(string $userId): array {
        return $this->mapper->findAllByUserIdOrReceiverId($userId);
    }

    /**
     * @param string $passwordUuid
     *
     * @return Share[]
     *
     * @throws Exception
     */
    public function findBySourcePassword(string $passwordUuid): array {
        return $this->mapper->findAllByField('source_password', $passwordUuid);
    }

    /**
     * @param string $passwordUuid
     *
     * @return Share|EntityInterface|null
     * @throws DoesNotExistException
     * @throws MultipleObjectsReturnedException
     */
    public function findByTargetPassword(string $passwordUuid): Share {
        return $this->mapper->findOneByField('target_password', $passwordUuid);
    }

    /**
     * @return Share[]
     *
     * @throws Exception
     */
    public function findBySourceUpdated(): array {
        return $this->mapper->findAllByFields(
            ['source_updated', true, IQueryBuilder::PARAM_BOOL],
            ['target_updated', null, IQueryBuilder::PARAM_NULL, 'neq'],
            ['target_password', null, IQueryBuilder::PARAM_NULL, 'neq']
        );
    }

    /**
     * @return Share[]
     *
     * @throws Exception
     */
    public function findByTargetUpdated(): array {
        return $this->mapper->findAllByField('target_updated', true, IQueryBuilder::PARAM_BOOL);
    }

    /**
     * @param string $passwordUuid
     * @param string $userId
     *
     * @return Share|EntityInterface|null
     *
     * @throws DoesNotExistException
     * @throws MultipleObjectsReturnedException
     */
    public function findBySourcePasswordAndReceiver(string $passwordUuid, string $userId): ?Share {
        return $this->mapper->findOneByFields(
            ['source_password', $passwordUuid],
            ['receiver', $userId]
        );
    }

    /**
     * @return Share[]
     * @throws Exception
     */
    public function findNew(): array {
        // Only classic password shares: a new password share has no target yet
        // and always has a source password. Folder-share roots are picked up by
        // findNewFolderShares() instead (they have a source folder but no source
        // password), so they must not be processed by the password-share flow.
        return $this->mapper->findAllByFields(
            ['target_password', null, IQueryBuilder::PARAM_NULL],
            ['source_password', null, IQueryBuilder::PARAM_NULL, 'neq']
        );
    }

    /**
     * Folder-share roots that have not been materialized yet (no target folder).
     *
     * @return Share[]
     * @throws Exception
     */
    public function findNewFolderShares(): array {
        return $this->mapper->findAllByFields(
            ['source_folder', null, IQueryBuilder::PARAM_NULL, 'neq'],
            ['source_password', null, IQueryBuilder::PARAM_NULL],
            ['target_folder', null, IQueryBuilder::PARAM_NULL]
        );
    }

    /**
     * All shares (root and children) that belong to a shared source folder.
     *
     * @param string $folderUuid
     *
     * @return Share[]
     * @throws Exception
     */
    public function findAllBySourceFolder(string $folderUuid): array {
        return $this->mapper->findAllByField('source_folder', $folderUuid);
    }

    /**
     * All shares where the given user is the receiver.
     *
     * @param string $receiverId
     *
     * @return Share[]
     * @throws Exception
     */
    public function findAllByReceiver(string $receiverId): array {
        return $this->mapper->findAllByField('receiver', $receiverId);
    }

    /**
     * All folder-share roots (both pending and materialized). A root references a
     * source folder and has no source password; its children carry a source
     * password plus this same source folder.
     *
     * @return Share[]
     * @throws Exception
     */
    public function findAllFolderShareRoots(): array {
        return $this->mapper->findAllByFields(
            ['source_folder', null, IQueryBuilder::PARAM_NULL, 'neq'],
            ['source_password', null, IQueryBuilder::PARAM_NULL]
        );
    }

    /**
     * The child share of a folder share for a single source password inside the
     * given shared source folder. Returns null when no such child share exists.
     *
     * @param string $sourceFolder   uuid of the shared source folder (folder-share root)
     * @param string $sourcePassword uuid of the source password
     *
     * @return Share|null
     * @throws Exception
     */
    public function findFolderChildShare(string $sourceFolder, string $sourcePassword): ?Share {
        $shares = $this->mapper->findAllByFields(
            ['source_folder', $sourceFolder],
            ['source_password', $sourcePassword]
        );

        return count($shares) ? $shares[0] : null;
    }

    /**
     * @return Share[]
     * @throws Exception
     */
    public function findExpired(): array {
        return $this->mapper->findAllByField('expires', time(), IQueryBuilder::PARAM_INT, 'lte');
    }

    /**
     * @param string $uuid
     *
     * @return Share|EntityInterface
     * @throws DoesNotExistException
     * @throws MultipleObjectsReturnedException
     */
    public function findByUuid(string $uuid): Share {
        return $this->mapper->findByUuid($uuid);
    }

    /**
     * @param string   $passwordId
     * @param string   $receiverId
     * @param string   $type
     * @param bool     $editable
     * @param int|null $expires
     * @param bool     $shareable
     *
     * @return Share|ModelInterface
     */
    public function create(
        string $passwordId,
        string $receiverId,
        string $type,
        bool $editable,
        ?int $expires = null,
        bool $shareable = true
    ): Share {
        $model = $this->createModel($passwordId, $receiverId, $type, $editable, $expires, $shareable);
        $this->fireEvent('instantiated', $model);

        return $model;
    }

    /**
     * Create the root share of a folder share. Unlike a password share the root
     * references a source folder instead of a source password. The actual cloning
     * of the folder tree (folders and passwords) happens in the SynchronizeShares
     * cron.
     *
     * @param string   $folderId   uuid of the shared source folder
     * @param string   $receiverId user who receives the folder share
     * @param string   $type       'user' or 'group'
     * @param bool     $editable   whether the receiver may edit the clones
     * @param int|null $expires    optional expiry timestamp
     * @param bool     $shareable  whether the receiver may re-share the clones
     *
     * @return Share|ModelInterface
     */
    public function createFolderShare(
        string $folderId,
        string $receiverId,
        string $type,
        bool $editable,
        ?int $expires = null,
        bool $shareable = true
    ): Share {
        $model = new Share();
        $model->setDeleted(false);
        $model->setUserId($this->userId);
        $model->setUuid($this->uuidHelper->generateUuid());
        $model->setCreated(time());
        $model->setUpdated(time());

        $model->setSourceFolder($folderId);
        $model->setSourceUpdated(true);
        $model->setReceiver($receiverId);
        $model->setShareable($shareable);
        $model->setEditable($editable);
        $model->setExpires($expires);
        $model->setType($type);
        $model->setClient($this->environment->getClient());

        $this->fireEvent('instantiated', $model);

        return $model;
    }

    /**
     * Create a child share of a folder share for a single password inside the
     * shared folder tree. A child share links a source password to its cloned
     * target password and carries the same source folder as its root. It inherits
     * the receiver, type, editable and shareable flags from the folder-share root
     * so the existing password-share sync machinery treats it like any other share.
     *
     * @param string   $sharerId       owner of the share (the folder-share root's user)
     * @param string   $sourceFolder   uuid of the shared source folder (folder-share root)
     * @param string   $sourcePassword uuid of the source password
     * @param string   $targetPassword uuid of the cloned (target) password
     * @param string   $receiverId     user who receives the share
     * @param string   $type           'user' or 'group'
     * @param bool     $editable       whether the receiver may edit the clone
     * @param int|null $expires        optional expiry timestamp
     * @param bool     $shareable      whether the receiver may re-share the clone
     *
     * @return Share|ModelInterface
     */
    public function createFolderChildShare(
        string $sharerId,
        string $sourceFolder,
        string $sourcePassword,
        string $targetPassword,
        string $receiverId,
        string $type,
        bool $editable,
        ?int $expires = null,
        bool $shareable = true
    ): Share {
        $model = new Share();
        $model->setDeleted(false);
        $model->setUserId($sharerId);
        $model->setUuid($this->uuidHelper->generateUuid());
        $model->setCreated(time());
        $model->setUpdated(time());

        $model->setSourceFolder($sourceFolder);
        $model->setSourcePassword($sourcePassword);
        $model->setTargetPassword($targetPassword);
        // The clone is created from the current source state, so both sides are
        // already in sync at creation time.
        $model->setSourceUpdated(false);
        $model->setTargetUpdated(false);
        $model->setReceiver($receiverId);
        $model->setShareable($shareable);
        $model->setEditable($editable);
        $model->setExpires($expires);
        $model->setType($type);
        $model->setClient($this->environment->getClient());

        $this->fireEvent('instantiated', $model);

        return $model;
    }

    /**
     * @param string   $passwordId
     * @param string   $receiverId
     * @param string   $type
     * @param bool     $editable
     * @param int|null $expires
     * @param bool     $shareable
     *
     * @return Share
     */
    protected function createModel(
        string $passwordId,
        string $receiverId,
        string $type,
        bool $editable,
        ?int $expires,
        bool $shareable
    ): Share {

        $model = new Share();
        $model->setDeleted(false);
        $model->setUserId($this->userId);
        $model->setUuid($this->uuidHelper->generateUuid());
        $model->setCreated(time());
        $model->setUpdated(time());

        $model->setSourcePassword($passwordId);
        $model->setSourceUpdated(true);
        $model->setReceiver($receiverId);
        $model->setShareable($shareable);
        $model->setEditable($editable);
        $model->setExpires($expires);
        $model->setType($type);
        $model->setClient($this->environment->getClient());

        return $model;
    }

    /**
     * @param Share|EntityInterface $original
     * @param array                 $overwrites
     *
     * @return Share
     */
    protected function cloneModel(EntityInterface $original, array $overwrites = []): EntityInterface {

        /** @var Share $clone */
        $clone = parent::cloneModel($original, $overwrites);
        $clone->setUuid($this->uuidHelper->generateUuid());
        $clone->setClient($this->environment->getClient());

        return $clone;
    }
}
