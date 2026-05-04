<?php
declare(strict_types=1);

namespace PDFChatRAG\Api\Controllers;

use WP_REST_Request;
use WP_REST_Response;
use PDFChatRAG\Services\Rag\Pipeline;
use PDFChatRAG\Database\Repository\ChatRepository;

class ChatController {
    private Pipeline $pipeline;
    private ChatRepository $history;

    public function __construct(Pipeline $pipeline) {
        $this->pipeline = $pipeline;
        $this->history = new ChatRepository();
    }

    public function sendMessage(WP_REST_Request $request): WP_REST_Response {
        $message = sanitize_text_field($request->get_param('message'));
        $sessionId = sanitize_text_field($request->get_param('session_id') ?? uniqid('chat_', true));

        if (empty($message)) {
            return new WP_REST_Response(['error' => 'Message required'], 400);
        }

        try {
            $result = $this->pipeline->query($message, $sessionId);

            return new WP_REST_Response([
                'success'    => true,
                'response'   => $result['response'],
                'session_id' => $result['session_id'],
                'context'    => $result['context'] ?? [],
            ], 200);
        } catch (\Exception $e) {
            return new WP_REST_Response(['error' => $e->getMessage()], 500);
        }
    }

    public function getHistory(WP_REST_Request $request): WP_REST_Response {
        $sessionId = sanitize_text_field($request->get_param('session_id'));
        $history = $this->history->getHistory($sessionId, 20);

        return new WP_REST_Response([
            'success' => true,
            'history' => array_reverse($history),
        ]);
    }
}
