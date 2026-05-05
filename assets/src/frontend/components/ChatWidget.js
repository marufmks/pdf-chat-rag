import { useState } from '@wordpress/element';
import { ChatBox } from './ChatBox';
import '../style.css';

export const ChatWidget = () => {
	const [ isOpen, setIsOpen ] = useState( false );

	if ( ! isOpen ) {
		return (
			<button
				className="pdf-chat-rag-widget__toggle"
				onClick={ () => setIsOpen( true ) }
				aria-label="Open chat"
			>
				<svg
					width="24"
					height="24"
					viewBox="0 0 24 24"
					fill="none"
					xmlns="http://www.w3.org/2000/svg"
				>
					<path
						d="M20 2H4C2.9 2 2 2.9 2 4V22L6 18H20C21.1 18 22 17.1 22 16V4C22 2.9 21.1 2 20 2Z"
						fill="white"
					/>
					<circle cx="8.5" cy="10" r="1.5" fill="#6366F1" />
					<circle cx="12" cy="10" r="1.5" fill="#6366F1" />
					<circle cx="15.5" cy="10" r="1.5" fill="#6366F1" />
				</svg>
			</button>
		);
	}

	return (
		<div className="pdf-chat-rag-widget">
			<div className="pdf-chat-rag-widget__header">
				<div className="pdf-chat-rag-widget__header-brand">
					<div className="pdf-chat-rag-widget__header-icon">
						<svg
							width="20"
							height="20"
							viewBox="0 0 20 20"
							fill="none"
						>
							<path
								d="M13.5 2H5C4.17 2 3.5 2.67 3.5 3.5V16.5C3.5 17.33 4.17 18 5 18H14C14.83 18 15.5 17.33 15.5 16.5V4.5L13.5 2Z"
								fill="white"
								opacity="0.9"
							/>
							<path d="M13 2V5H16" fill="white" opacity="0.6" />
							<rect
								x="5.5"
								y="8.5"
								width="9"
								height="1"
								rx="0.5"
								fill="white"
								opacity="0.4"
							/>
							<rect
								x="5.5"
								y="11"
								width="7"
								height="1"
								rx="0.5"
								fill="white"
								opacity="0.4"
							/>
							<rect
								x="5.5"
								y="13.5"
								width="5"
								height="1"
								rx="0.5"
								fill="white"
								opacity="0.4"
							/>
						</svg>
					</div>
					<div className="pdf-chat-rag-widget__header-text">
						<h3>PDF Assistant</h3>
						<span>Ask anything about your documents</span>
					</div>
				</div>
				<button
					className="pdf-chat-rag-widget__close"
					onClick={ () => setIsOpen( false ) }
					aria-label="Close chat"
				>
					<svg width="16" height="16" viewBox="0 0 16 16" fill="none">
						<path
							d="M4 4L12 12M12 4L4 12"
							stroke="currentColor"
							strokeWidth="2"
							strokeLinecap="round"
						/>
					</svg>
				</button>
			</div>
			<ChatBox />
		</div>
	);
};
