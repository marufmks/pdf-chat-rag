<?php
declare(strict_types=1);

namespace PDFChatRAG\Api;

use PDFChatRAG\Api\Controllers\ChatController;
use PDFChatRAG\Api\Controllers\PdfController;
use PDFChatRAG\Api\Controllers\SettingsController;
use PDFChatRAG\Api\Middleware\AuthMiddleware;
use PDFChatRAG\Services\Rag\Pipeline;

class RestApi {
    private const NAMESPACE = 'pdf-chat-rag/v1';
    private Pipeline $pipeline;

    public function __construct(Pipeline $pipeline) {
        $this->pipeline = $pipeline;
        add_action('rest_api_init', [$this, 'registerRoutes']);
    }

    public function registerRoutes(): void {
        register_rest_route(self::NAMESPACE, '/chat', [
            'methods'             => 'POST',
            'callback'            => [new ChatController($this->pipeline), 'sendMessage'],
            'permission_callback' => '__return_true',
            'args'                => [
                'message'    => ['required' => true, 'type' => 'string'],
                'session_id' => ['required' => false, 'type' => 'string'],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/chat/history', [
            'methods'             => 'GET',
            'callback'            => [new ChatController($this->pipeline), 'getHistory'],
            'permission_callback' => '__return_true',
            'args'                => [
                'session_id' => ['required' => true, 'type' => 'string'],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/pdf/upload', [
            'methods'             => 'POST',
            'callback'            => [new PdfController(), 'upload'],
            'permission_callback' => [AuthMiddleware::class, 'checkAdminPermission'],
        ]);

        register_rest_route(self::NAMESPACE, '/settings', [
            'methods'             => 'GET',
            'callback'            => [new SettingsController(), 'get'],
            'permission_callback' => [AuthMiddleware::class, 'checkAdminPermission'],
        ]);

        register_rest_route(self::NAMESPACE, '/settings', [
            'methods'             => 'POST',
            'callback'            => [new SettingsController(), 'save'],
            'permission_callback' => [AuthMiddleware::class, 'checkAdminPermission'],
        ]);
    }
}
