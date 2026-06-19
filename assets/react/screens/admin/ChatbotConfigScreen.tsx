import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { useEffect, useState } from 'react';
import { appIcons } from '../../icons/fontAwesome';
import { Badge, Button, Card, Input, Textarea } from '../../components/ui';

function Switch({ checked, onChange }: { checked: boolean; onChange: (checked: boolean) => void }) {
  return (
    <button
      type="button"
      className={`h-6 w-12 rounded-full p-1 transition ${checked ? 'bg-[color:var(--ds-primary)]' : 'bg-[color:var(--ds-outline-variant)]'}`}
      onClick={() => onChange(!checked)}
      aria-pressed={checked}
    >
      <span className={`block h-4 w-4 rounded-full bg-white shadow transition ${checked ? 'ml-auto' : ''}`} />
    </button>
  );
}

export function ChatbotConfigScreen() {
  const [boutiqueChatEnabled, setBoutiqueChatEnabled] = useState(() => localStorage.getItem('hanooty_boutique_chat_enabled') !== 'false');
  const [globalChatEnabled, setGlobalChatEnabled] = useState(() => localStorage.getItem('hanooty_global_chat_enabled') !== 'false');

  useEffect(() => {
    localStorage.setItem('hanooty_boutique_chat_enabled', boutiqueChatEnabled ? 'true' : 'false');
  }, [boutiqueChatEnabled]);

  useEffect(() => {
    localStorage.setItem('hanooty_global_chat_enabled', globalChatEnabled ? 'true' : 'false');
  }, [globalChatEnabled]);

  return (
    <section className="space-y-6">
      <Card className="ds-hero">
        <div className="flex flex-wrap items-center justify-between gap-4">
          <div>
            <p className="ds-hero__eyebrow">Chatbot</p>
            <h1 className="ds-hero__title">Configuration du Chatbot</h1>
            <p className="ds-hero__subtitle">Ton, règles, réponses rapides et intégration front-office.</p>
          </div>
          <div className="flex gap-2">
            <Badge tone="success">Publié</Badge>
            <Badge tone="neutral">Front-office</Badge>
          </div>
        </div>
      </Card>

      <div className="ds-grid ds-grid--split">
        <div className="space-y-6">
          <Card>
            <h2 className="text-xl font-bold">Personnalité & Ton</h2>
            <div className="mt-4 space-y-4">
              <div>
                <label className="mb-1 block text-sm font-medium">Ton général</label>
                <select className="ds-select w-full">
                  <option>Professionnel & rassurant</option>
                  <option>Décontracté & amical</option>
                  <option>Luxe & premium</option>
                  <option>Technique & précis</option>
                </select>
              </div>
              <div>
                <label className="mb-1 block text-sm font-medium">Instructions personnalisées</label>
                <Textarea
                  placeholder="Décrivez le comportement attendu du chatbot..."
                  defaultValue="Répondre de façon courtoise et professionnelle. Proposer des alternatives si un produit n'est pas disponible. Rediriger vers le service client en cas de réclamation."
                  rows={4}
                />
              </div>
            </div>
          </Card>

          <Card>
            <h2 className="text-xl font-bold">Réponses rapides</h2>
            <div className="mt-4 space-y-3">
              {[
                { trigger: 'Livraison', response: 'Nos délais de livraison sont de 3 à 5 jours ouvrés.' },
                { trigger: 'Retour', response: 'Vous disposez de 30 jours pour retourner un article.' },
                { trigger: 'Stock', response: 'Je vérifie la disponibilité pour vous...' },
              ].map((r) => (
                <div key={r.trigger} className="rounded-2xl border border-[color:var(--ds-outline-variant)] bg-white p-4">
                  <div className="flex items-start justify-between">
                    <div>
                      <strong className="text-sm">{r.trigger}</strong>
                      <p className="mt-1 text-sm text-[color:var(--ds-on-surface-variant)]">{r.response}</p>
                    </div>
                    <Button variant="ghost" onClick={() => { window.alert(`Édition de la réponse rapide "${r.trigger}" à connecter à l’API.`); }}>
                      <FontAwesomeIcon icon={appIcons.edit} />
                    </Button>
                  </div>
                </div>
              ))}
              <Button variant="secondary" className="w-full" onClick={() => { window.alert('Ajout de réponse rapide à connecter à l’API.'); }}>
                <FontAwesomeIcon icon={appIcons.plus} /> Ajouter une réponse
              </Button>
            </div>
          </Card>
        </div>

        <div className="space-y-6">
          <Card>
            <h2 className="text-xl font-bold">Statut & Visibilité</h2>
            <div className="mt-4 space-y-4">
              <div className="flex items-center justify-between rounded-2xl border border-[color:var(--ds-outline-variant)] bg-white p-4">
                <span>Chat boutique activé</span>
                <Switch checked={boutiqueChatEnabled} onChange={setBoutiqueChatEnabled} />
              </div>
              <div className="flex items-center justify-between rounded-2xl border border-[color:var(--ds-outline-variant)] bg-white p-4">
                <span>Assistant général home</span>
                <Switch checked={globalChatEnabled} onChange={setGlobalChatEnabled} />
              </div>
              <div className="flex items-center justify-between rounded-2xl border border-[color:var(--ds-outline-variant)] bg-white p-4">
                <span>Visible sur le front-office</span>
                <Badge tone={boutiqueChatEnabled || globalChatEnabled ? 'success' : 'neutral'}>{boutiqueChatEnabled || globalChatEnabled ? 'Oui' : 'Non'}</Badge>
              </div>
              <div className="flex items-center justify-between rounded-2xl border border-[color:var(--ds-outline-variant)] bg-white p-4">
                <span>Assistant WhatsApp</span>
                <Badge tone="neutral">Non configuré</Badge>
              </div>
            </div>
          </Card>

          <Card>
            <h2 className="text-xl font-bold">Limites & Sécurité</h2>
            <div className="mt-4 space-y-4">
              <div className="flex items-center justify-between">
                <span className="text-sm">Messages max par session</span>
                <Input type="number" defaultValue={50} className="w-24 text-right" />
              </div>
              <div className="flex items-center justify-between">
                <span className="text-sm">Seuil de mécontentement</span>
                <select className="ds-select w-32">
                  <option>Bas</option>
                  <option selected>Moyen</option>
                  <option>Élevé</option>
                </select>
              </div>
            </div>
            <Button variant="primary" className="mt-6 w-full" onClick={() => { window.alert('Configuration chatbot enregistrée localement.'); }}>Enregistrer</Button>
          </Card>

          <Card>
            <h2 className="text-xl font-bold">Aperçu</h2>
            <div className="mt-4 rounded-2xl border border-[color:var(--ds-outline-variant)] bg-white p-4">
              <div className="flex items-start gap-3">
                <div className="flex h-8 w-8 items-center justify-center rounded-full bg-[color:var(--ds-primary-container)] text-sm font-bold text-[color:var(--ds-primary)]">C</div>
                <div>
                  <p className="rounded-2xl rounded-tl-none bg-[color:var(--ds-surface-container-high)] p-3 text-sm">
                    Bonjour ! Je suis l'assistant de Luxe Paris. Comment puis-je vous aider ?
                  </p>
                </div>
              </div>
            </div>
          </Card>
        </div>
      </div>
    </section>
  );
}
