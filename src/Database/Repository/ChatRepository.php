<?php
declare(strict_types=1);

namespace PDFChatRAG\Database\Repository;

class ChatRepository {
    private string $table;

    public function __construct() {
        global $wpdb;
        $this->table = $wpdb->prefix . 'pdf_chat_history';
    }

    public function save(string $sessionId, ?int $userId, string $message, string $response, ?array $context = null): int {
        global $wpdb;

        $wpdb->insert($this->table, [
            'session_id' => $sessionId,
            'user_id'    => $userId,
            'message'    => $message,
            'response'   => $response,
            'context'    => $context ? wp_json_encode($context) : null,
        ], ['%s', '%d', '%s', '%s', '%s']);

        return (int) $wpdb->insert_id;
    }

    public function getHistory(string $sessionId, int $limit = 10): array {
        global $wpdb;

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->table} WHERE session_id = %s ORDER BY created_at DESC LIMIT %d",
            $sessionId,
            $limit
        ), ARRAY_A);

        return $results ?: [];
    }
}