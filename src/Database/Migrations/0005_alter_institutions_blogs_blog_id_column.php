<?php

use Illuminate\Database\Schema\Builder;
use PressbooksMultiInstitution\Interfaces\MigrationInterface;

return new class implements MigrationInterface {
    public function up(): void
    {
        /** @var Builder $schema */
        $schema = app('db')->schema();

        if (! $schema->hasTable('institutions_blogs')) {
            return;
        }

        $connection = app('db')->connection();

        $table = $connection->getTablePrefix() . 'institutions_blogs';

        $column = $connection->selectOne(
            "SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = 'blog_id'",
            [$table]
        );

        if (! $column || str_contains(strtolower($column->COLUMN_TYPE), 'unsigned')) {
            return;
        }

        $connection->statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `institutions_blogs_blog_id_foreign`");
        $connection->statement("ALTER TABLE `{$table}` MODIFY `blog_id` BIGINT UNSIGNED NOT NULL");
        $connection->statement(
            "ALTER TABLE `{$table}` ADD CONSTRAINT `institutions_blogs_blog_id_foreign` FOREIGN KEY (`blog_id`) REFERENCES `{$connection->getTablePrefix()}blogs` (`blog_id`) ON DELETE CASCADE"
        );
    }

    public function down(): void
    {
        // Intentionally left empty - no need to revert to signed bigint
    }
};
