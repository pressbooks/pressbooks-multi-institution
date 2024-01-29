<?php

use PressbooksMultiInstitution\Interfaces\MigrationInterface;

return new class implements MigrationInterface {
    public function up(): void
    {
        global $wpdb;

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS {$wpdb->base_prefix}institutions_users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    institution_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    UNIQUE (user_id, institution_id),
    FOREIGN KEY (user_id) REFERENCES {$wpdb->users}(ID) ON DELETE CASCADE,
    FOREIGN KEY (institution_id) REFERENCES {$wpdb->base_prefix}institutions(id) ON DELETE CASCADE
) {$wpdb->get_charset_collate()}
SQL;

        $wpdb->query($sql);
    }
};
