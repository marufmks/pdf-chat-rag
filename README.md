# PDF Chat RAG

A WordPress plugin that enables users to chat with uploaded PDF documents using a Retrieval-Augmented Generation (RAG) pipeline.

The plugin acts as a PHP orchestrator and frontend host, delegating heavy AI/vector operations to a Python FastAPI microservice.

## Features

- **Chat with PDFs**: Ask questions about your PDF documents through a conversational interface
- **Session-based conversations**: Maintain context across multiple messages in a chat session
- **Chat history**: View and retrieve past conversation history
- **Admin dashboard**: Upload PDFs and configure service settings from the WordPress admin
- **Frontend widget**: Floating chat widget on singular posts/pages

## Architecture

```
┌─────────────────────────────────────────────────────────┐
│                    WordPress (PHP)                       │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌─────────┐ │
│  │  Admin   │  │ REST API │  │ Frontend │  │   DB    │ │
│  │  (React) │  │ Routes   │  │  Widget  │  │ (wpdb)  │ │
│  └────┬─────┘  └────┬─────┘  └────┬─────┘  └────┬────┘ │
│       │              │             │              │      │
│       └──────────────┼─────────────┼──────────────┘      │
│                      │             │                     │
│              ┌───────▼─────────────▼───────┐             │
│              │         Pipeline            │             │
│              │  (embed → retrieve → gen)   │             │
│              └──────────────┬──────────────┘             │
│                             │                            │
│              ┌──────────────▼──────────────┐             │
│              │     MicroserviceClient      │             │
│              │   (wp_remote_post / JSON)   │             │
│              └──────────────┬──────────────┘             │
└─────────────────────────────┼───────────────────────────┘
                              │ HTTP + JSON
                              ▼
┌─────────────────────────────────────────────────────────┐
│                Python FastAPI Microservice                │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌─────────┐ │
│  │ PDF      │  │ Embed    │  │ Vector   │  │   LLM   │ │
│  │ Parser   │  │ Query    │  │ Store    │  │ (OpenAI)│ │
│  └──────────┘  └──────────┘  └──────────┘  └─────────┘ │
└─────────────────────────────────────────────────────────┘
```

### Request Flow

1. User sends a message via the frontend chat widget
2. WordPress REST API receives the request (`POST /pdf-chat-rag/v1/chat`)
3. `ChatController` delegates to the `Pipeline`
4. `Pipeline` uses `MicroserviceClient` to send the query to the Python FastAPI service
5. Response is returned to the user and persisted in the database

## Requirements

- PHP 7.4+
- WordPress 6.0+
- Composer
- Node.js & npm (for frontend builds)
- Python FastAPI microservice (developed separately)

## Installation

### 1. Install PHP dependencies

```bash
composer install
```

### 2. Build frontend assets

```bash
npm install
npm run build
```

### 3. Activate the plugin

Via WordPress admin or WP-CLI:

```bash
wp plugin activate pdf-chat-rag
```

### 4. Configure the microservice

Navigate to **PDF Chat RAG** in the WordPress admin and set:
- **Python Microservice URL** (default: `http://localhost:8000`)
- **Microservice API Key**
- **OpenAI API Key** (if your microservice requires it)

## Development

### Directory Structure

```
pdf-chat-rag/
├── pdf-chat-rag.php                 # Main plugin file
├── composer.json                    # PHP dependencies + PSR-4 autoload
├── package.json                     # Frontend build config
├── webpack.config.js                # wp-scripts multi-entry override
├── .gitignore
├── Agents.md                        # AI Agent context file
├── build/                           # Compiled assets (wp-scripts output)
│   ├── admin.js
│   ├── admin.css
│   ├── frontend.js
│   └── frontend.css
├── assets/                          # Frontend source
│   └── src/
│       ├── admin/
│       │   ├── index.js
│       │   ├── components/
│       │   │   └── AdminApp.js
│       │   └── style.css
│       ├── frontend/
│       │   ├── index.js
│       │   ├── components/
│       │   │   └── ChatWidget.js
│       │   └── style.css
│       └── utils/
│           └── api.js
├── src/                             # PHP (PSR-4: PDFChatRAG\)
│   ├── Core/
│   │   ├── Plugin.php               # Singleton entry point
│   │   └── Activator.php            # Activation + migrations
│   ├── Admin/
│   │   └── AdminMenu.php
│   ├── Api/
│   │   ├── RestApi.php
│   │   ├── Controllers/
│   │   │   ├── ChatController.php
│   │   │   ├── PdfController.php
│   │   │   └── SettingsController.php
│   │   └── Middleware/
│   │       └── AuthMiddleware.php
│   ├── Services/
│   │   ├── Contracts/
│   │   │   ├── PdfParserInterface.php
│   │   │   ├── VectorStoreInterface.php
│   │   │   └── LlmProviderInterface.php
│   │   ├── Rag/
│   │   │   ├── Pipeline.php         # RAG orchestration
│   │   │   └── MicroserviceClient.php
│   │   └── WordPress/
│   │       └── WpPdfParser.php
│   ├── Database/
│   │   ├── Migrations/
│   │   │   ├── ChatHistoryTable.php
│   │   │   └── PdfIndexTable.php
│   │   └── Repository/
│   │       ├── ChatRepository.php
│   │       └── PdfRepository.php
│   └── Frontend/
│       └── AssetLoader.php
└── vendor/                          # Composer autoload
```

### Commands

```bash
# PHP dependencies
composer install

# Frontend development
npm run start        # Watch mode with hot reload

# Frontend production build
npm run build

# Linting
npm run lint:js
npm run lint:js:fix
```

### REST API

#### Chat

```
POST /wp-json/pdf-chat-rag/v1/chat
```

**Request:**
```json
{
  "message": "What is the summary?",
  "session_id": "abc-123"
}
```

**Response:**
```json
{
  "success": true,
  "response": "The document discusses...",
  "session_id": "abc-123",
  "context": []
}
```

#### Chat History

```
GET /wp-json/pdf-chat-rag/v1/chat/history?session_id=abc-123
```

Returns chat history for a session (reversed, limit 20).

#### PDF Upload (Admin)

```
POST /wp-json/pdf-chat-rag/v1/pdf/upload
```

Multipart form data. Requires `manage_options` capability.

#### Settings

```
GET  /wp-json/pdf-chat-rag/v1/settings   # Retrieve settings
POST /wp-json/pdf-chat-rag/v1/settings   # Save settings
```

Both require `manage_options` capability.

### Python Microservice Contract

The PHP `MicroserviceClient` expects the Python service to expose:

| Endpoint   | Method | Description                              |
|------------|--------|------------------------------------------|
| `/query`   | POST   | Process a chat query with RAG pipeline   |
| `/ingest`  | POST   | Upload and index a PDF document          |
| `/health`  | GET    | Health check                             |

### Coding Standards

- **PHP**: PSR-4 autoloading, `declare(strict_types=1)`, typed properties, namespaces under `PDFChatRAG\`
- **JavaScript**: WordPress ESLint via `@wordpress/scripts`, `@wordpress/components` for admin UI
- **CSS**: Scoped per component (`.pdf-chat-rag-admin`, `.pdf-chat-rag-widget`)

## Database

Custom tables created on activation:

| Table                        | Purpose                  |
|------------------------------|--------------------------|
| `{prefix}pdf_chat_history`   | Chat message history     |
| `{prefix}pdf_index`          | PDF document index       |

## License

GPL-2.0+
