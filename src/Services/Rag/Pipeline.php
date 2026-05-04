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

    public function __construct(ChatRepository $history) {
        $this->history = $history;
        // TODO: Resolve LLM and VectorStore from a container or settings
    }

    public function query(string $message, string $sessionId): array {
        // 1. Embed query
        $embedding = $this->llm->createEmbeddings($message);

        // 2. Retrieve context
        $context = $this->vectorStore->search($embedding, 5);

        // 3. Get recent history
        $recentHistory = $this->history->getHistory($sessionId, 5);

        // 4. Generate
        $response = $this->llm->generateResponse($message, $context, $recentHistory);

        // 5. Persist
        $this->history->save($sessionId, get_current_user_id(), $message, $response, $context);

        return [
            'response'   => $response,
            'session_id' => $sessionId,
            'context'    => $context,
        ];
    }
}