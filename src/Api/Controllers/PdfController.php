<?php
declare(strict_types=1);

namespace PDFChatRAG\Api\Controllers;

use WP_REST_Request;
use WP_REST_Response;
use PDFChatRAG\Services\WpPdfParser;
use PDFChatRAG\Services\TextChunker;
use PDFChatRAG\Services\GeminiClient;
use PDFChatRAG\Services\PhpVectorStore;

class PdfController {
    public function upload(WP_REST_Request $request): WP_REST_Response {
        $files = $request->get_file_params();

        if (empty($files['file'])) {
            return new WP_REST_Response(['error' => 'No file uploaded'], 400);
        }

        $file = $files['file'];

        $fileType = wp_check_filetype($file['name'], ['pdf' => 'application/pdf']);
        if (empty($fileType['ext'])) {
            return new WP_REST_Response(['error' => 'Only PDF files allowed'], 400);
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return new WP_REST_Response(['error' => 'File upload failed'], 500);
        }

        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }

        $upload = wp_handle_upload($file, ['test_form' => false]);
        if (isset($upload['error'])) {
            return new WP_REST_Response(['error' => $upload['error']], 500);
        }

        try {
            $parser = new WpPdfParser();
            $text   = $parser->extractText($upload['file']);

            if (empty(trim($text))) {
                wp_delete_file($upload['file']);
                return new WP_REST_Response(['error' => 'No text extracted. Scanned PDFs are not supported.'], 400);
            }

            $chunker = new TextChunker();
            $chunks  = $chunker->split($text, 1000, 200);

            if (empty($chunks)) {
                wp_delete_file($upload['file']);
                return new WP_REST_Response(['error' => 'Could not chunk document'], 400);
            }

            $gemini     = new GeminiClient();
            $embeddings = $gemini->createEmbeddingsBatch($chunks);

            $documentId = uniqid('doc_', true);
            $store      = new PhpVectorStore();
            $store->store($documentId, $chunks, $embeddings, ['filename' => $file['name']]);

            wp_delete_file($upload['file']);

            return new WP_REST_Response([
                'success'     => true,
                'document_id' => $documentId,
                'filename'    => $file['name'],
                'chunks'      => count($chunks),
            ], 201);

        } catch (\Exception $e) {
            if (file_exists($upload['file'])) {
                wp_delete_file($upload['file']);
            }
            return new WP_REST_Response(['error' => $e->getMessage()], 500);
        }
    }
}
