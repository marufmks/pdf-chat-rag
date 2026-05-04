<?php
declare(strict_types=1);

namespace PDFChatRAG\Api\Controllers;

use WP_REST_Request;
use WP_REST_Response;

class SettingsController {
    private const OPTIONS = [
        'pdf_chat_rag_service_url'  => 'string',
        'pdf_chat_rag_service_key'  => 'string',
        'pdf_chat_rag_openai_key'   => 'string',
    ];

    public function get(WP_REST_Request $request): WP_REST_Response {
        $settings = [];

        foreach (array_keys(self::OPTIONS) as $option) {
            $settings[$option] = get_option($option, '');
        }

        return new WP_REST_Response([
            'success'  => true,
            'settings' => $settings,
        ], 200);
    }

    public function save(WP_REST_Request $request): WP_REST_Response {
        $params = $request->get_json_params();

        if (!is_array($params)) {
            return new WP_REST_Response(
                ['error' => 'Invalid request body'],
                400
            );
        }

        foreach (self::OPTIONS as $option => $type) {
            if (isset($params[$option])) {
                $value = sanitize_text_field($params[$option]);
                update_option($option, $value);
            }
        }

        return new WP_REST_Response([
            'success' => true,
            'message' => 'Settings saved successfully',
        ], 200);
    }
}
