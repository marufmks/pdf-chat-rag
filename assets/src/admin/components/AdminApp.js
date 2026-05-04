import { useState, useEffect } from '@wordpress/element';
import { Button, Panel, PanelBody, TextControl, Notice } from '@wordpress/components';
import { pdfApi } from '../../utils/api';
import '../../admin/style.css';

const BASE = window.pdfChatRag.restUrl;

export const AdminApp = () => {
    const [settings, setSettings] = useState({
        pdf_chat_rag_service_url: '',
        pdf_chat_rag_service_key: '',
        pdf_chat_rag_openai_key: '',
    });
    const [notice, setNotice] = useState(null);
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        fetch(`${BASE}/settings`, {
            headers: { 'X-WP-Nonce': window.pdfChatRag.nonce },
        })
            .then((r) => r.json())
            .then((data) => {
                if (data.settings) {
                    setSettings(data.settings);
                }
            })
            .catch(() => {
                setNotice({ status: 'error', message: 'Failed to load settings' });
            });
    }, []);

    const handleSave = async () => {
        setLoading(true);
        try {
            const res = await fetch(`${BASE}/settings`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': window.pdfChatRag.nonce,
                },
                body: JSON.stringify(settings),
            });
            const data = await res.json();

            if (data.success) {
                setNotice({ status: 'success', message: 'Settings saved successfully' });
            } else {
                setNotice({ status: 'error', message: data.error || 'Failed to save settings' });
            }
        } catch (err) {
            setNotice({ status: 'error', message: 'Failed to save settings' });
        } finally {
            setLoading(false);
        }
    };

    const handlePdfUpload = async (e) => {
        const file = e.target.files[0];
        if (!file) return;

        try {
            const res = await pdfApi.upload(file);
            if (res.error) {
                setNotice({ status: 'error', message: res.error });
            } else {
                setNotice({ status: 'success', message: 'PDF processed: ' + res.document_id });
            }
        } catch (err) {
            setNotice({ status: 'error', message: err.message });
        }

        e.target.value = '';
    };

    return (
        <div className="pdf-chat-rag-admin">
            <h1>PDF Chat RAG</h1>
            {notice && (
                <Notice status={notice.status} onRemove={() => setNotice(null)}>
                    {notice.message}
                </Notice>
            )}

            <Panel>
                <PanelBody title="Upload Knowledge Base PDF" initialOpen={true}>
                    <input type="file" accept=".pdf" onChange={handlePdfUpload} />
                </PanelBody>

                <PanelBody title="Service Configuration" initialOpen={true}>
                    <TextControl
                        label="Python Microservice URL"
                        value={settings.pdf_chat_rag_service_url}
                        onChange={(v) => setSettings({ ...settings, pdf_chat_rag_service_url: v })}
                        placeholder="http://localhost:8000"
                    />
                    <TextControl
                        label="Microservice API Key"
                        type="password"
                        value={settings.pdf_chat_rag_service_key}
                        onChange={(v) => setSettings({ ...settings, pdf_chat_rag_service_key: v })}
                    />
                    <TextControl
                        label="OpenAI API Key"
                        type="password"
                        value={settings.pdf_chat_rag_openai_key}
                        onChange={(v) => setSettings({ ...settings, pdf_chat_rag_openai_key: v })}
                    />
                    <Button
                        variant="primary"
                        onClick={handleSave}
                        isBusy={loading}
                        disabled={loading}
                    >
                        {loading ? 'Saving...' : 'Save Settings'}
                    </Button>
                </PanelBody>
            </Panel>
        </div>
    );
};
