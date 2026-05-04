<?php
declare(strict_types=1);

namespace PDFChatRAG\Core;

use PDFChatRAG\Admin\AdminMenu;
use PDFChatRAG\Api\RestApi;
use PDFChatRAG\Frontend\AssetLoader;

class Plugin {
    private static ?self $instance = null;

    private function __construct() {
        $this->init();
    }

    public static function instance(): self {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function init(): void {
        new AdminMenu();
        new RestApi();
        new AssetLoader();

        register_activation_hook(
            PDF_CHAT_RAG_PLUGIN_DIR . 'pdf-chat-rag.php',
            [Activator::class, 'activate']
        );
    }

    private function __clone() {}
    
    public function __wakeup() {
        throw new \Exception("Cannot unserialize singleton");
    }
}