# PDF Chat RAG

A WordPress plugin that enables users to chat with uploaded PDF documents using a Retrieval-Augmented Generation (RAG) pipeline — entirely in PHP. No external microservices, Docker, or Python required.

## Features

- **Chat with PDFs**: Ask questions about your PDF documents through a conversational interface
- **Session-based conversations**: Maintain context across multiple messages in a chat session
- **Chat history**: View and retrieve past conversation history
- **Admin dashboard**: Upload PDFs and configure your Gemini API key from the WordPress admin
- **Floating chat widget**: Beautiful gradient purple bubble button on posts/pages that opens a sleek chat panel
- **Shortcode `[pdf_chat]`**: Embed a polished chat widget inline on any page or post

## Architecture

```
┌─────────────────────────────────────────────────────────┐
│                    WordPress (PHP-Native)                 │
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
│              └───────┬──────────┬──────────┘             │
│                      │          │                         │
│           ┌──────────▼──┐  ┌───▼──────────────┐         │
│           │ GeminiClient│  │  PhpVectorStore  │         │
│           │  (wp_remote │  │  (cosine sim +   │         │
│           │   _post())  │  │   MySQL JSON)    │         │
│           └─────────────┘  └──────────────────┘         │
│                                                         │
│  ┌──────────────┐  ┌──────────────┐                    │
│  │ WpPdfParser  │  │ TextChunker  │                    │
│  │(smalot lib)  │  │  (sentence   │                    │
│  │              │  │   splitting) │                    │
│  └──────────────┘  └──────────────┘                    │
└─────────────────────────────────────────────────────────┘
```

### Request Flow

1. User uploads a PDF via the admin dashboard
2. `WpPdfParser` (smalot/pdfparser) extracts text from the PDF
3. `TextChunker` splits text into ~1000 character chunks with overlap
4. `GeminiClient` calls Google Gemini `gemini-embedding-001` for each chunk
5. `PhpVectorStore` stores chunks + embeddings as JSON in `{prefix}pdf_vectors`
6. User sends a chat message via widget or shortcode
7. `Pipeline` embeds the query, searches vectors via brute-force cosine similarity, and calls Gemini `gemini-2.5-flash` with retrieved context
8. Response is returned and saved to `{prefix}pdf_chat_history`

## Requirements

- PHP 8.0+
- WordPress 6.0+
- Composer
- Node.js & npm (for frontend builds)
- Google Gemini API key (free tier available)

### Honest Limitations

- **Vector search is O(n)** — for <10,000 text chunks (~1,000 pages), search takes ~20–80ms in PHP 8+. Beyond that, swap `PhpVectorStore` for a hosted vector DB implementation.
- **No OCR** — scanned/image-based PDFs will not work. Only text-based PDFs.
- **PDF parsing** via `smalot/pdfparser` is less robust than Python's `PyMuPDF` on complex layouts.

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

### 4. Configure Gemini

Navigate to **PDF Chat RAG** in the WordPress admin and set:
- **Gemini API Key**

