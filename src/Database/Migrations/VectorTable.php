<?php
declare(strict_types=1);

namespace PDFChatRAG\Database\Migrations;

class VectorTable {
    public static function up(): void {
        global $wpdb;
        $table   = $wpdb->prefix . 'pdf_vectors';
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            document_id varchar(100) NOT NULL,
            chunk_index int(11) NOT NULL DEFAULT 0,
            chunk_text longtext NOT NULL,
            embedding longtext NOT NULL,
            source varchar(255) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY document_id (document_id)
        ) {$charset};";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}
