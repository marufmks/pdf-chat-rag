import { render } from '@wordpress/element';
import { ChatWidget } from './components/ChatWidget';
import './style.css';

const init = () => {
	const el = document.createElement( 'div' );
	el.id = 'pdf-chat-rag-widget-root';
	document.body.appendChild( el );
	render( <ChatWidget />, el );
};

if ( document.readyState === 'loading' ) {
	document.addEventListener( 'DOMContentLoaded', init );
} else {
	init();
}
