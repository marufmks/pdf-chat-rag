<?php
declare(strict_types=1);

namespace PDFChatRAG\Core;

use PDFChatRAG\Database\Migrations\ChatHistoryTable;
use PDFChatRAG\Database\Migrations\PdfIndexTable;

class Activator {
    public static function activate(): void {
        ChatHistoryTable::up();
        PdfIndexTable::up();
        flush_rewrite_rules();
    }
}