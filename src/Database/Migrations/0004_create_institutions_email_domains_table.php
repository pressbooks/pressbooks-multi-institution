<?php

use PressbooksMultiInstitution\Interfaces\MigrationInterface;

return new class implements MigrationInterface {
    public function up(): void
    {
        global $wpdb;

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS {$wpdb->base_prefix}institutions_email_domains (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    institution_id BIGINT UNSIGNED NOT NULL,
    domain VARCHAR(255) NOT NULL,
    created_at datetime NOT NULL,
    updated_at datetime NOT NULL,
    PRIMARY KEY (id),
    UNIQUE (institution_id, domain),
    FOREIGN KEY (institution_id) REFERENCES {$wpdb->base_prefix}institutions(id) ON DELETE CASCADE
) {$wpdb->get_charset_collate()}
SQL;

        $wpdb->query($sql);
    }

    public function down(): void
    {
        global $wpdb;

        $sql = <<<SQL
DROP TABLE IF EXISTS {$wpdb->base_prefix}institutions_email_domains
SQL;

        $wpdb->query($sql);
    }
};
