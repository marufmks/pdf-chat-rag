<?php
declare(strict_types=1);

namespace PDFChatRAG\Database\Repository;

class PdfRepository {
    private \wpdb $db;
    private string $table;

    public function __construct() {
        global $wpdb;
        $this->db = $wpdb;
        $this->table = $wpdb->prefix . 'pdf_index';
    }

    public function save(string $documentId, string $filename, int $totalChunks): int {
        $existing = $this->findByDocumentId($documentId);

        if ($existing) {
            $this->db->update(
                $this->table,
                [
                    'filename'     => $filename,
                    'total_chunks' => $totalChunks,
                    'updated_at'   => current_time('mysql'),
                ],
                ['document_id' => $documentId],
                ['%s', '%d', '%s'],
                ['%s']
            );

            return (int) $existing['id'];
        }

        $this->db->insert(
            $this->table,
            [
                'document_id'  => $documentId,
                'filename'     => $filename,
                'total_chunks' => $totalChunks,
                'status'       => 'indexed',
                'created_at'   => current_time('mysql'),
            ],
            ['%s', '%s', '%d', '%s', '%s']
        );

        return (int) $this->db->insert_id;
    }

    public function findByDocumentId(string $documentId): ?array {
        $result = $this->db->get_row(
            $this->db->prepare(
                "SELECT * FROM {$this->table} WHERE document_id = %s LIMIT 1",
                $documentId
            ),
            ARRAY_A
        );

        return $result ?: null;
    }

    public function findAll(int $limit = 50): array {
        return (array) $this->db->get_results(
            $this->db->prepare(
                "SELECT * FROM {$this->table} ORDER BY created_at DESC LIMIT %d",
                $limit
            ),
            ARRAY_A
        );
    }

    public function delete(string $documentId): bool {
        return (bool) $this->db->delete(
            $this->table,
            ['document_id' => $documentId],
            ['%s']
        );
    }
}
