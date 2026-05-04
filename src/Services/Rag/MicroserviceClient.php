<?php
declare(strict_types=1);

namespace PDFChatRAG\Services\Rag;

/**
 * Client for Python FastAPI microservice handling heavy RAG operations
 */
class MicroserviceClient {
    private string $baseUrl;
    private string $apiKey;

    public function __construct() {
        $this->baseUrl = rtrim(get_option('pdf_chat_rag_service_url', 'http://localhost:8000'), '/');
        $this->apiKey = get_option('pdf_chat_rag_service_key', '');
    }

    public function query(array $payload): array {
        $response = wp_remote_post("{$this->baseUrl}/query", [
            'headers' => [
                'Content-Type' => 'application/json',
                'X-API-Key'    => $this->apiKey,
            ],
            'body'    => wp_json_encode($payload),
            'timeout' => 60,
        ]);

        if (is_wp_error($response)) {
            throw new \Exception('Microservice error: ' . $response->get_error_message());
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        if ($code !== 200 || empty($body)) {
            throw new \Exception('Invalid microservice response');
        }

        return $body;
    }

    public function healthCheck(): bool {
        $response = wp_remote_get("{$this->baseUrl}/health", ['timeout' => 5]);
        return !is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200;
    }
}