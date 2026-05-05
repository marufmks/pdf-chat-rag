import { useState, useEffect } from '@wordpress/element';
import {
	Button,
	Panel,
	PanelBody,
	TextControl,
	Notice,
	Spinner,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { pdfApi } from '../../utils/api';
import '../../admin/style.css';

const BASE = window.pdfChatRag.restUrl;

export const AdminApp = () => {
	const [ settings, setSettings ] = useState( {
		gemini_api_key: '',
	} );
	const [ notice, setNotice ] = useState( null );
	const [ loading, setLoading ] = useState( false );
	const [ uploading, setUploading ] = useState( false );

	useEffect( () => {
		fetch( `${ BASE }/settings`, {
			headers: { 'X-WP-Nonce': window.pdfChatRag.nonce },
		} )
			.then( ( r ) => r.json() )
			.then( ( data ) => {
				setSettings( {
					gemini_api_key: '',
					...data,
				} );
			} )
			.catch( () => {
				setNotice( {
					status: 'error',
					message: __( 'Failed to load settings', 'pdf-chat-rag' ),
				} );
			} );
	}, [] );

	const handleSave = async () => {
		setLoading( true );
		try {
			const body = {};
			if ( settings.gemini_api_key ) {
				body.gemini_api_key = settings.gemini_api_key;
			}

			const res = await fetch( `${ BASE }/settings`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': window.pdfChatRag.nonce,
				},
				body: JSON.stringify( body ),
			} );
			const data = await res.json();

			if ( data.success ) {
				setNotice( {
					status: 'success',
					message: __(
						'Settings saved successfully',
						'pdf-chat-rag'
					),
				} );
				setSettings( ( prev ) => ( { ...prev, gemini_api_key: '' } ) );
			} else {
				setNotice( {
					status: 'error',
					message:
						data.error ||
						__( 'Failed to save settings', 'pdf-chat-rag' ),
				} );
			}
		} catch ( err ) {
			setNotice( {
				status: 'error',
				message: __( 'Failed to save settings', 'pdf-chat-rag' ),
			} );
		} finally {
			setLoading( false );
		}
	};

	const handlePdfUpload = async ( e ) => {
		const file = e.target.files[ 0 ];
		if ( ! file ) return;

		setUploading( true );
		try {
			const res = await pdfApi.upload( file );
			if ( res.error ) {
				setNotice( { status: 'error', message: res.error } );
			} else {
				setNotice( {
					status: 'success',
					message:
						__( 'PDF processed\u2026', 'pdf-chat-rag' ) +
						' ' +
						res.filename +
						' (' +
						res.chunks +
						' ' +
						__( 'chunks', 'pdf-chat-rag' ) +
						')',
				} );
			}
		} catch ( err ) {
			setNotice( {
				status: 'error',
				message: err.message,
			} );
		} finally {
			setUploading( false );
			e.target.value = '';
		}
	};

	return (
		<div className="pdf-chat-rag-admin">
			<h1>{ __( 'PDF Chat RAG', 'pdf-chat-rag' ) }</h1>
			<p className="description">
				{ __(
					'Upload PDF documents and configure your Gemini API key to enable document-based chat.',
					'pdf-chat-rag'
				) }
			</p>

			{ notice && (
				<Notice
					status={ notice.status }
					onRemove={ () => setNotice( null ) }
					isDismissible
				>
					{ notice.message }
				</Notice>
			) }

			<Panel>
				<PanelBody
					title={ __( 'API Configuration', 'pdf-chat-rag' ) }
					initialOpen={ true }
				>
					<div className="pdf-chat-rag-admin__field">
						<TextControl
							label={ __( 'Gemini API Key', 'pdf-chat-rag' ) }
							type="password"
							value={ settings.gemini_api_key }
							onChange={ ( v ) =>
								setSettings( {
									...settings,
									gemini_api_key: v,
								} )
							}
							placeholder="AIza..."
							help={ __(
								'Leave blank to keep the current key. You can also define PDF_CHAT_RAG_GEMINI_API_KEY in wp-config.php for added security.',
								'pdf-chat-rag'
							) }
						/>
					</div>

					<div className="pdf-chat-rag-admin__field">
						<p className="description">
							{ __(
								'Using Google Gemini 2.5 Flash for chat and Gemini Embedding 001 for embeddings. Free tier: 1,500 requests per day.',
								'pdf-chat-rag'
							) }
						</p>
					</div>

					<Button
						variant="primary"
						onClick={ handleSave }
						isBusy={ loading }
						disabled={ loading }
					>
						{ loading
							? __( 'Saving\u2026', 'pdf-chat-rag' )
							: __( 'Save Settings', 'pdf-chat-rag' ) }
					</Button>
				</PanelBody>

				<PanelBody
					title={ __( 'Knowledge Base', 'pdf-chat-rag' ) }
					initialOpen={ true }
				>
					<p className="description">
						{ __(
							'Upload PDF documents to build the knowledge base. Only text-based PDFs are supported.',
							'pdf-chat-rag'
						) }
					</p>

					<div className="pdf-chat-rag-admin__upload">
						{ uploading && <Spinner /> }
						<input
							type="file"
							accept=".pdf"
							onChange={ handlePdfUpload }
							disabled={ uploading }
						/>
					</div>
				</PanelBody>

				<PanelBody
					title={ __( 'Usage', 'pdf-chat-rag' ) }
					initialOpen={ false }
				>
					<p>
						{ __(
							'Embed the chat widget using shortcode:',
							'pdf-chat-rag'
						) }
					</p>
					<code>[pdf_chat]</code>
					<p>
						{ __(
							'Add this shortcode to any page or post to display the chat widget inline.',
							'pdf-chat-rag'
						) }
					</p>
				</PanelBody>
			</Panel>
		</div>
	);
};
