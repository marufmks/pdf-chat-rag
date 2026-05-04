<?php
/**
 * Plugin Name: PDF Chat RAG
 * Description: Chat with PDFs using RAG architecture
 * Version: 1.0.0
 * Author: You
 * License: GPL-2.0+
 * Text Domain: pdf-chat-rag
 */

if (!defined('ABSPATH')) {
    exit;
}

define('PDF_CHAT_RAG_VERSION', '1.0.0');
define('PDF_CHAT_RAG_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PDF_CHAT_RAG_PLUGIN_URL', plugin_dir_url(__FILE__));

// Composer autoloader
if (file_exists(PDF_CHAT_RAG_PLUGIN_DIR . 'vendor/autoload.php')) {
    require_once PDF_CHAT_RAG_PLUGIN_DIR . 'vendor/autoload.php';
}

// Initialize singleton
add_action('plugins_loaded', [PDFChatRAG\Core\Plugin::class, 'instance']);