import apiFetch from '@wordpress/api-fetch';

apiFetch.use( apiFetch.createNonceMiddleware( window.pdfChatRag.nonce ) );

const NAMESPACE = '/pdf-chat-rag/v1';

export const chatApi = {
	sendMessage: ( message, sessionId = null ) =>
		apiFetch( {
			path: `${ NAMESPACE }/chat`,
			method: 'POST',
			data: { message, session_id: sessionId },
		} ),

	getHistory: ( sessionId ) =>
		apiFetch( {
			path: `${ NAMESPACE }/chat/history`,
			method: 'GET',
			params: { session_id: sessionId },
		} ),
};

export const pdfApi = {
	upload: async ( file ) => {
		const formData = new FormData();
		formData.append( 'file', file );

		const baseUrl = window.pdfChatRag.restUrl.replace( /\/$/, '' );

		return fetch( `${ baseUrl }/pdf/upload`, {
			method: 'POST',
			headers: {
				'X-WP-Nonce': window.pdfChatRag.nonce,
			},
			body: formData,
		} ).then( ( r ) => r.json() );
	},
};
