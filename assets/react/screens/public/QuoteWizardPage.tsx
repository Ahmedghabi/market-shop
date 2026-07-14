import { Badge, Button, Card, Input, Textarea } from '../../components/ui';

export function QuoteWizardPage() {
  return (
    <main className="ds-shell">
      <section className="ds-page py-8 md:py-12">
        <div className="mb-6">
          <p className="ds-hero__eyebrow">Devis</p>
          <h1 className="ds-hero__title">Système de devis multi-étapes</h1>
          <p className="ds-hero__subtitle">Sélection des produits, coordonnées client et confirmation finale en un flux guidé.</p>
        </div>

        <div className="ds-stepper mb-6">
          {['Produits', 'Client', 'Confirmation'].map((step, index) => (
            <div className="ds-stepper__item" key={step}>
              <span className="ds-stepper__index">{index + 1}</span>
              <div>
                <strong>{step}</strong>
                <p className="text-sm text-[color:var(--ds-on-surface-variant)]">Étape {index + 1}</p>
              </div>
            </div>
          ))}
        </div>

        <div className="ds-grid ds-grid--split">
          <Card>
            <h2 className="text-xl font-bold">Produits sélectionnés</h2>
            <div className="mt-4 space-y-4">
              {['Robe de Soirée', 'Pochette satin', 'Escarpins'].map((item) => (
                <div key={item} className="flex items-center justify-between gap-4 rounded-2xl border border-[color:var(--ds-outline-variant)] bg-white p-4">
                  <div>
                    <strong>{item}</strong>
                    <p className="text-sm text-[color:var(--ds-on-surface-variant)]">Quantité et estimation</p>
                  </div>
                  <div className="flex items-center gap-2">
                    <Input className="w-20" defaultValue={1} type="number" />
                    <Badge tone="neutral">245,90 DT</Badge>
                  </div>
                </div>
              ))}
            </div>
          </Card>

          <div className="space-y-6">
            <Card>
              <h2 className="text-xl font-bold">Informations client</h2>
              <div className="mt-4 grid gap-4">
                <Input placeholder="Nom" />
                <Input placeholder="Email" />
                <Input placeholder="Téléphone" />
                <Textarea placeholder="Message" />
              </div>
            </Card>
            <Card>
              <h2 className="text-xl font-bold">Récapitulatif</h2>
              <p className="mt-2 text-sm text-[color:var(--ds-on-surface-variant)]">Estimation totale: 423,90 DT</p>
              <Button variant="primary" className="mt-5 w-full">Envoyer la demande</Button>
            </Card>
          </div>
        </div>
      </section>
    </main>
  );
}
