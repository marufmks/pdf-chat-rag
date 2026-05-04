import { useState } from '@wordpress/element';
import { Button, Panel, PanelBody, TextControl, Notice } from '@wordpress/components';
import { pdfApi } from '../../utils/api';

export const AdminApp = () => {
    const [settings, setSettings] = useState({ openaiKey: '', serviceUrl: '' });
    const [notice, setNotice] = useState(null);

    const handlePdfUpload = async (e) => {
        const file = e.target.files[0];
        if (!file) return;
        
        try {
            const res = await pdfApi.upload(file);
            setNotice({ status: 'success', message: 'PDF processed: ' + res.document_id });
        } catch (err) {
            setNotice({ status: 'error', message: err.message });
        }
    };

    return (
        <div className="pdf-chat-rag-admin">
            <h1>PDF Chat RAG</h1>
            {notice && <Notice status={notice.status}>{notice.message}</Notice>}
            
            <Panel>
                <PanelBody title="Upload Knowledge Base PDF" initialOpen={true}>
                    <input type="file" accept=".pdf" onChange={handlePdfUpload} />
                </PanelBody>
                
                <PanelBody title="API Configuration">
                    <TextControl
                        label="OpenAI API Key"
                        value={settings.openaiKey}
                        onChange={(v) => setSettings({ ...settings, openaiKey: v })}
                    />
                    <TextControl
                        label="Python Microservice URL"
                        value={settings.serviceUrl}
                        onChange={(v) => setSettings({ ...settings, serviceUrl: v })}
                    />
                    <Button variant="primary">Save Settings</Button>
                </PanelBody>
            </Panel>
        </div>
    );
};