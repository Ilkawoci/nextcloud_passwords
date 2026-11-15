<?php
/**
 * This file is part of the Passwords App
 * created by Marius David Wieschollek
 * and licensed under the AGPL.
 */

namespace OCA\Passwords\Db;

use Exception;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\DB\QueryBuilder\IQueryBuilder;

/**
 * Class ShareMapper
 *
 * @package OCA\Passwords\Db
 */
class ShareMapper extends AbstractMapper {

    const string TABLE_NAME = 'passwords_share';

    /**
     * @param string $userId
     *
     * @return Share[]
     * @throws Exception
     */
    public function findAllByUserIdOrReceiverId(string $userId): array {
        $sql = $this->getStatement();

        $sql->orWhere(
            $sql->expr()->eq('user_id', $sql->createNamedParameter($userId)),
            $sql->expr()->eq('receiver', $sql->createNamedParameter($userId))
        );

        return $this->findEntities($sql);
    }

    /**
     * @param string $folderUuid
     *
     * @return Share[]
     * @throws Exception
     */
    public function findAllBySourceFolder(string $folderUuid): array {
        /** @var Share[] $shares */
        $shares = $this->findAllByField('source_folder', $folderUuid);

        return $shares;
    }

    /**
     * @param string $shareUuid
     *
     * @return Share[]
     * @throws Exception
     */
    public function findAllByParentShare(string $shareUuid): array {
        /** @var Share[] $shares */
        $shares = $this->findAllByField('parent_share', $shareUuid);

        return $shares;
    }

    /**
     * @param string $folderUuid
     * @param string $receiver
     *
     * @return Share|null
     * @throws Exception
     */
    public function findOneBySourceFolderAndReceiver(string $folderUuid, string $receiver): ?Share {
        try {
            /** @var Share $share */
            $share = $this->findOneByFields(
                ['source_folder', $folderUuid],
                ['receiver', $receiver]
            );
        } catch(DoesNotExistException|MultipleObjectsReturnedException $e) {
            return null;
        }

        return $share;
    }

    /**
     * @param string $userId
     *
     * @return Share[]
     * @throws Exception
     */
    public function findAllByReceiverId(string $userId): array {
        /** @var Share[] $shares */
        $shares = $this->findAllByField('receiver', $userId);

        return $shares;
    }

    /**
     * Find all shares that still need to be materialized
     *
     * @return Share[]
     * @throws Exception
     */
    public function findAllPending(): array {
        $qb = $this->db->getQueryBuilder();

        $qb->select('*')
           ->from(static::TABLE_NAME)
           ->where(
               $qb->expr()->eq('deleted', $qb->createNamedParameter(false, IQueryBuilder::PARAM_BOOL))
           )
           ->andWhere(
               $qb->expr()->orX(
                   $qb->expr()->andX(
                       $qb->expr()->eq('source_type', $qb->createNamedParameter(Share::ENTITY_PASSWORD)),
                       $qb->expr()->isNull('target_password')
                   ),
                   $qb->expr()->andX(
                       $qb->expr()->eq('source_type', $qb->createNamedParameter(Share::ENTITY_FOLDER)),
                       $qb->expr()->isNull('target_folder')
                   )
               )
           );

        /** @var Share[] $shares */
        $shares = $this->findEntities($qb);

        return $shares;
    }

    /**
     * Returns all folder shares that are the root of a shared tree
     *
     * @return Share[]
     * @throws Exception
     */
    public function findAllRootFolderShares(): array {
        $qb = $this->getStatement();

        $qb->andWhere(
            $qb->expr()->eq('source_type', $qb->createNamedParameter(Share::ENTITY_FOLDER))
        )->andWhere(
            $qb->expr()->isNull('parent_share')
        )->andWhere(
            $qb->expr()->isNotNull('target_folder')
        );

        /** @var Share[] $shares */
        $shares = $this->findEntities($qb);

        return $shares;
    }

    /**
     * @return IQueryBuilder
     */
    protected function getStatement(): IQueryBuilder {
        $qb = $this->db->getQueryBuilder();

        $qb->select('*')
           ->from(static::TABLE_NAME)
           ->where(
               $qb->expr()->eq('deleted', $qb->createNamedParameter(false, IQueryBuilder::PARAM_BOOL))
           );

        if($this->userId !== null) {
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->eq('user_id', $qb->createNamedParameter($this->userId)),
                    $qb->expr()->andX(
                        $qb->expr()->eq('receiver', $qb->createNamedParameter($this->userId)),
                        $qb->expr()->orX(
                            $qb->expr()->isNotNull('target_password'),
                            $qb->expr()->isNotNull('target_folder')
                        )
                    )
                )
            );
        }

        return $qb;
    }
}