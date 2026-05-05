import { render } from '@wordpress/element';
import { ChatBox } from '../frontend/components/ChatBox';
import '../shortcode/style.css';

document.addEventListener( 'DOMContentLoaded', () => {
	const containers = document.querySelectorAll( '.pdf-chat-rag-shortcode' );

	containers.forEach( ( container, index ) => {
		const sessionId = container.dataset?.sessionId || null;
		const root = document.createElement( 'div' );
		root.id = `pdf-chat-rag-shortcode-root-${ index }`;
		container.innerHTML = '';
		container.appendChild( root );

		render( <ChatBox sessionId={ sessionId } />, root );
	} );
} );
