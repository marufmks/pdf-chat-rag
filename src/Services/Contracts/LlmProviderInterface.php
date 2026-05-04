<?php
declare(strict_types=1);

namespace PDFChatRAG\Services\Contracts;

interface LlmProviderInterface {
    public function generateResponse(string $message, array $context, array $history): string;
    public function createEmbeddings(string $text): array;
}