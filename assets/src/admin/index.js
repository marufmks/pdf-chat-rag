import { render } from '@wordpress/element';
import { AdminApp } from './components/AdminApp';
import './style.css';

document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('pdf-chat-rag-admin');
    if (root) {
        render(<AdminApp />, root);
    }
});