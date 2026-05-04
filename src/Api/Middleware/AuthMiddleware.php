<?php
declare(strict_types=1);

namespace PDFChatRAG\Api\Middleware;

use WP_Error;
use WP_REST_Request;

class AuthMiddleware {
    public static function checkAdminPermission(): bool|WP_Error {
        if (!current_user_can('manage_options')) {
            return new WP_Error(
                'rest_forbidden',
                __('You do not have permission to access this resource.', 'pdf-chat-rag'),
                ['status' => 403]
            );
        }

        return true;
    }

    public static function validateApiKey(WP_REST_Request $request): bool|WP_Error {
        $apiKey = get_option('pdf_chat_rag_service_key', '');

        if (empty($apiKey)) {
            return new WP_Error(
                'rest_service_not_configured',
                __('Microservice API key is not configured.', 'pdf-chat-rag'),
                ['status' => 503]
            );
        }

        return true;
    }
}
