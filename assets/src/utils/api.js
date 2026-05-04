import apiFetch from '@wordpress/api-fetch';

apiFetch.use(apiFetch.createNonceMiddleware(window.pdfChatRag.nonce));

const BASE = window.pdfChatRag.restUrl;

export const chatApi = {
    sendMessage: (message, sessionId = null) =>
        apiFetch({
            path: `${BASE}/chat`,
            method: 'POST',
            data: { message, session_id: sessionId },
        }),

    getHistory: (sessionId) =>
        apiFetch({
            path: `${BASE}/chat/history?session_id=${encodeURIComponent(sessionId)}`,
            method: 'GET',
        }),
};

export const pdfApi = {
    upload: (file) => {
        const formData = new FormData();
        formData.append('file', file);
        
        return fetch(`${BASE}/pdf/upload`, {
            method: 'POST',
            headers: {
                'X-WP-Nonce': window.pdfChatRag.nonce,
            },
            body: formData,
        }).then(r => r.json());
    },
};