<?php
declare(strict_types=1);

namespace PDFChatRAG\Core;

use PDFChatRAG\Database\Migrations\ChatHistoryTable;
use PDFChatRAG\Database\Migrations\PdfIndexTable;
use PDFChatRAG\Database\Migrations\VectorTable;

class Activator {
    private const DB_VERSION = '1.1.0';

    public static function activate(): void {
        self::runMigrations();
        update_option('pdf_chat_rag_db_version', self::DB_VERSION);
        flush_rewrite_rules();
    }

    public static function ensureTables(): void {
        if (get_option('pdf_chat_rag_db_version') !== self::DB_VERSION) {
            self::runMigrations();
            update_option('pdf_chat_rag_db_version', self::DB_VERSION);
        }
    }

    private static function runMigrations(): void {
        ChatHistoryTable::up();
        PdfIndexTable::up();
        VectorTable::up();
    }
}
