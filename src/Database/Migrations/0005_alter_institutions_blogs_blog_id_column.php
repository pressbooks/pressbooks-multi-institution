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
        $prefix = $connection->getTablePrefix();

        // Only proceed if wp_blogs.blog_id is already unsigned (WP 6.9+).
        $blogsColumn = $connection->selectOne(
            "SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = 'blog_id'",
            [$prefix . 'blogs']
        );

        if (! $blogsColumn || ! str_contains(strtolower($blogsColumn->COLUMN_TYPE), 'unsigned')) {
            return;
        }

        $table = $prefix . 'institutions_blogs';

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
            "ALTER TABLE `{$table}` ADD CONSTRAINT `institutions_blogs_blog_id_foreign` FOREIGN KEY (`blog_id`) REFERENCES `{$prefix}blogs` (`blog_id`) ON DELETE CASCADE"
        );
    }

    public function down(): void
    {
        // No need to revert, as the change is non-destructive and compatible with both signed and unsigned blog_id.
    }
};
