<?php
declare(strict_types=1);

namespace PDFChatRAG\Services\Contracts;

interface PdfParserInterface {
    public function extractText(string $filePath): string;
}