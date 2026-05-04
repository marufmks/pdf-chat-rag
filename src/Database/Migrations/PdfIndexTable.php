<?php
declare(strict_types=1);

namespace PDFChatRAG\Database\Migrations;

class PdfIndexTable {
    public static function up(): void {
        global $wpdb;
        $table = $wpdb->prefix . 'pdf_index';
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            document_id varchar(100) NOT NULL,
            filename varchar(255) NOT NULL,
            total_chunks int(11) unsigned DEFAULT 0,
            status varchar(20) DEFAULT 'indexed',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY document_id (document_id),
            KEY status (status),
            KEY created_at (created_at)
        ) {$charset};";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}
