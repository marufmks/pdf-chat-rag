<?php
declare(strict_types=1);

namespace PDFChatRAG\Api\Controllers;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use PDFChatRAG\Services\Rag\MicroserviceClient;
use PDFChatRAG\Database\Repository\PdfRepository;

class PdfController {
    private MicroserviceClient $client;
    private PdfRepository $repository;

    public function __construct() {
        $this->client = new MicroserviceClient();
        $this->repository = new PdfRepository();
    }

    public function upload(WP_REST_Request $request): WP_REST_Response {
        $files = $request->get_file_params();

        if (empty($files['file'])) {
            return new WP_REST_Response(
                ['error' => 'No file uploaded'],
                400
            );
        }

        $file = $files['file'];

        if (!$this->isValidPdf($file)) {
            return new WP_REST_Response(
                ['error' => 'Only PDF files are allowed'],
                400
            );
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return new WP_REST_Response(
                ['error' => 'File upload failed'],
                500
            );
        }

        try {
            $result = $this->client->uploadPdf($file['tmp_name'], $file['name']);

            $this->repository->save(
                $result['document_id'],
                $file['name'],
                $result['chunks'] ?? 0
            );

            return new WP_REST_Response([
                'success'      => true,
                'document_id'  => $result['document_id'],
                'chunks'       => $result['chunks'] ?? 0,
            ], 200);
        } catch (\Exception $e) {
            return new WP_REST_Response(
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    private function isValidPdf(array $file): bool {
        $allowedMimeTypes = ['application/pdf'];
        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($fileInfo, $file['tmp_name']);
        finfo_close($fileInfo);

        return in_array($mimeType, $allowedMimeTypes, true);
    }
}
