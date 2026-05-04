import { render } from '@wordpress/element';
import { AdminApp } from './components/AdminApp';

document.addEventListener('DOMContentLoaded', () => {
    const el = document.getElementById('pdf-chat-rag-admin');
    if (el) {
        render(<AdminApp />, el);
    }
});
