<?php
declare(strict_types=1);

namespace PDFChatRAG\Services\Contracts;

interface VectorStoreInterface {
    public function store(string $documentId, array $chunks, array $embeddings): bool;
    public function search(array $vector, int $topK = 5): array;
    public function delete(string $documentId): bool;
}