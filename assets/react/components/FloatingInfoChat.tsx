import { useEffect, useState, type CSSProperties, type FormEvent } from 'react';
import { useAuth } from '../auth/useAuth';

type LocalMessage = {
  author: 'bot' | 'user';
  text: string;
};

type GuestContact = {
  name: string;
  email: string;
  phone: string;
};

const contactCookieName = 'hanooti_assistant_contact';
const messagesCookieName = 'hanooti_assistant_messages';

function readCookie<T>(name: string): T | null {
  const cookie = document.cookie.split('; ').find((item) => item.startsWith(`${name}=`));
  if (!cookie) {
    return null;
  }

  try {
    return JSON.parse(decodeURIComponent(cookie.slice(name.length + 1))) as T;
  } catch {
    return null;
  }
}

function writeCookie(name: string, value: unknown) {
  const maxAge = 60 * 60 * 24 * 30;
  document.cookie = `${name}=${encodeURIComponent(JSON.stringify(value))}; Max-Age=${maxAge}; Path=/; SameSite=Lax`;
}

export function FloatingInfoChat({ title, welcomeMessage, accent = '#0369A1' }: { title: string; welcomeMessage: string; accent?: string }) {
  const { user } = useAuth();
  const [open, setOpen] = useState(false);
  const [draft, setDraft] = useState('');
  const [guestContact, setGuestContact] = useState<GuestContact | null>(() => readCookie<GuestContact>(contactCookieName));
  const [contactDraft, setContactDraft] = useState<GuestContact>(() => readCookie<GuestContact>(contactCookieName) ?? { name: '', email: '', phone: '' });
  const [messages, setMessages] = useState<LocalMessage[]>(() => readCookie<LocalMessage[]>(messagesCookieName) ?? [{ author: 'bot', text: welcomeMessage }]);

  const requiresContact = !user && !guestContact;

  useEffect(() => {
    writeCookie(messagesCookieName, messages.slice(-12));
  }, [messages]);

  function submitContact(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    const contact = {
      name: contactDraft.name.trim(),
      email: contactDraft.email.trim(),
      phone: contactDraft.phone.trim(),
    };

    if (!contact.name || !contact.email || !contact.phone) {
      return;
    }

    writeCookie(contactCookieName, contact);
    setGuestContact(contact);
    setMessages((current) => [
      ...current,
      { author: 'bot', text: `Merci ${contact.name}. Vous pouvez maintenant envoyer votre message.` },
    ]);
  }

  function submit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    if (requiresContact) {
      return;
    }

    const text = draft.trim();
    if (!text) {
      return;
    }

    setMessages((current) => [
      ...current,
      { author: 'user', text },
      { author: 'bot', text: user ? 'Merci pour votre message. Un conseiller Hanooti vous répondra prochainement.' : 'Merci. Vos coordonnées sont enregistrées dans un cookie pour conserver cette conversation et vous recontacter.' },
    ]);
    setDraft('');
  }

  return (
    <div className="floating-info-chat" style={{ '--chat-accent': accent } as CSSProperties}>
      {!open && (
        <button className="floating-info-chat__launcher" type="button" onClick={() => setOpen(true)} aria-label={`Ouvrir ${title}`}>
          <span className="material-symbols-outlined" aria-hidden="true">chat</span>
        </button>
      )}

      {open && (
        <section className="floating-info-chat__panel" aria-label={title}>
          <header>
            <strong>{title}</strong>
            <button type="button" onClick={() => setOpen(false)} aria-label="Fermer le chat">
              <span className="material-symbols-outlined" aria-hidden="true">close</span>
            </button>
          </header>
          <div className="floating-info-chat__messages">
            {messages.map((message, index) => (
              <p className={`floating-info-chat__message is-${message.author}`} key={`${message.author}-${index}`}>{message.text}</p>
            ))}
          </div>
          {requiresContact ? (
            <form className="floating-info-chat__contact" onSubmit={submitContact}>
              <p>Veuillez renseigner vos coordonnées avant d’envoyer un message.</p>
              <input required value={contactDraft.name} onChange={(event) => setContactDraft((current) => ({ ...current, name: event.target.value }))} placeholder="Nom complet" aria-label="Nom complet" />
              <input required type="email" value={contactDraft.email} onChange={(event) => setContactDraft((current) => ({ ...current, email: event.target.value }))} placeholder="Email" aria-label="Email" />
              <input required type="tel" value={contactDraft.phone} onChange={(event) => setContactDraft((current) => ({ ...current, phone: event.target.value }))} placeholder="Téléphone" aria-label="Téléphone" />
              <button type="submit">Continuer</button>
            </form>
          ) : (
            <form className="floating-info-chat__composer" onSubmit={submit}>
              <input value={draft} onChange={(event) => setDraft(event.target.value)} placeholder="Écrire un message..." aria-label="Message" />
              <button type="submit" aria-label="Envoyer">
                <span className="material-symbols-outlined" aria-hidden="true">send</span>
              </button>
            </form>
          )}
        </section>
      )}
    </div>
  );
}
