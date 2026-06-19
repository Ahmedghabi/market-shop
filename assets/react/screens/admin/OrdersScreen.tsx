import { Badge, Button, Card } from '../../components/ui';

export function OrdersScreen() {
  return (
    <section className="space-y-6">
      <Card className="ds-hero">
        <div className="flex flex-wrap items-start justify-between gap-4">
          <div>
            <p className="ds-hero__eyebrow">Commandes</p>
            <h1 className="ds-hero__title">Orders & Fulfillment</h1>
            <p className="ds-hero__subtitle">Commandes web, préparation, livraison et suivi en temps réel.</p>
          </div>
          <Badge tone="success">Flux actif</Badge>
        </div>
      </Card>
      <Card>
        <div className="space-y-3">
          {[
            ['#ORD-7829', 'Jean Dupont', '245,90 €', 'Expédié', 'success'],
            ['#ORD-7828', 'Marie Leclerc', '1 120,00 €', 'En attente', 'warning'],
          ].map(([order, customer, amount, status, tone]) => (
            <div key={order} className="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-[color:var(--ds-outline-variant)] bg-[color:var(--ds-surface-container-lowest)] p-4">
              <div>
                <strong className="block">{order}</strong>
                <p className="text-sm text-[color:var(--ds-on-surface-variant)]">{customer}</p>
              </div>
              <div className="flex items-center gap-4">
                <span className="font-semibold">{amount}</span>
                <Badge tone={tone as 'success' | 'warning'}>{status}</Badge>
                <Button variant="ghost" onClick={() => { window.location.href = '/admin/orders/ord-7829'; }}>Détail</Button>
              </div>
            </div>
          ))}
        </div>
      </Card>
    </section>
  );
}
