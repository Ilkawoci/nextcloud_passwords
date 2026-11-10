<?php

declare(strict_types=1);

namespace OCA\Passwords\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * Class Version20261110
 *
 * Adds folder-sharing support to the share table:
 *  - source_password becomes nullable (folder-share roots have no source password)
 *  - source_folder / target_folder columns (nullable uuids)
 *  - index on source_folder
 *
 * @package OCA\Passwords\Migration
 */
class Version20261110 extends SimpleMigrationStep {
    /**
     * Human readable name of the migration step
     *
     * @return string
     */
    public function name(): string {
        return 'Add folder sharing support';
    }

    /**
     * Human readable description of the migration step
     *
     * @return string
     */
    public function description(): string {
        return 'Make source_password nullable and add source_folder/target_folder columns to the share table to allow sharing whole folders.';
    }

    /**
     * @param IOutput $output
     * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
     * @param array $options
     * @return null|ISchemaWrapper
     */
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();
        $table  = $schema->getTable('passwords_share');

        if($table->hasColumn('source_password')) {
            $output->info('Making source_password column of table passwords_share nullable');
            $table->setNotnull('source_password', false);
        }

        if(!$table->hasColumn('source_folder')) {
            $output->info('Creating column source_folder in table passwords_share');
            $table->addColumn(
                'source_folder',
                'string',
                [
                    'notnull' => false,
                    'length'  => 36,
                ]
            );
        }

        if(!$table->hasColumn('target_folder')) {
            $output->info('Creating column target_folder in table passwords_share');
            $table->addColumn(
                'target_folder',
                'string',
                [
                    'notnull' => false,
                    'length'  => 36,
                ]
            );
        }

        if(!$table->hasIndex('share_source_folder_index')) {
            $table->addIndex(['source_folder'], 'share_source_folder_index');
        }

        // Cloned folders (created by folder shares) keep a back-reference to the
        // originating share and source folder so the cron can re-map the tree
        // idempotently across runs.
        $folderTable = $schema->getTable('passwords_folder');

        if(!$folderTable->hasColumn('share_id')) {
            $output->info('Creating column share_id in table passwords_folder');
            $folderTable->addColumn(
                'share_id',
                'string',
                [
                    'notnull' => false,
                    'length'  => 36,
                ]
            );
        }

        if(!$folderTable->hasColumn('source_folder')) {
            $output->info('Creating column source_folder in table passwords_folder');
            $folderTable->addColumn(
                'source_folder',
                'string',
                [
                    'notnull' => false,
                    'length'  => 36,
                ]
            );
        }

        if(!$folderTable->hasIndex('folder_share_source_index')) {
            $folderTable->addIndex(['share_id', 'source_folder'], 'folder_share_source_index');
        }

        return $schema;
    }
}
