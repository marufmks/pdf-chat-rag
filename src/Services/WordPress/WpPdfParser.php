<?php
declare(strict_types=1);

namespace PDFChatRAG\Services\WordPress;

use PDFChatRAG\Services\Contracts\PdfParserInterface;

class WpPdfParser implements PdfParserInterface {
    public function extractText(string $filePath): string {
        if (!class_exists('\Smalot\PdfParser\Parser')) {
            throw new \RuntimeException(
                'PDF parser library is not installed. Run: composer require smalot/pdfparser'
            );
        }

        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile($filePath);

        return $pdf->getText();
    }
}
