import { useCallback, useEffect, useRef, useState } from 'react';

type ConversationSummary = {
  id: string;
  boutiqueId: string;
  boutiqueName: string;
  userDisplayName?: string | null;
  guestName?: string | null;
  guestEmail?: string | null;
  lastMessage?: string | null;
  lastMessageAt?: string | null;
  unreadCount: number;
  active: boolean;
  createdAt: string;
};

type Message = {
  id: string;
  conversationId: string;
  senderType: string;
  content: string;
  fileUrl?: string | null;
  fileType?: string | null;
  read: boolean;
  createdAt: string;
};

type AdminChatPanelProps = {
  apiBaseUrl: string;
  token: string;
};

export function AdminChatPanel({ apiBaseUrl, token }: AdminChatPanelProps) {
  const [conversations, setConversations] = useState<ConversationSummary[]>([]);
  const [selectedId, setSelectedId] = useState<string | null>(null);
  const [messages, setMessages] = useState<Message[]>([]);
  const [content, setContent] = useState('');
  const [loading, setLoading] = useState(true);
  const [sending, setSending] = useState(false);
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
    loadConversations();
  }, []);

  useEffect(() => {
    if (!selectedId) return;

    loadMessages(selectedId);
    markAsRead(selectedId);

    const mercureUrl = import.meta.env?.VITE_MERCURE_PUBLIC_URL || process.env.MERCURE_PUBLIC_URL || 'http://localhost:3000/.well-known/mercure';
    const url = new URL(mercureUrl);
    url.searchParams.append('topic', `chat/conversation/${selectedId}`);

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
        setConversations((prev) => prev.map((c) => {
          if (c.id === selectedId) {
            return { ...c, unreadCount: 0 };
          }
          return c;
        }));
      } catch { /* ignore */ }
    };

    return () => {
      es.close();
      eventSourceRef.current = null;
    };
  }, [selectedId]);

  async function loadConversations() {
    setLoading(true);
    try {
      const res = await fetch(`${apiBaseUrl}/admin/conversations`, {
        headers: { Authorization: `Bearer ${token}` },
      });
      if (res.ok) {
        const data = await res.json();
        setConversations(Array.isArray(data) ? data : data['hydra:member'] ?? []);
      }
    } catch { /* ignore */ } finally {
      setLoading(false);
    }
  }

  async function loadMessages(conversationId: string) {
    try {
      const conv = conversations.find((c) => c.id === conversationId);
      if (!conv) return;

      const res = await fetch(
        `${apiBaseUrl}/boutiques/${conv.boutiqueId}/conversations/${conversationId}/messages`,
        { headers: { Authorization: `Bearer ${token}` } },
      );
      if (res.ok) {
        const data = await res.json();
        setMessages(Array.isArray(data) ? data : data['hydra:member'] ?? []);
      }
    } catch { /* ignore */ }
  }

  async function markAsRead(conversationId: string) {
    try {
      const conv = conversations.find((c) => c.id === conversationId);
      if (!conv) return;

      await fetch(
        `${apiBaseUrl}/boutiques/${conv.boutiqueId}/conversations/${conversationId}/messages/read`,
        {
          method: 'POST',
          headers: {
            Authorization: `Bearer ${token}`,
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({ senderType: 'admin' }),
        },
      );
    } catch { /* ignore */ }
  }

  async function sendMessage(e: React.FormEvent) {
    e.preventDefault();
    if (!content.trim() || !selectedId) return;

    setSending(true);
    try {
      const conv = conversations.find((c) => c.id === selectedId);
      if (!conv) return;

      await fetch(
        `${apiBaseUrl}/boutiques/${conv.boutiqueId}/conversations/${selectedId}/messages`,
        {
          method: 'POST',
          headers: {
            Authorization: `Bearer ${token}`,
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({ content: content.trim(), senderType: 'admin' }),
        },
      );
      setContent('');
    } catch { /* ignore */ } finally {
      setSending(false);
    }
  }

  function getConversationLabel(conv: ConversationSummary): string {
    return conv.userDisplayName || conv.guestName || conv.guestEmail || 'Client anonyme';
  }

  function formatTime(dateStr: string): string {
    return new Date(dateStr).toLocaleString('fr-FR', {
      day: '2-digit',
      month: '2-digit',
      hour: '2-digit',
      minute: '2-digit',
    });
  }

  return (
    <div style={{
      display: 'flex',
      height: 'calc(100vh - 120px)',
      fontFamily: 'system-ui, sans-serif',
      borderRadius: '8px',
      overflow: 'hidden',
      border: '1px solid #e0e0e0',
      background: '#fff',
    }}>
      <div style={{
        width: '320px',
        borderRight: '1px solid #e0e0e0',
        overflowY: 'auto',
        flexShrink: 0,
      }}>
        <div style={{
          padding: '16px',
          borderBottom: '1px solid #e0e0e0',
          background: '#f8f9fa',
        }}>
          <h2 style={{ margin: 0, fontSize: '16px' }}>Conversations</h2>
        </div>

        {loading && <p style={{ padding: '16px', color: '#999' }}>Chargement...</p>}

        {conversations.map((conv) => (
          <div
            key={conv.id}
            onClick={() => setSelectedId(conv.id)}
            style={{
              padding: '12px 16px',
              cursor: 'pointer',
              borderBottom: '1px solid #f0f0f0',
              background: selectedId === conv.id ? '#f0f0ff' : '#fff',
              transition: 'background 0.15s',
            }}
          >
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
              <strong style={{ fontSize: '14px' }}>{getConversationLabel(conv)}</strong>
              {conv.unreadCount > 0 && (
                <span style={{
                  background: '#e53935',
                  color: '#fff',
                  borderRadius: '50%',
                  padding: '2px 6px',
                  fontSize: '11px',
                  fontWeight: 700,
                }}>
                  {conv.unreadCount}
                </span>
              )}
            </div>
            <div style={{ fontSize: '12px', color: '#666', marginTop: '4px' }}>
              {conv.boutiqueName}
            </div>
            {conv.lastMessage && (
              <div style={{ fontSize: '12px', color: '#999', marginTop: '4px', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                {conv.lastMessage}
              </div>
            )}
            {conv.lastMessageAt && (
              <div style={{ fontSize: '11px', color: '#bbb', marginTop: '2px' }}>
                {formatTime(conv.lastMessageAt)}
              </div>
            )}
          </div>
        ))}

        {!loading && conversations.length === 0 && (
          <p style={{ padding: '16px', color: '#999', fontSize: '14px', textAlign: 'center' }}>
            Aucune conversation
          </p>
        )}
      </div>

      <div style={{ flex: 1, display: 'flex', flexDirection: 'column' }}>
        {selectedId ? (
          <>
            <div style={{
              padding: '12px 16px',
              borderBottom: '1px solid #e0e0e0',
              background: '#f8f9fa',
              fontSize: '14px',
            }}>
              {(() => {
                const conv = conversations.find((c) => c.id === selectedId);
                return conv ? getConversationLabel(conv) : '';
              })()}
            </div>

            <div style={{
              flex: 1,
              overflowY: 'auto',
              padding: '16px',
              display: 'flex',
              flexDirection: 'column',
              gap: '8px',
            }}>
              {messages.map((msg) => (
                <div
                  key={msg.id}
                  style={{
                    alignSelf: msg.senderType === 'admin' ? 'flex-end' : 'flex-start',
                    background: msg.senderType === 'admin' ? '#3525cd' : '#f0f0f0',
                    color: msg.senderType === 'admin' ? '#fff' : '#333',
                    padding: '8px 12px',
                    borderRadius: '12px',
                    maxWidth: '75%',
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
                        <a
                          href={msg.fileUrl}
                          target="_blank"
                          rel="noopener noreferrer"
                          style={{ color: msg.senderType === 'admin' ? '#fff' : '#3525cd' }}
                        >
                          📎 Fichier
                        </a>
                      )}
                    </div>
                  )}
                  {msg.content}
                  <div style={{
                    fontSize: '10px',
                    opacity: 0.7,
                    marginTop: '4px',
                    display: 'flex',
                    justifyContent: 'space-between',
                  }}>
                    <span>{new Date(msg.createdAt).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })}</span>
                    {msg.senderType === 'admin' && (
                      <span>{msg.read ? '✓✓ Lu' : '✓'}</span>
                    )}
                  </div>
                </div>
              ))}
              <div ref={messagesEndRef} />
            </div>

            <form onSubmit={sendMessage} style={{
              display: 'flex',
              gap: '8px',
              padding: '12px 16px',
              borderTop: '1px solid #e0e0e0',
            }}>
              <input
                ref={fileInputRef}
                type="file"
                style={{ display: 'none' }}
                accept="image/*,.pdf,.doc,.docx,.txt"
              />
              <button
                type="button"
                onClick={() => fileInputRef.current?.click()}
                style={{
                  background: 'none',
                  border: '1px solid #ddd',
                  borderRadius: '8px',
                  cursor: 'pointer',
                  fontSize: '18px',
                  padding: '8px 10px',
                }}
                aria-label="Joindre"
              >
                📎
              </button>
              <input
                placeholder="Écrivez votre réponse..."
                value={content}
                onChange={(e) => setContent(e.target.value)}
                style={{
                  flex: 1,
                  border: '1px solid #ddd',
                  borderRadius: '8px',
                  padding: '8px 12px',
                  fontSize: '14px',
                  outline: 'none',
                }}
              />
              <button
                type="submit"
                disabled={!content.trim() || sending}
                style={{
                  background: '#3525cd',
                  color: '#fff',
                  border: 'none',
                  borderRadius: '8px',
                  padding: '8px 20px',
                  cursor: 'pointer',
                  fontSize: '14px',
                  fontWeight: 600,
                }}
              >
                {sending ? '...' : 'Envoyer'}
              </button>
            </form>
          </>
        ) : (
          <div style={{
            flex: 1,
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            color: '#999',
            fontSize: '14px',
          }}>
            Sélectionnez une conversation
          </div>
        )}
      </div>
    </div>
  );
}
