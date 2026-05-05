<?php
declare(strict_types=1);

namespace PDFChatRAG\Services\Rag;

use PDFChatRAG\Services\Contracts\LlmProviderInterface;
use PDFChatRAG\Services\Contracts\VectorStoreInterface;
use PDFChatRAG\Database\Repository\ChatRepository;

class Pipeline {
    private LlmProviderInterface $llm;
    private VectorStoreInterface $vectorStore;
    private ChatRepository $history;

    public function __construct(
        LlmProviderInterface $llm,
        VectorStoreInterface $vectorStore,
        ChatRepository $history
    ) {
        $this->llm         = $llm;
        $this->vectorStore = $vectorStore;
        $this->history     = $history;
    }

    public function query(string $message, string $sessionId): array {
        $embedding = $this->llm->createEmbeddings($message);

        $context = $this->vectorStore->search($embedding, 5);

        $dbHistory = $this->history->getHistory($sessionId, 5);
        $formatted = [];
        foreach (array_reverse($dbHistory) as $h) {
            $formatted[] = ['role' => 'user',      'content' => $h['message']];
            $formatted[] = ['role' => 'assistant', 'content' => $h['response']];
        }

        $response = $this->llm->generateResponse($message, $context, $formatted);

        $this->history->save($sessionId, get_current_user_id(), $message, $response, $context);

        return [
            'response'   => $response,
            'session_id' => $sessionId,
            'context'    => $context,
        ];
    }
}
