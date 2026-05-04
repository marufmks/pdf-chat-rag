<?php
declare(strict_types=1);

namespace PDFChatRAG\Services\Rag;

use PDFChatRAG\Database\Repository\ChatRepository;

class Pipeline {
    private MicroserviceClient $client;
    private ChatRepository $history;

    public function __construct(MicroserviceClient $client, ChatRepository $history) {
        $this->client = $client;
        $this->history = $history;
    }

    public function query(string $message, string $sessionId): array {
        $recentHistory = $this->history->getHistory($sessionId, 5);

        $payload = [
            'message'     => $message,
            'session_id'  => $sessionId,
            'history'     => $recentHistory,
        ];

        $result = $this->client->query($payload);

        $this->history->save(
            $sessionId,
            get_current_user_id(),
            $message,
            $result['response'] ?? '',
            $result['context'] ?? []
        );

        return [
            'response'   => $result['response'] ?? '',
            'session_id' => $sessionId,
            'context'    => $result['context'] ?? [],
        ];
    }
}
