<?php
/**
 * This file is part of the Passwords App
 * created by Marius David Wieschollek
 * and licensed under the AGPL.
 */

namespace OCA\Passwords\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

/**
 * Class FolderMapper
 *
 * @package OCA\Passwords\Db
 */
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
     * Find the cloned folder that the given share produced for the given source
     * folder. Used to make folder-share tree cloning idempotent.
     *
     * @param string $shareId
     * @param string $sourceFolder
     *
     * @return null|Folder
     * @throws DoesNotExistException
     * @throws MultipleObjectsReturnedException
     */
    public function findCloneByShareAndSource(string $shareId, string $sourceFolder): ?Folder {
        $sql = $this->getStatement();

        $sql->andWhere(
            $sql->expr()->eq('share_id', $sql->createNamedParameter($shareId)),
            $sql->expr()->eq('source_folder', $sql->createNamedParameter($sourceFolder))
        );

        // findEntity() throws DoesNotExistException on an empty result, but a
        // missing clone is the normal (not-yet-materialized) state — return null.
        $clones = $this->findEntities($sql);

        return count($clones) ? $clones[0] : null;
    }

}