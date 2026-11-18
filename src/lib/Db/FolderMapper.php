<?php
/**
 * This file is part of the Passwords App
 * created by Marius David Wieschollek
 * and licensed under the AGPL.
 */

namespace OCA\PasswordFolders\Db;

/**
 * Class FolderMapper
 *
 * @package OCA\PasswordFolders\Db
 */
use OCP\DB\QueryBuilder\IQueryBuilder;

class FolderMapper extends AbstractMapper {

    const string TABLE_NAME = 'passwords_folder';

    /**
     * @param string $parentUuid
     *
     * @return Folder[]
     */
    public function findAllByParentFolder(string $parentUuid): array {
        $sql = $this->getJoinStatement(FolderRevisionMapper::TABLE_NAME);

        $sql->andWhere(
            $sql->expr()->eq('b.parent', $sql->createNamedParameter($parentUuid))
        );

        return $this->findEntities($sql);
    }

    /**
     * Folders that were created for a share whose share row is gone or deleted
     *
     * @return Folder[]
     * @throws Exception
     */
    public function findAllOrphanedTargetFolders(): array {
        $qb = $this->db->getQueryBuilder();

        $qb->select('a.*')
           ->from(static::TABLE_NAME, 'a')
           ->leftJoin('a', ShareMapper::TABLE_NAME, 'b', 'a.`share_id` = b.`uuid`')
           ->where($qb->expr()->isNotNull('a.share_id'))
           ->andWhere($qb->expr()->eq('a.deleted', $qb->createNamedParameter(false, IQueryBuilder::PARAM_BOOL)))
           ->andWhere(
               $qb->expr()->orX(
                   $qb->expr()->isNull('b.uuid'),
                   $qb->expr()->eq('b.deleted', $qb->createNamedParameter(true, IQueryBuilder::PARAM_BOOL))
               )
           );

        /** @var Folder[] $folders */
        $folders = $this->findEntities($qb);

        return $folders;
    }

}
