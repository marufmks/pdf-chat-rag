import { useState, useRef } from '@wordpress/element';
import { Button, Spinner } from '@wordpress/components';
import { chatApi } from '../../utils/api';

export const ChatWidget = () => {
    const [messages, setMessages] = useState([]);
    const [input, setInput] = useState('');
    const [loading, setLoading] = useState(false);
    const [sessionId] = useState(() => 'chat_' + Math.random().toString(36).substr(2, 9));
    const messagesEndRef = useRef(null);

    const scrollToBottom = () => {
        messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        if (!input.trim() || loading) return;

        const userMessage = input.trim();
        setInput('');
        setMessages((prev) => [...prev, { role: 'user', content: userMessage }]);
        setLoading(true);

        try {
            const res = await chatApi.sendMessage(userMessage, sessionId);
            setMessages((prev) => [
                ...prev,
                { role: 'assistant', content: res.response },
            ]);
        } catch (err) {
            setMessages((prev) => [
                ...prev,
                { role: 'assistant', content: 'Error: ' + (err.message || 'Something went wrong') },
            ]);
        } finally {
            setLoading(false);
        }
    };

    if (typeof scrollToBottom !== 'undefined') {
        // Trigger scroll after render
    }

    return (
        <div className="pdf-chat-rag-widget">
            <div className="pdf-chat-rag-widget__header">
                <h3>Chat with PDF</h3>
            </div>

            <div className="pdf-chat-rag-widget__messages">
                {messages.map((msg, i) => (
                    <div key={i} className={`pdf-chat-rag-widget__message pdf-chat-rag-widget__message--${msg.role}`}>
                        <div className="pdf-chat-rag-widget__message-content">
                            {msg.content}
                        </div>
                    </div>
                ))}
                {loading && (
                    <div className="pdf-chat-rag-widget__message pdf-chat-rag-widget__message--assistant">
                        <Spinner />
                    </div>
                )}
                <div ref={messagesEndRef} />
            </div>

            <form className="pdf-chat-rag-widget__input" onSubmit={handleSubmit}>
                <input
                    type="text"
                    value={input}
                    onChange={(e) => setInput(e.target.value)}
                    placeholder="Ask a question..."
                    disabled={loading}
                />
                <Button type="submit" variant="primary" disabled={loading || !input.trim()}>
                    Send
                </Button>
            </form>
        </div>
    );
};
