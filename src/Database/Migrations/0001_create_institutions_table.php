<?php

use PressbooksMultiInstitution\Interfaces\MigrationInterface;

return new class implements MigrationInterface {
    public function up(): void
    {
        global $wpdb;

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS {$wpdb->base_prefix}institutions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    book_limit INT UNSIGNED NULL,
    user_limit INT UNSIGNED NULL,
    created_at datetime NOT NULL,
    updated_at datetime NOT NULL,
    PRIMARY KEY (id)
) {$wpdb->get_charset_collate()}
SQL;

        $wpdb->query($sql);
    }

    public function down(): void
    {
        global $wpdb;

        $sql = <<<SQL
DROP TABLE IF EXISTS {$wpdb->base_prefix}institutions
SQL;

        $wpdb->query($sql);
    }
};