Get a free API key at [Google AI Studio](https://aistudio.google.com/apikey). Free tier: 15 requests per minute.

For added security, define the key in `wp-config.php`:
```php
define('PDF_CHAT_RAG_GEMINI_API_KEY', 'AIza-your-key-here');
```
This overrides any database-stored key and is never exposed via the REST API.

### 5. Embed the chat widget

Use the shortcode on any page or post:
```
[pdf_chat]
```

Or with a custom session ID:
```
[pdf_chat session_id="my_custom_session"]
```

## Development

### Directory Structure

```
pdf-chat-rag/
├── pdf-chat-rag.php                 # Main plugin file
├── composer.json                    # PHP dependencies + PSR-4 autoload
├── package.json                     # Frontend build config
├── webpack.config.js                # wp-scripts multi-entry override
├── .gitignore
├── AGENTS.md                        # AI Agent context file
├── README.md                        # This file
├── build/                           # Compiled assets (wp-scripts output)
│   ├── admin.js
│   ├── admin.css
│   ├── frontend.js
│   ├── frontend.css
│   ├── shortcode.js
│   └── shortcode.css
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
│       │   │   ├── ChatWidget.js    # Floating bubble + panel
│       │   │   └── ChatBox.js       # Shared chat UI (used by widget + shortcode)
│       │   └── style.css
│       ├── shortcode/
│       │   ├── index.js             # Shortcode React entry point
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
│   │   │   └── Pipeline.php         # RAG orchestration
│   │   ├── GeminiClient.php         # Gemini chat + embeddings
│   │   ├── PhpVectorStore.php       # Brute-force cosine similarity
│   │   ├── TextChunker.php          # Sentence-aware text splitting
│   │   └── WpPdfParser.php          # smalot/pdfparser wrapper
│   ├── Database/
│   │   ├── Migrations/
│   │   │   ├── ChatHistoryTable.php
│   │   │   ├── PdfIndexTable.php
│   │   │   └── VectorTable.php      # New: pdf_vectors table
│   │   └── Repository/
│   │       ├── ChatRepository.php
│   │       └── PdfRepository.php
│   └── Frontend/
│       └── AssetLoader.php          # Enqueues assets + registers shortcode
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

### Shortcode

```
[pdf_chat]
[pdf_chat session_id="custom_session"]
```

Renders an inline chat widget. Assets are only loaded on pages where the shortcode is used.

### AI/Vector Stack (PHP-Native)

The plugin uses pure PHP for the entire RAG pipeline. No external microservice is required.

- **PDF Parsing**: `smalot/pdfparser` (Composer) extracts text from text-based PDFs.
- **Text Chunking**: `Services\TextChunker` splits text into ~1,000 character chunks with overlap.
- **Embeddings**: `Services\GeminiClient` calls Google Gemini's `gemini-embedding-001` API via `wp_remote_post()`.
- **Vector Storage**: `Services\PhpVectorStore` stores embeddings as JSON in `{prefix}pdf_vectors`
  and performs brute-force cosine similarity in PHP. Suitable for <20,000 chunks.
- **LLM Generation**: `Services\GeminiClient` calls Google Gemini's `gemini-2.5-flash` model.
- **Pipeline**: `Services\Rag\Pipeline` orchestrates: Embed → Retrieve → Generate → Store.

#### Performance

| Metric | Expected Performance |
|:---|:---|
| Embedding creation | ~500ms per batch of 100 chunks |
| Vector search | ~20–80ms for 5,000 chunks; ~100–300ms for 20,000 chunks |
| Memory usage | ~2MB per 1,000 chunks during search |
| PDF parsing | ~1s per 50 pages |

#### Scaling Path

If vector search performance degrades, implement a new `VectorStoreInterface` (e.g., `PineconeStore`)
that calls a hosted vector DB via HTTP. The rest of the plugin remains unchanged.

### Coding Standards

- **PHP**: PSR-4 autoloading, `declare(strict_types=1)`, typed properties, namespaces under `PDFChatRAG\`
- **JavaScript**: WordPress ESLint via `@wordpress/scripts`, `@wordpress/components` for admin UI
- **CSS**: Scoped per component (`.pdf-chat-rag-admin`, `.pdf-chat-rag-widget`, `.pdf-chat-rag-chatbox`, `.pdf-chat-rag-shortcode`)

### UI Design System

The frontend chat interface uses a modern design language:

- **Primary accent**: Indigo gradient (`#6366F1` → `#8B5CF6`)
- **User messages**: Purple gradient background with white text
- **Assistant messages**: White background with subtle shadow
- **Header**: Gradient purple with decorative circular accents
- **Toggle button**: Rounded-square with gradient and glow shadow
- **Animations**: Fade-in, slide-up, typing bounce, and spin effects
- **Typography**: System font stack (-apple-system, BlinkMacSystemFont, Segoe UI, Roboto)

## Database

Custom tables created on activation:

| Table                        | Purpose                          |
|------------------------------|----------------------------------|
| `{prefix}pdf_chat_history`   | Chat message history             |
| `{prefix}pdf_index`          | PDF document index               |
| `{prefix}pdf_vectors`        | Vector chunks + embeddings (JSON)|

## License

GPL-2.0+
