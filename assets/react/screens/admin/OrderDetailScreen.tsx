import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { appIcons } from '../../icons/fontAwesome';
import { Badge, Button, Card } from '../../components/ui';

export function OrderDetailScreen() {
  return (
    <section className="space-y-6">
      <Card className="ds-hero">
        <div className="flex flex-wrap items-center justify-between gap-4">
          <div>
            <p className="ds-hero__eyebrow">Commande</p>
            <h1 className="ds-hero__title">Détail de Commande</h1>
            <p className="ds-hero__subtitle">Suivi, livraison, paiements et actions opérationnelles.</p>
          </div>
          <div className="flex flex-wrap gap-2">
            <Badge tone="success">Payé</Badge>
            <Badge tone="warning">Expédié</Badge>
          </div>
        </div>
      </Card>

      <div className="ds-grid ds-grid--split">
        <div className="space-y-6">
          <Card>
            <div className="flex items-center justify-between gap-3">
              <div>
                <h2 className="text-xl font-bold">#ORD-7829</h2>
                <p className="text-sm text-[color:var(--ds-on-surface-variant)]">Jean Dupont · jean.dupont@email.com</p>
              </div>
              <Button variant="secondary" onClick={() => window.print()}><FontAwesomeIcon icon={appIcons.pos} /> Imprimer</Button>
            </div>
          </Card>

          <Card>
            <h2 className="text-xl font-bold">Articles commandés</h2>
            <table className="ds-table mt-4">
              <thead>
                <tr><th>Produit</th><th>Qté</th><th>Prix</th><th>Total</th></tr>
              </thead>
              <tbody>
                <tr><td>Robe de Soirée</td><td>1</td><td>245,90 €</td><td>245,90 €</td></tr>
                <tr><td>Pochette satin</td><td>2</td><td>89,00 €</td><td>178,00 €</td></tr>
              </tbody>
            </table>
          </Card>

          <Card>
            <h2 className="text-xl font-bold">Historique</h2>
            <div className="mt-4 space-y-3">
              {['Commande passée', 'Paiement confirmé', 'Préparation', 'Expédiée'].map((step, index) => (
                <div key={step} className="flex items-center gap-3 rounded-2xl border border-[color:var(--ds-outline-variant)] bg-white p-4">
                  <div className="inline-flex h-8 w-8 items-center justify-center rounded-full bg-[color:var(--ds-secondary-container)] text-sm font-bold text-[color:var(--ds-primary)]">{index + 1}</div>
                  <div>
                    <strong>{step}</strong>
                    <p className="text-sm text-[color:var(--ds-on-surface-variant)]">16/03/2025 · 14:20</p>
                  </div>
                </div>
              ))}
            </div>
          </Card>
        </div>

        <div className="space-y-6">
          <Card>
            <h2 className="text-xl font-bold">Livraison</h2>
            <p className="mt-3 text-sm text-[color:var(--ds-on-surface-variant)]">Tracking: <strong>TRK-772299</strong></p>
            <p className="mt-2 text-sm text-[color:var(--ds-on-surface-variant)]">Livraison standard · ETA 18/03/2025</p>
            <div className="mt-5 flex flex-col gap-3">
              <Button variant="primary" onClick={() => { window.alert('Commande marquée comme livrée dans l’interface.'); }}>Marquer comme livré</Button>
              <Button variant="secondary" onClick={() => { window.open('https://www.google.com/search?q=TRK-772299+tracking', '_blank', 'noopener,noreferrer'); }}>Voir le tracking</Button>
            </div>
          </Card>

          <Card>
            <h2 className="text-xl font-bold">Client</h2>
            <p className="mt-3 text-sm text-[color:var(--ds-on-surface-variant)]">Adresse de livraison complète et coordonnées du client.</p>
          </Card>
        </div>
      </div>
    </section>
  );
}
