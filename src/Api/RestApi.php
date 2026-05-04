<?php
declare(strict_types=1);

namespace PDFChatRAG\Api;

use PDFChatRAG\Api\Controllers\ChatController;
use PDFChatRAG\Api\Controllers\PdfController;
use PDFChatRAG\Api\Controllers\SettingsController;

class RestApi {
    private const NAMESPACE = 'pdf-chat-rag/v1';

    public function __construct() {
        add_action('rest_api_init', [$this, 'registerRoutes']);
    }

    public function registerRoutes(): void {
        // Chat
        register_rest_route(self::NAMESPACE, '/chat', [
            'methods'             => 'POST',
            'callback'            => [new ChatController(), 'sendMessage'],
            'permission_callback' => '__return_true',
            'args'                => [
                'message'    => ['required' => true, 'type' => 'string'],
                'session_id' => ['required' => false, 'type' => 'string'],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/chat/history', [
            'methods'             => 'GET',
            'callback'            => [new ChatController(), 'getHistory'],
            'permission_callback' => '__return_true',
            'args'                => [
                'session_id' => ['required' => true, 'type' => 'string'],
            ],
        ]);

        // PDF Upload
        register_rest_route(self::NAMESPACE, '/pdf/upload', [
            'methods'             => 'POST',
            'callback'            => [new PdfController(), 'upload'],
            'permission_callback' => function () {
                return current_user_can('manage_options');
            },
        ]);

        // Settings
        register_rest_route(self::NAMESPACE, '/settings', [
            'methods'             => 'GET',
            'callback'            => [new SettingsController(), 'get'],
            'permission_callback' => function () {
                return current_user_can('manage_options');
            },
        ]);

        register_rest_route(self::NAMESPACE, '/settings', [
            'methods'             => 'POST',
            'callback'            => [new SettingsController(), 'save'],
            'permission_callback' => function () {
                return current_user_can('manage_options');
            },
        ]);
    }
}