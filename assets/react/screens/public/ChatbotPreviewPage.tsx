import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { useState } from 'react';
import { appIcons } from '../../icons/fontAwesome';
import { Badge, Button, Card, Input } from '../../components/ui';

type ChatMessage = { author: 'customer' | 'bot'; message: string };

export function ChatbotPreviewPage({ title, description }: { title: string; description: string }) {
  const [messages, setMessages] = useState<ChatMessage[]>([
    { author: 'bot', message: 'Bonjour, comment puis-je vous aider ?' },
    { author: 'customer', message: 'Je cherche une robe de soirée.' },
    { author: 'bot', message: 'Voici les meilleures suggestions de la boutique Luxe Paris.' },
  ]);
  const [draft, setDraft] = useState('');

  function sendMessage() {
    const value = draft.trim();
    if (!value) return;
    setMessages((current) => [...current, { author: 'customer', message: value }]);
    setDraft('');
  }

  return (
    <main className="ds-shell">
      <section className="ds-page py-8 md:py-12">
        <div className="grid gap-6 lg:grid-cols-[1fr_420px]">
          <Card className="ds-hero">
            <p className="ds-hero__eyebrow">Assistant IA</p>
            <h1 className="ds-hero__title">{title}</h1>
            <p className="ds-hero__subtitle">{description}</p>
            <div className="mt-6 flex flex-wrap gap-3">
              <Badge tone="success">Temps réel</Badge>
              <Badge tone="neutral">Produits</Badge>
              <Badge tone="neutral">Livraison</Badge>
            </div>
          </Card>

          <Card className="space-y-4">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-xs font-bold uppercase tracking-[0.15em] text-[color:var(--ds-on-surface-variant)]">Conversation</p>
                <h2 className="mt-1 text-xl font-bold">Chatbot interactif</h2>
              </div>
              <Badge tone="success">En ligne</Badge>
            </div>

            <div className="space-y-3 rounded-2xl bg-[color:var(--ds-surface-container-low)] p-4">
              {messages.map((message, index) => (
                <div key={`${message.author}-${index}`} className={`max-w-[85%] rounded-2xl px-4 py-3 text-sm ${message.author === 'customer' ? 'ml-auto bg-[color:var(--ds-primary)] text-white' : 'bg-white text-[color:var(--ds-on-surface)]'}`}>
                  {message.message}
                </div>
              ))}
            </div>

            <div className="space-y-3">
              <Input value={draft} onChange={(event) => setDraft(event.target.value)} placeholder="Écrire une question client..." />
              <div className="flex gap-3">
                <Button variant="primary" className="flex-1" onClick={sendMessage}>Envoyer</Button>
                <Button variant="ghost"><FontAwesomeIcon icon={appIcons.users} /></Button>
              </div>
            </div>
          </Card>
        </div>
      </section>
    </main>
  );
}
