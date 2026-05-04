<?php
declare(strict_types=1);

namespace PDFChatRAG\Core;

use PDFChatRAG\Admin\AdminMenu;
use PDFChatRAG\Api\RestApi;
use PDFChatRAG\Frontend\AssetLoader;
use PDFChatRAG\Services\Rag\Pipeline;
use PDFChatRAG\Services\Rag\MicroserviceClient;
use PDFChatRAG\Database\Repository\ChatRepository;

class Plugin {
    private static ?self $instance = null;

    private MicroserviceClient $client;
    private Pipeline $pipeline;

    private function __construct() {
        $this->client = new MicroserviceClient();
        $this->pipeline = new Pipeline($this->client, new ChatRepository());
        $this->init();
    }

    public static function instance(): self {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getPipeline(): Pipeline {
        return $this->pipeline;
    }

    private function init(): void {
        new AdminMenu();
        new RestApi($this->pipeline);
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
