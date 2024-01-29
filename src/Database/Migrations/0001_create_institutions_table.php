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
    PRIMARY KEY (id)
) {$wpdb->get_charset_collate()}
SQL;

        $wpdb->query($sql);
    }
};
