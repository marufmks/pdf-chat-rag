<?php
declare(strict_types=1);

namespace PDFChatRAG\Services;

use PDFChatRAG\Services\Contracts\PdfParserInterface;
use Smalot\PdfParser\Parser;

class WpPdfParser implements PdfParserInterface {
    public function extractText(string $filePath): string {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException('PDF not found');
        }

        $parser = new Parser();
        $pdf    = $parser->parseFile($filePath);
        $text   = $pdf->getText();

        return $text ?: '';
    }
}
