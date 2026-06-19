import { Badge, Button, Card, Input, Select } from '../../components/ui';

export function CheckoutPage() {
  return (
    <main className="ds-shell">
      <section className="ds-page py-8 md:py-12">
        <div className="mb-6">
          <p className="ds-hero__eyebrow">Checkout</p>
          <h1 className="ds-hero__title">Paiement sécurisé</h1>
          <p className="ds-hero__subtitle">Livraison, paiement et confirmation dans un parcours simple et rassurant.</p>
        </div>

        <div className="ds-grid ds-grid--split">
          <div className="space-y-6">
            <Card>
              <h2 className="text-xl font-bold">1. Livraison</h2>
              <div className="mt-4 grid gap-4 md:grid-cols-2">
                <Input placeholder="Nom complet" />
                <Input placeholder="Téléphone" />
                <Input className="md:col-span-2" placeholder="Adresse" />
                <Input placeholder="Ville" />
                <Input placeholder="Code postal" />
              </div>
            </Card>

            <Card>
              <h2 className="text-xl font-bold">2. Mode de livraison</h2>
              <div className="mt-4 grid gap-3 md:grid-cols-2">
                <div className="rounded-2xl border border-[color:var(--ds-outline-variant)] bg-white p-4"><strong>Standard</strong><p className="text-sm text-[color:var(--ds-on-surface-variant)]">3 à 5 jours - Gratuit</p></div>
                <div className="rounded-2xl border border-[color:var(--ds-outline-variant)] bg-white p-4"><strong>Express</strong><p className="text-sm text-[color:var(--ds-on-surface-variant)]">24h - 9,90 €</p></div>
              </div>
            </Card>

            <Card>
              <h2 className="text-xl font-bold">3. Paiement</h2>
              <div className="mt-4 grid gap-4 md:grid-cols-2">
                <Select>
                  <option>Carte bancaire</option>
                  <option>Virement</option>
                </Select>
                <Input placeholder="Titulaire" />
                <Input placeholder="Numéro de carte" />
                <Input placeholder="MM/AA" />
              </div>
            </Card>
          </div>

          <Card>
            <h2 className="text-xl font-bold">Récapitulatif</h2>
            <div className="mt-4 space-y-3 text-sm">
              <div className="flex justify-between"><span>Articles</span><strong>423,90 €</strong></div>
              <div className="flex justify-between"><span>Livraison</span><strong>9,90 €</strong></div>
              <div className="flex justify-between border-t border-[color:var(--ds-outline-variant)] pt-3 text-base"><span>Total</span><strong>433,80 €</strong></div>
            </div>
            <div className="mt-6 flex items-center gap-3">
              <Badge tone="success">Sécurisé</Badge>
              <Badge tone="neutral">SSL</Badge>
            </div>
            <Button variant="primary" className="mt-6 w-full">Confirmer et payer</Button>
          </Card>
        </div>
      </section>
    </main>
  );
}
