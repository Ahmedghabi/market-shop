import { Badge, Button, Card } from '../../components/ui';

export function MarketingPromotionsScreen() {
  return (
    <section className="space-y-6">
      <Card className="ds-hero">
        <p className="ds-hero__eyebrow">Marketing</p>
        <h1 className="ds-hero__title">Marketing & Promotions</h1>
        <p className="ds-hero__subtitle">Promotions globales, campagnes, priorités produit et visibilité boutique.</p>
      </Card>
      <div className="ds-grid ds-grid--cards">
        {['Promo été', 'Offres privées', 'Lancement nouvelle collection'].map((label) => (
          <Card key={label}>
            <Badge tone="neutral">Actif</Badge>
            <h2 className="mt-3 text-xl font-bold">{label}</h2>
            <p className="mt-2 text-sm text-[color:var(--ds-on-surface-variant)]">Campagne prioritaire et publication multi-boutique.</p>
            <Button variant="secondary" className="mt-5 w-full" onClick={() => { window.alert('Édition promotion à connecter à l’API.'); }}>Éditer</Button>
          </Card>
        ))}
      </div>
    </section>
  );
}
