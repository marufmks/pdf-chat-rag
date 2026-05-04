<?php
declare(strict_types=1);

namespace PDFChatRAG\Frontend;

class AssetLoader {
    public function __construct() {
        add_action('admin_enqueue_scripts', [$this, 'adminAssets']);
        add_action('wp_enqueue_scripts', [$this, 'frontendAssets']);
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
            PDF_CHAT_RAG_PLUGIN_URL . 'build/admin.css',
            [],
            $asset['version']
        );

        wp_localize_script('pdf-chat-rag-admin', 'pdfChatRag', [
            'restUrl' => esc_url_raw(rest_url('pdf-chat-rag/v1')),
            'nonce'   => wp_create_nonce('wp_rest'),
        ]);
    }

    public function frontendAssets(): void {
        if (!is_singular()) {
            return;
        }

        $assetFile = PDF_CHAT_RAG_PLUGIN_DIR . 'build/frontend.asset.php';
        if (!file_exists($assetFile)) {
            return;
        }

        $asset = require $assetFile;

        wp_enqueue_script(
            'pdf-chat-rag-frontend',
            PDF_CHAT_RAG_PLUGIN_URL . 'build/frontend.js',
            $asset['dependencies'],
            $asset['version'],
            true
        );

        wp_enqueue_style(
            'pdf-chat-rag-frontend',
            PDF_CHAT_RAG_PLUGIN_URL . 'build/frontend.css',
            [],
            $asset['version']
        );

        wp_localize_script('pdf-chat-rag-frontend', 'pdfChatRag', [
            'restUrl' => esc_url_raw(rest_url('pdf-chat-rag/v1')),
            'nonce'   => wp_create_nonce('wp_rest'),
        ]);
    }
}