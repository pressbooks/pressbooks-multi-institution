<?php

use Illuminate\Database\Schema\Builder;
use PressbooksMultiInstitution\Interfaces\MigrationInterface;

return new class implements MigrationInterface {
    public function up(): void
    {
        $this->dropInstitutionsBlogsForeignKeys();

        $this->dropInstitutionsUsersForeignKeys();
    }

    public function down(): void
    {
    }

    protected function dropInstitutionsBlogsForeignKeys(): void
    {
        $connection = app('db')->connection();

        $table = $connection->getTablePrefix() . 'institutions_blogs';

        $hasForeignKey = $connection->selectOne(
            "SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? AND CONSTRAINT_TYPE = 'FOREIGN KEY'",
            [$table, 'institutions_blogs_blog_id_foreign']
        );

        if (! $hasForeignKey) {
            return;
        }

        $connection->statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `institutions_blogs_blog_id_foreign`");
    }

    protected function dropInstitutionsUsersForeignKeys(): void
    {
        $connection = app('db')->connection();

        $table = $connection->getTablePrefix() . 'institutions_users';

        $hasForeignKey = $connection->selectOne(
            "SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? AND CONSTRAINT_TYPE = 'FOREIGN KEY'",
            [$table, 'institutions_users_user_id_foreign']
        );

        if (! $hasForeignKey) {
            return;
        }

        $connection->statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `institutions_users_user_id_foreign`");
    }
};
