<?php
declare(strict_types=1);

namespace PDFChatRAG\Services;

use PDFChatRAG\Services\Contracts\VectorStoreInterface;

class PhpVectorStore implements VectorStoreInterface {
    private string $table;

    public function __construct() {
        global $wpdb;
        $this->table = $wpdb->prefix . 'pdf_vectors';
    }

    public function store(string $documentId, array $chunks, array $embeddings, array $metadata = []): bool {
        global $wpdb;

        if (count($chunks) !== count($embeddings)) {
            throw new \InvalidArgumentException('Chunk/embedding count mismatch');
        }

        foreach ($chunks as $i => $chunk) {
            $wpdb->insert($this->table, [
                'document_id' => $documentId,
                'chunk_index' => $i,
                'chunk_text'  => $chunk,
                'embedding'   => wp_json_encode($embeddings[$i]),
                'source'      => $metadata['filename'] ?? 'unknown',
            ], ['%s', '%d', '%s', '%s', '%s']);
        }

        return true;
    }

    public function search(array $vector, int $topK = 5): array {
        global $wpdb;

        $rows = $wpdb->get_results("SELECT * FROM {$this->table}", ARRAY_A);

        if (empty($rows)) {
            return [];
        }

        error_log('VectorStore search: ' . count($rows) . ' rows, query vector length: ' . count($vector));

        $scored = [];
        foreach ($rows as $row) {
            $embedding = json_decode($row['embedding'], true);
            if (!is_array($embedding)) {
                error_log('Failed to decode embedding for row ' . $row['id']);
                continue;
            }

            $score = $this->cosineSimilarity($vector, $embedding);
            error_log('Row ' . $row['id'] . ' score: ' . $score);

            $scored[] = [
                'score'    => $score,
                'text'     => $row['chunk_text'],
                'metadata' => [
                    'document_id' => $row['document_id'],
                    'source'      => $row['source'],
                    'chunk_index' => (int) $row['chunk_index'],
                ],
            ];
        }

        usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);
        error_log('Top score: ' . ($scored[0]['score'] ?? 'none'));
        return array_slice($scored, 0, $topK);
    }

    public function delete(string $documentId): bool {
        global $wpdb;
        $wpdb->delete($this->table, ['document_id' => $documentId], ['%s']);
        return true;
    }

    private function cosineSimilarity(array $a, array $b): float {
        $dot   = 0.0;
        $normA = 0.0;
        $normB = 0.0;
        $count = min(count($a), count($b));

        for ($i = 0; $i < $count; $i++) {
            $dot   += $a[$i] * $b[$i];
            $normA += $a[$i] * $a[$i];
            $normB += $b[$i] * $b[$i];
        }

        if ($normA == 0.0 || $normB == 0.0) {
            return 0.0;
        }

        return $dot / (sqrt($normA) * sqrt($normB));
    }
}
