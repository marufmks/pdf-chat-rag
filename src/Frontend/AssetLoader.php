<?php
declare(strict_types=1);

namespace PDFChatRAG\Frontend;

class AssetLoader {
    public function __construct() {
        add_action('admin_enqueue_scripts', [$this, 'adminAssets']);
        add_action('wp_enqueue_scripts', [$this, 'frontendAssets']);
        add_shortcode('pdf_chat', [$this, 'renderShortcode']);
    }

    public function adminAssets(string $hook): void {
        if ($hook !== 'toplevel_page_pdf-chat-rag') {
            return;
        }

        $assetFile = PDF_CHAT_RAG_PLUGIN_DIR . 'build/admin.asset.php';
        if (!file_exists($assetFile)) {
            return;
        }

        $asset = require $assetFile;

        wp_enqueue_script(
            'pdf-chat-rag-admin',
            PDF_CHAT_RAG_PLUGIN_URL . 'build/admin.js',
            $asset['dependencies'],
            $asset['version'],
            true
        );

        wp_enqueue_style(
            'pdf-chat-rag-admin',
            PDF_CHAT_RAG_PLUGIN_URL . 'build/style-admin.css',
            [],
            $asset['version']
        );

        wp_localize_script('pdf-chat-rag-admin', 'pdfChatRag', [
            'restUrl' => esc_url_raw(rest_url('pdf-chat-rag/v1')),
            'nonce'   => wp_create_nonce('wp_rest'),
        ]);
    }

    public function frontendAssets(): void {
        global $post;
        if (!$post) {
            return;
        }

        $hasShortcode = has_shortcode($post->post_content, 'pdf_chat');

        $shortcodeAssetFile = PDF_CHAT_RAG_PLUGIN_DIR . 'build/shortcode.asset.php';
        if ($hasShortcode && file_exists($shortcodeAssetFile)) {
            $asset = require $shortcodeAssetFile;

            wp_enqueue_script(
                'pdf-chat-rag-shortcode',
                PDF_CHAT_RAG_PLUGIN_URL . 'build/shortcode.js',
                $asset['dependencies'],
                $asset['version'],
                true
            );

            wp_enqueue_style(
                'pdf-chat-rag-shortcode',
                PDF_CHAT_RAG_PLUGIN_URL . 'build/style-shortcode.css',
                [],
                $asset['version']
            );

            wp_localize_script('pdf-chat-rag-shortcode', 'pdfChatRag', [
                'restUrl' => esc_url_raw(rest_url('pdf-chat-rag/v1')),
                'nonce'   => wp_create_nonce('wp_rest'),
            ]);

            return;
        }

        $frontendAssetFile = PDF_CHAT_RAG_PLUGIN_DIR . 'build/frontend.asset.php';
        if (is_singular() && file_exists($frontendAssetFile)) {
            $asset = require $frontendAssetFile;

            wp_enqueue_script(
                'pdf-chat-rag-frontend',
                PDF_CHAT_RAG_PLUGIN_URL . 'build/frontend.js',
                $asset['dependencies'],
                $asset['version'],
                true
            );

            wp_enqueue_style(
                'pdf-chat-rag-frontend',
                PDF_CHAT_RAG_PLUGIN_URL . 'build/style-frontend.css',
                [],
                $asset['version']
            );

            wp_localize_script('pdf-chat-rag-frontend', 'pdfChatRag', [
                'restUrl' => esc_url_raw(rest_url('pdf-chat-rag/v1')),
                'nonce'   => wp_create_nonce('wp_rest'),
            ]);
        }
    }

    public function renderShortcode(array $atts = []): string {
        $atts = shortcode_atts([
            'session_id' => '',
        ], $atts, 'pdf_chat');

        $sessionIdAttr = $atts['session_id'] ? ' data-session-id="' . esc_attr($atts['session_id']) . '"' : '';

        return '<div class="pdf-chat-rag-shortcode"' . $sessionIdAttr . '></div>';
    }
}
