<?php
declare(strict_types=1);

namespace PDFChatRAG\Services;

use PDFChatRAG\Services\Contracts\LlmProviderInterface;

class GeminiClient implements LlmProviderInterface {
    private string $chatBaseUrl = 'https://generativelanguage.googleapis.com/v1beta/openai';
    private string $embeddingBaseUrl = 'https://generativelanguage.googleapis.com/v1beta/models';
    private string $chatModel = 'gemini-2.5-flash';
    private string $embeddingModel = 'gemini-embedding-001';

    private function getApiKey(): string {
        if (defined('PDF_CHAT_RAG_GEMINI_API_KEY')) {
            return PDF_CHAT_RAG_GEMINI_API_KEY;
        }
        return get_option('pdf_chat_rag_gemini_api_key', '');
    }

    public function generateResponse(string $message, array $context, array $history): string {
        $contextText = '';
        if (!empty($context)) {
            $parts = [];
            foreach ($context as $c) {
                $source = $c['metadata']['source'] ?? 'PDF';
                $parts[] = "[Source: {$source}]\n" . $c['text'];
            }
            $contextText = implode("\n\n---\n\n", $parts);
        }

        $systemPrompt = "You are a helpful assistant that answers questions based on uploaded PDF documents.\nUse ONLY the provided context. If the answer is not in the context, say you don't know.\n\nContext:\n" . $contextText;

        $messages = [['role' => 'system', 'content' => $systemPrompt]];

        foreach ($history as $h) {
            $messages[] = ['role' => $h['role'], 'content' => $h['content']];
        }

        $messages[] = ['role' => 'user', 'content' => $message];

        $response = wp_remote_post("{$this->chatBaseUrl}/chat/completions", [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getApiKey(),
                'Content-Type'  => 'application/json',
            ],
            'body'    => wp_json_encode([
                'model'       => $this->chatModel,
                'messages'    => $messages,
                'temperature' => 0.3,
                'max_tokens'  => 1500,
            ]),
            'timeout' => 60,
        ]);

        if (is_wp_error($response)) {
            throw new \Exception('LLM error: ' . $response->get_error_message());
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        if ($code !== 200 || empty($body['choices'][0]['message']['content'])) {
            $errorMessage = $body['error']['message'] ?? 'Unknown error';
            throw new \Exception('LLM API error: HTTP ' . $code . ' — ' . $errorMessage);
        }

        return $body['choices'][0]['message']['content'];
    }

    public function createEmbeddings(string $text): array {
        $result = $this->createEmbeddingsBatch([$text]);
        return $result[0] ?? [];
    }

    public function createEmbeddingsBatch(array $texts): array {
        if (empty($texts)) {
            return [];
        }

        $apiKey = $this->getApiKey();
        $embeddings = [];

        foreach ($texts as $text) {
            $response = wp_remote_post("{$this->embeddingBaseUrl}/{$this->embeddingModel}:embedContent?key={$apiKey}", [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body'    => wp_json_encode([
                    'content' => [
                        'parts' => [['text' => $text]],
                    ],
                ]),
                'timeout' => 60,
            ]);

            if (is_wp_error($response)) {
                throw new \Exception('Embedding error: ' . $response->get_error_message());
            }

            $code = wp_remote_retrieve_response_code($response);
            $body = json_decode(wp_remote_retrieve_body($response), true);

            if ($code !== 200 || empty($body['embedding']['values'])) {
                $errorMessage = $body['error']['message'] ?? 'Unknown error';
                throw new \Exception('Embedding API error: HTTP ' . $code . ' — ' . $errorMessage);
            }

            $embeddings[] = $body['embedding']['values'];
        }

        return $embeddings;
    }
}
