<?php
declare(strict_types=1);

namespace PDFChatRAG\Admin;

class AdminMenu {
    public function __construct() {
        add_action('admin_menu', [$this, 'register']);
    }

    public function register(): void {
        add_menu_page(
            __('PDF Chat RAG', 'pdf-chat-rag'),
            __('PDF Chat RAG', 'pdf-chat-rag'),
            'manage_options',
            'pdf-chat-rag',
            [$this, 'renderPage'],
            'dashicons-format-chat',
            6
        );
    }

    public function renderPage(): void {
        echo '<div id="pdf-chat-rag-admin" class="wrap"></div>';
    }
}