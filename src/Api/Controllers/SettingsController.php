<?php
declare(strict_types=1);

namespace PDFChatRAG\Api\Controllers;

use WP_REST_Request;
use WP_REST_Response;

class SettingsController {
    private const PREFIX = 'pdf_chat_rag_';

    public function get(): WP_REST_Response {
        return new WP_REST_Response([
            'gemini_api_key' => $this->mask(get_option(self::PREFIX . 'gemini_api_key', '')),
        ]);
    }

    public function save(WP_REST_Request $request): WP_REST_Response {
        $params = $request->get_json_params();

        if (!is_array($params)) {
            return new WP_REST_Response(['error' => 'Invalid request body'], 400);
        }

        if (isset($params['gemini_api_key'])) {
            update_option(self::PREFIX . 'gemini_api_key', sanitize_text_field($params['gemini_api_key']));
        }

        return new WP_REST_Response(['success' => true]);
    }

    private function mask(string $key): string {
        if (strlen($key) < 12) {
            return '';
        }
        return substr($key, 0, 6) . '...' . substr($key, -4);
    }
}
