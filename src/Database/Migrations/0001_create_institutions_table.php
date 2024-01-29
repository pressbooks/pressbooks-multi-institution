<?php

use PressbooksMultiInstitution\Interfaces\MigrationInterface;

return new class implements MigrationInterface {
    public function up(): void
    {
        global $wpdb;

        // TODO: add the required fields
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS {$wpdb->base_prefix}institutions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    book_limit SMALLINT UNSIGNED NULL,
    user_limit SMALLINT UNSIGNED NULL,
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
