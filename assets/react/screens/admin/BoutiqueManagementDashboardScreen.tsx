import { Badge, Button, Card } from '../../components/ui';

export function BoutiqueManagementDashboardScreen({ boutiquesCount = 0 }: { boutiquesCount?: number }) {
  return (
    <section className="space-y-6">
      <Card className="ds-hero">
        <p className="ds-hero__eyebrow">Boutique</p>
        <h1 className="ds-hero__title">Boutique Management Dashboard</h1>
        <p className="ds-hero__subtitle">KPI opérationnels, commandes récentes et gestion boutique.</p>
      </Card>
      <div className="ds-grid ds-grid--cards">
        <Card><p className="text-sm text-[color:var(--ds-on-surface-variant)]">Boutiques</p><h2 className="mt-2 text-3xl font-bold">{boutiquesCount}</h2></Card>
        <Card><p className="text-sm text-[color:var(--ds-on-surface-variant)]">Commandes</p><h2 className="mt-2 text-3xl font-bold">45</h2></Card>
        <Card><p className="text-sm text-[color:var(--ds-on-surface-variant)]">Produits</p><h2 className="mt-2 text-3xl font-bold">128</h2></Card>
        <Card><p className="text-sm text-[color:var(--ds-on-surface-variant)]">Statut</p><h2 className="mt-2 text-3xl font-bold">Actif</h2></Card>
      </div>
      <Card>
        <div className="flex flex-wrap items-center justify-between gap-3">
          <div>
            <h2 className="text-xl font-bold">Actions rapides</h2>
            <p className="text-sm text-[color:var(--ds-on-surface-variant)]">Créer boutique, valider abonnement, publier front-office.</p>
          </div>
          <div className="flex gap-3">
            <Button variant="primary" onClick={() => { window.location.href = '/admin/boutiques'; }}>Créer boutique</Button>
            <Button variant="secondary" onClick={() => { window.location.href = '/admin/subscriptions'; }}>Gérer abonnements</Button>
          </div>
        </div>
      </Card>
    </section>
  );
}
