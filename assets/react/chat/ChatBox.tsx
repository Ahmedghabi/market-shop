import { useCallback, useEffect, useRef, useState } from 'react';

type Message = {
  id: string;
  conversationId: string;
  senderType: string;
  content: string;
  fileUrl?: string | null;
  fileType?: string | null;
  createdAt: string;
};

type ChatBoxProps = {
  boutiqueId: string;
  apiBaseUrl: string;
  token?: string | null;
};

export function ChatBox({ boutiqueId, apiBaseUrl, token }: ChatBoxProps) {
  const [open, setOpen] = useState(false);
  const [messages, setMessages] = useState<Message[]>([]);
  const [conversationId, setConversationId] = useState<string | null>(null);
  const [content, setContent] = useState('');
  const [guestName, setGuestName] = useState('');
  const [guestEmail, setGuestEmail] = useState('');
  const [guestPhone, setGuestPhone] = useState('');
  const [showForm, setShowForm] = useState(true);
  const [loading, setLoading] = useState(false);
  const [uploading, setUploading] = useState(false);
  const eventSourceRef = useRef<EventSource | null>(null);
  const messagesEndRef = useRef<HTMLDivElement>(null);
  const fileInputRef = useRef<HTMLInputElement>(null);

  const scrollToBottom = useCallback(() => {
    messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
  }, []);

  useEffect(() => {
    scrollToBottom();
  }, [messages, scrollToBottom]);

  useEffect(() => {
    if (!conversationId) return;

    const mercureUrl = import.meta.env?.VITE_MERCURE_PUBLIC_URL || process.env.MERCURE_PUBLIC_URL || 'http://localhost:3000/.well-known/mercure';
    const url = new URL(mercureUrl);
    url.searchParams.append('topic', `chat/conversation/${conversationId}`);

    const es = new EventSource(url);
    eventSourceRef.current = es;

    es.onmessage = (event) => {
      try {
        const data = JSON.parse(event.data);
        if (data.type === 'typing' || data.type === 'read') return;
        setMessages((prev) => {
          if (prev.some((m) => m.id === data.id)) return prev;
          return [...prev, data];
        });
      } catch { /* ignore parse errors */ }
    };

    return () => {
      es.close();
      eventSourceRef.current = null;
    };
  }, [conversationId]);

  useEffect(() => {
    if (!conversationId) return;
    fetchMessages();
  }, [conversationId]);

  async function fetchMessages() {
    if (!conversationId) return;
    try {
      const headers: Record<string, string> = {};
      if (token) headers['Authorization'] = `Bearer ${token}`;

      const res = await fetch(
        `${apiBaseUrl}/boutiques/${boutiqueId}/conversations/${conversationId}/messages`,
        { headers },
      );
      if (res.ok) {
        const data = await res.json();
        setMessages(Array.isArray(data) ? data : data['hydra:member'] ?? []);
      }
    } catch { /* ignore */ }
  }

  async function startConversation() {
    if (!guestName.trim()) return;
    setLoading(true);

    try {
      const headers: Record<string, string> = { 'Content-Type': 'application/json' };
      if (token) headers['Authorization'] = `Bearer ${token}`;

      const res = await fetch(
        `${apiBaseUrl}/boutiques/${boutiqueId}/conversations`,
        {
          method: 'POST',
          headers,
          body: JSON.stringify({
            guestName: guestName.trim(),
            guestEmail: guestEmail.trim() || undefined,
            guestPhone: guestPhone.trim() || undefined,
          }),
        },
      );

      if (res.ok) {
        const data = await res.json();
        setConversationId(data.id);
        setShowForm(false);
      }
    } catch { /* ignore */ } finally {
      setLoading(false);
    }
  }

  async function sendMessage(e: React.FormEvent) {
    e.preventDefault();
    if (!content.trim() || !conversationId) return;

    const headers: Record<string, string> = { 'Content-Type': 'application/json' };
    if (token) headers['Authorization'] = `Bearer ${token}`;

    try {
      await fetch(
        `${apiBaseUrl}/boutiques/${boutiqueId}/conversations/${conversationId}/messages`,
        {
          method: 'POST',
          headers,
          body: JSON.stringify({ content: content.trim(), senderType: 'user' }),
        },
      );
      setContent('');
    } catch { /* ignore */ }
  }

  async function uploadFile(e: React.ChangeEvent<HTMLInputElement>) {
    const file = e.target.files?.[0];
    if (!file || !conversationId) return;

    setUploading(true);
    try {
      const formData = new FormData();
      formData.append('file', file);

      const uploadHeaders: Record<string, string> = {};
      if (token) uploadHeaders['Authorization'] = `Bearer ${token}`;

      const uploadRes = await fetch(`${apiBaseUrl}/chat/upload`, {
        method: 'POST',
        headers: uploadHeaders,
        body: formData,
      });

      if (!uploadRes.ok) return;
      const { url, type } = await uploadRes.json();

      const headers: Record<string, string> = { 'Content-Type': 'application/json' };
      if (token) headers['Authorization'] = `Bearer ${token}`;

      await fetch(
        `${apiBaseUrl}/boutiques/${boutiqueId}/conversations/${conversationId}/messages`,
        {
          method: 'POST',
          headers,
          body: JSON.stringify({
            content: '',
            senderType: 'user',
            fileUrl: url,
            fileType: type,
          }),
        },
      );
    } catch { /* ignore */ } finally {
      setUploading(false);
      if (fileInputRef.current) fileInputRef.current.value = '';
    }
  }

  return (
    <div style={{
      position: 'fixed',
      bottom: '16px',
      right: '16px',
      zIndex: 1000,
      fontFamily: 'system-ui, sans-serif',
    }}>
      {!open && (
        <button
          onClick={() => setOpen(true)}
          style={{
            width: '56px',
            height: '56px',
            borderRadius: '50%',
            border: 'none',
            background: '#3525cd',
            color: '#fff',
            fontSize: '24px',
            cursor: 'pointer',
            boxShadow: '0 4px 12px rgba(0,0,0,0.25)',
          }}
          aria-label="Ouvrir le chat"
        >
          💬
        </button>
      )}

      {open && (
        <div style={{
          width: '360px',
          maxHeight: '520px',
          background: '#fff',
          borderRadius: '12px',
          boxShadow: '0 8px 32px rgba(0,0,0,0.15)',
          display: 'flex',
          flexDirection: 'column',
          overflow: 'hidden',
        }}>
          <div style={{
            display: 'flex',
            justifyContent: 'space-between',
            alignItems: 'center',
            padding: '12px 16px',
            background: '#3525cd',
            color: '#fff',
          }}>
            <strong>Support</strong>
            <button
              onClick={() => setOpen(false)}
              style={{ background: 'none', border: 'none', color: '#fff', cursor: 'pointer', fontSize: '18px' }}
              aria-label="Fermer"
            >
              ✕
            </button>
          </div>

          {showForm && !conversationId ? (
            <div style={{ padding: '16px', display: 'flex', flexDirection: 'column', gap: '8px' }}>
              <p style={{ margin: '0 0 8px', fontSize: '14px', color: '#555' }}>Laissez-nous un message :</p>
              <input
                placeholder="Votre nom *"
                value={guestName}
                onChange={(e) => setGuestName(e.target.value)}
                style={inputStyle}
                required
              />
              <input
                placeholder="Votre email"
                type="email"
                value={guestEmail}
                onChange={(e) => setGuestEmail(e.target.value)}
                style={inputStyle}
              />
              <input
                placeholder="Votre téléphone"
                value={guestPhone}
                onChange={(e) => setGuestPhone(e.target.value)}
                style={inputStyle}
              />
              <button
                onClick={startConversation}
                disabled={loading || !guestName.trim()}
                style={sendBtnStyle}
              >
                {loading ? 'Envoi...' : 'Démarrer la conversation'}
              </button>
            </div>
          ) : (
            <>
              <div style={{
                flex: 1,
                overflowY: 'auto',
                padding: '12px',
                display: 'flex',
                flexDirection: 'column',
                gap: '8px',
                minHeight: '300px',
                maxHeight: '380px',
              }}>
                {messages.length === 0 && (
                  <p style={{ textAlign: 'center', color: '#999', fontSize: '14px', marginTop: '40px' }}>
                    Aucun message. Écrivez-nous !
                  </p>
                )}
                {messages.map((msg) => (
                  <div
                    key={msg.id}
                    style={{
                      alignSelf: msg.senderType === 'user' ? 'flex-end' : 'flex-start',
                      background: msg.senderType === 'user' ? '#3525cd' : '#f0f0f0',
                      color: msg.senderType === 'user' ? '#fff' : '#333',
                      padding: '8px 12px',
                      borderRadius: '12px',
                      maxWidth: '80%',
                      fontSize: '14px',
                      wordBreak: 'break-word',
                    }}
                  >
                    {msg.fileUrl && (
                      <div style={{ marginBottom: msg.content ? '4px' : 0 }}>
                        {msg.fileType?.startsWith('image') ? (
                          <img
                            src={msg.fileUrl}
                            alt="Pièce jointe"
                            style={{ maxWidth: '100%', borderRadius: '8px' }}
                          />
                        ) : (
                          <a href={msg.fileUrl} target="_blank" rel="noopener noreferrer" style={{ color: msg.senderType === 'user' ? '#fff' : '#3525cd' }}>
                            📎 Fichier joint
                          </a>
                        )}
                      </div>
                    )}
                    {msg.content}
                    <div style={{
                      fontSize: '10px',
                      opacity: 0.7,
                      marginTop: '4px',
                    }}>
                      {new Date(msg.createdAt).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })}
                    </div>
                  </div>
                ))}
                <div ref={messagesEndRef} />
              </div>

              <form onSubmit={sendMessage} style={{
                display: 'flex',
                gap: '8px',
                padding: '8px 12px',
                borderTop: '1px solid #eee',
              }}>
                <input
                  ref={fileInputRef}
                  type="file"
                  onChange={uploadFile}
                  style={{ display: 'none' }}
                  accept="image/*,.pdf,.doc,.docx,.txt,.zip"
                />
                <button
                  type="button"
                  onClick={() => fileInputRef.current?.click()}
                  disabled={uploading}
                  style={{
                    background: 'none',
                    border: 'none',
                    cursor: 'pointer',
                    fontSize: '20px',
                    padding: '4px',
                  }}
                  aria-label="Joindre un fichier"
                >
                  📎
                </button>
                <input
                  placeholder={uploading ? 'Upload...' : 'Votre message'}
                  value={content}
                  onChange={(e) => setContent(e.target.value)}
                  style={{
                    flex: 1,
                    border: '1px solid #ddd',
                    borderRadius: '20px',
                    padding: '8px 12px',
                    fontSize: '14px',
                    outline: 'none',
                  }}
                  disabled={uploading}
                />
                <button
                  type="submit"
                  disabled={!content.trim() || uploading}
                  style={{
                    background: '#3525cd',
                    color: '#fff',
                    border: 'none',
                    borderRadius: '20px',
                    padding: '8px 16px',
                    cursor: 'pointer',
                    fontSize: '14px',
                  }}
                >
                  Envoyer
                </button>
              </form>
            </>
          )}
        </div>
      )}
    </div>
  );
}

const inputStyle: React.CSSProperties = {
  border: '1px solid #ddd',
  borderRadius: '8px',
  padding: '10px 12px',
  fontSize: '14px',
  outline: 'none',
  width: '100%',
  boxSizing: 'border-box',
};

const sendBtnStyle: React.CSSProperties = {
  background: '#3525cd',
  color: '#fff',
  border: 'none',
  borderRadius: '8px',
  padding: '10px 16px',
  cursor: 'pointer',
  fontSize: '14px',
  fontWeight: 600,
};
