<?php

declare(strict_types=1);

namespace OCA\Passwords\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * Class Version20260115
 *
 * Adds folder sharing support. Shares can now reference a folder tree instead
 * of a single password. Every element of a shared folder tree gets its own
 * share row so that the existing synchronization cron keeps working.
 *
 * @package OCA\Passwords\Migration
 */
class Version20260115 extends SimpleMigrationStep {

    /**
     * Human readable name of the migration step
     *
     * @return string
     */
    public function name(): string {
        return 'Add folder sharing';
    }

    /**
     * Human readable description of the migration step
     *
     * @return string
     */
    public function description(): string {
        return 'Adds folder reference columns to the share table and share flags to the folder table';
    }

    /**
     * @param IOutput $output
     * @param Closure $schemaClosure
     * @param array   $options
     *
     * @return null|ISchemaWrapper
     */
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();

        $table = $schema->getTable('passwords_share');

        if(!$table->hasColumn('source_type')) {
            $output->info('Creating column source_type in table passwords_share');
            $table->addColumn(
                'source_type',
                'string',
                [
                    'notnull' => true,
                    'length'  => 16,
                    'default' => 'password',
                ]
            );
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

        if(!$table->hasColumn('parent_share')) {
            $output->info('Creating column parent_share in table passwords_share');
            $table->addColumn(
                'parent_share',
                'string',
                [
                    'notnull' => false,
                    'length'  => 36,
                ]
            );
        }

        if($table->hasColumn('source_password') && $table->getColumn('source_password')->getNotnull()) {
            $output->info('Allowing NULL values in column source_password of table passwords_share');
            $table->getColumn('source_password')->setNotnull(false);
        }

        if(!$table->hasIndex('share_source_folder_index')) {
            $output->info('Creating index share_source_folder_index in table passwords_share');
            $table->addIndex(['source_folder'], 'share_source_folder_index');
        }

        if(!$table->hasIndex('share_parent_share_index')) {
            $output->info('Creating index share_parent_share_index in table passwords_share');
            $table->addIndex(['parent_share'], 'share_parent_share_index');
        }

        if(!$table->hasIndex('share_source_type_index')) {
            $output->info('Creating index share_source_type_index in table passwords_share');
            $table->addIndex(['source_type', 'receiver'], 'share_source_type_index');
        }

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

        if(!$folderTable->hasColumn('has_shares')) {
            $output->info('Creating column has_shares in table passwords_folder');
            $folderTable->addColumn(
                'has_shares',
                'boolean',
                [
                    'notnull' => false,
                    'default' => false,
                ]
            );
        }

        return $schema;
    }
}
