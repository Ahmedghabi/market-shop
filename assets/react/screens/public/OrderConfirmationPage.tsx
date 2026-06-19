import { useMemo } from 'react';
import { Badge, Button, Card } from '../../components/ui';

type LastOrderSummary = {
  orderId: string;
  status: string;
  totalCents: number;
  currency: string;
  customerName?: string;
};

export function OrderConfirmationPage() {
  const orderId = useMemo(() => new URLSearchParams(window.location.search).get('orderId') || '', []);
  const summary = useMemo(() => {
    try {
      const raw = window.sessionStorage.getItem('market-shop:last-order');

      return raw ? JSON.parse(raw) as LastOrderSummary : null;
    } catch {
      return null;
    }
  }, []);
  const displayedOrderId = orderId || summary?.orderId || '';
  const total = (((summary?.totalCents ?? 0) / 100)).toFixed(2);

  return (
    <main className="ds-shell">
      <section className="ds-page py-8 md:py-12">
        <Card className="ds-hero text-center">
          <Badge tone="success" className="mx-auto">Succès</Badge>
          <h1 className="ds-hero__title mt-4">Commande confirmée</h1>
          <p className="ds-hero__subtitle mx-auto">Votre commande a bien été créée. Vous pouvez garder ce numéro pour le suivi.</p>
          <div className="mx-auto mt-6 max-w-2xl rounded-2xl border border-[color:var(--ds-outline-variant)] bg-white p-6 text-left">
            <p className="text-sm text-[color:var(--ds-on-surface-variant)]">N° commande</p>
            <strong className="text-2xl">{displayedOrderId ? `#${displayedOrderId}` : 'Commande créée'}</strong>
            <div className="mt-4 grid gap-4 md:grid-cols-2">
              <div>
                <p className="text-sm text-[color:var(--ds-on-surface-variant)]">Statut</p>
                <strong>{summary?.status || 'pending'}</strong>
              </div>
              <div>
                <p className="text-sm text-[color:var(--ds-on-surface-variant)]">Total</p>
                <strong>{total} {summary?.currency || 'EUR'}</strong>
              </div>
            </div>
            {summary?.customerName ? (
              <p className="mt-4 text-sm text-[color:var(--ds-on-surface-variant)]">Client: <strong>{summary.customerName}</strong></p>
            ) : null}
            <div className="mt-4 grid gap-3 md:grid-cols-4">
              {['Confirmée', 'Préparée', 'Expédiée', 'Livrée'].map((step, index) => (
                <div key={step} className={`rounded-2xl p-3 text-center ${index === 0 ? 'bg-[color:var(--ds-secondary-container)]' : 'bg-[color:var(--ds-surface-container-low)]'}`}>
                  <strong className="block">{step}</strong>
                  <span className="text-xs text-[color:var(--ds-on-surface-variant)]">Étape {index + 1}</span>
                </div>
              ))}
            </div>
          </div>
          <div className="mt-8 flex flex-wrap justify-center gap-3">
            <Button variant="primary" onClick={() => { window.location.href = '/'; }}>Retour à la boutique</Button>
            <Button variant="secondary" onClick={() => { window.history.back(); }}>Retour</Button>
          </div>
        </Card>
      </section>
    </main>
  );
}
