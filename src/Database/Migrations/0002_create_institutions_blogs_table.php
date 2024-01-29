<?php

use PressbooksPluginScaffold\Interfaces\MigrationInterface;

return new class implements MigrationInterface {
    public function up(): void
    {
        global $wpdb;

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS {$wpdb->base_prefix}institutions_blogs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    blog_id BIGINT NOT NULL,
    institution_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    UNIQUE (blog_id, institution_id),
    FOREIGN KEY (blog_id) REFERENCES {$wpdb->blogs}(blog_id) ON DELETE CASCADE,
    FOREIGN KEY (institution_id) REFERENCES {$wpdb->base_prefix}institutions(id) ON DELETE CASCADE
) {$wpdb->get_charset_collate()}
SQL;

        $wpdb->query($sql);
    }
};
