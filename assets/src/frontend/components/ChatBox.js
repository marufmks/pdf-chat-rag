import { useState, useRef, useEffect, useCallback } from '@wordpress/element';
import { chatApi } from '../../utils/api';

export const ChatBox = ( { sessionId, className = '' } ) => {
	const [ messages, setMessages ] = useState( [] );
	const [ input, setInput ] = useState( '' );
	const [ loading, setLoading ] = useState( false );
	const messagesEndRef = useRef( null );
	const inputRef = useRef( null );
	const [ activeSessionId ] = useState(
		() => sessionId || 'chat_' + Math.random().toString( 36 ).substr( 2, 9 )
	);

	const scrollToBottom = useCallback( () => {
		messagesEndRef.current?.scrollIntoView( { behavior: 'smooth' } );
	}, [] );

	useEffect( () => {
		scrollToBottom();
	}, [ messages, scrollToBottom ] );

	useEffect( () => {
		if ( ! loading && inputRef.current ) {
			inputRef.current.focus();
		}
	}, [ loading ] );

	const handleSubmit = async ( e ) => {
		e.preventDefault();
		if ( ! input.trim() || loading ) return;

		const userMessage = input.trim();
		setInput( '' );
		setMessages( ( prev ) => [
			...prev,
			{ role: 'user', content: userMessage },
		] );
		setLoading( true );

		try {
			const res = await chatApi.sendMessage(
				userMessage,
				activeSessionId
			);
			setMessages( ( prev ) => [
				...prev,
				{ role: 'assistant', content: res.response },
			] );
		} catch ( err ) {
			setMessages( ( prev ) => [
				...prev,
				{
					role: 'assistant',
					content:
						'Error: ' + ( err.message || 'Something went wrong' ),
				},
			] );
		} finally {
			setLoading( false );
		}
	};

	const formatTime = () => {
		return new Date().toLocaleTimeString( [], {
			hour: '2-digit',
			minute: '2-digit',
		} );
	};

	const formatMessage = ( content ) => {
		const lines = content.split( '\n' );
		if ( lines.length === 1 ) return content;

		return lines.map( ( line, i ) => (
			<span key={ i }>
				{ line }
				{ i < lines.length - 1 && <br /> }
			</span>
		) );
	};

	return (
		<div className={ `pdf-chat-rag-chatbox ${ className }` }>
			<div className="pdf-chat-rag-chatbox__messages">
				{ messages.length === 0 && (
					<div className="pdf-chat-rag-chatbox__empty">
						<div className="pdf-chat-rag-chatbox__empty-icon">
							<svg
								width="48"
								height="48"
								viewBox="0 0 48 48"
								fill="none"
							>
								<rect
									x="12"
									y="8"
									width="24"
									height="32"
									rx="3"
									stroke="currentColor"
									strokeWidth="2"
								/>
								<rect
									x="16"
									y="14"
									width="16"
									height="2"
									rx="1"
									fill="currentColor"
									opacity="0.3"
								/>
								<rect
									x="16"
									y="20"
									width="12"
									height="2"
									rx="1"
									fill="currentColor"
									opacity="0.3"
								/>
								<rect
									x="16"
									y="26"
									width="14"
									height="2"
									rx="1"
									fill="currentColor"
									opacity="0.3"
								/>
								<circle
									cx="24"
									cy="36"
									r="3"
									stroke="currentColor"
									strokeWidth="1.5"
								/>
							</svg>
						</div>
						<p className="pdf-chat-rag-chatbox__empty-title">
							Chat with your PDF
						</p>
						<p className="pdf-chat-rag-chatbox__empty-subtitle">
							Ask questions and get instant answers from your
							documents
						</p>
					</div>
				) }

				{ messages.map( ( msg, i ) => (
					<div
						key={ i }
						className={ `pdf-chat-rag-chatbox__message pdf-chat-rag-chatbox__message--${ msg.role }` }
					>
						{ msg.role === 'assistant' && (
							<div className="pdf-chat-rag-chatbox__avatar pdf-chat-rag-chatbox__avatar--assistant">
								<svg
									width="16"
									height="16"
									viewBox="0 0 16 16"
									fill="none"
								>
									<path
										d="M12 3H4C3.45 3 3 3.45 3 4V13C3 13.55 3.45 14 4 14H12C12.55 14 13 13.55 13 13V4C13 3.45 12.55 3 12 3Z"
										fill="white"
									/>
									<rect
										x="5"
										y="7"
										width="6"
										height="1"
										rx="0.5"
										fill="#6366F1"
										opacity="0.7"
									/>
									<rect
										x="5"
										y="9.5"
										width="4"
										height="1"
										rx="0.5"
										fill="#6366F1"
										opacity="0.7"
									/>
								</svg>
							</div>
						) }
						<div className="pdf-chat-rag-chatbox__message-content">
							<div className="pdf-chat-rag-chatbox__message-text">
								{ formatMessage( msg.content ) }
							</div>
							<div className="pdf-chat-rag-chatbox__message-time">
								{ formatTime() }
							</div>
						</div>
					</div>
				) ) }

				{ loading && (
					<div className="pdf-chat-rag-chatbox__message pdf-chat-rag-chatbox__message--assistant">
						<div className="pdf-chat-rag-chatbox__avatar pdf-chat-rag-chatbox__avatar--assistant">
							<svg
								width="16"
								height="16"
								viewBox="0 0 16 16"
								fill="none"
							>
								<path
									d="M12 3H4C3.45 3 3 3.45 3 4V13C3 13.55 3.45 14 4 14H12C12.55 14 13 13.55 13 13V4C13 3.45 12.55 3 12 3Z"
									fill="white"
								/>
								<rect
									x="5"
									y="7"
									width="6"
									height="1"
									rx="0.5"
									fill="#6366F1"
									opacity="0.7"
								/>
								<rect
									x="5"
									y="9.5"
									width="4"
									height="1"
									rx="0.5"
									fill="#6366F1"
									opacity="0.7"
								/>
							</svg>
						</div>
						<div className="pdf-chat-rag-chatbox__message-content">
							<div className="pdf-chat-rag-chatbox__typing">
								<span></span>
								<span></span>
								<span></span>
							</div>
						</div>
					</div>
				) }

				<div ref={ messagesEndRef } />
			</div>

			<form
				className="pdf-chat-rag-chatbox__input"
				onSubmit={ handleSubmit }
			>
				<input
					ref={ inputRef }
					type="text"
					value={ input }
					onChange={ ( e ) => setInput( e.target.value ) }
					placeholder="Ask a question..."
					disabled={ loading }
				/>
				<button
					type="submit"
					className="pdf-chat-rag-chatbox__send"
					disabled={ loading || ! input.trim() }
					aria-label="Send message"
				>
					{ loading ? (
						<svg
							width="18"
							height="18"
							viewBox="0 0 18 18"
							fill="none"
							className="pdf-chat-rag-chatbox__spinner"
						>
							<circle
								cx="9"
								cy="9"
								r="7"
								stroke="currentColor"
								strokeWidth="2"
								opacity="0.3"
							/>
							<path
								d="M9 2C13.4183 2 17 5.58172 17 10"
								stroke="currentColor"
								strokeWidth="2"
								strokeLinecap="round"
							/>
						</svg>
					) : (
						<svg
							width="18"
							height="18"
							viewBox="0 0 18 18"
							fill="none"
						>
							<path
								d="M15 3L7 11M15 3L11 15L7 11M15 3L3 7L7 11"
								stroke="currentColor"
								strokeWidth="1.5"
								strokeLinecap="round"
								strokeLinejoin="round"
							/>
						</svg>
					) }
				</button>
			</form>
		</div>
	);
};
