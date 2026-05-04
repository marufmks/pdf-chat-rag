<?php
declare(strict_types=1);

namespace PDFChatRAG\Database\Migrations;

class ChatHistoryTable {
    public static function up(): void {
        global $wpdb;
        $table = $wpdb->prefix . 'pdf_chat_history';
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            session_id varchar(100) NOT NULL,
            user_id bigint(20) unsigned DEFAULT NULL,
            message text NOT NULL,
            response longtext,
            context longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY session_id (session_id),
            KEY user_id (user_id),
            KEY created_at (created_at)
        ) {$charset};";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}