import { Badge, Button, Card } from '../../components/ui';

export function OrderConfirmationPage() {
  return (
    <main className="ds-shell">
      <section className="ds-page py-8 md:py-12">
        <Card className="ds-hero text-center">
          <Badge tone="success" className="mx-auto">Succès</Badge>
          <h1 className="ds-hero__title mt-4">Commande confirmée</h1>
          <p className="ds-hero__subtitle mx-auto">Votre paiement a été validé. Un email de confirmation a été envoyé.</p>
          <div className="mx-auto mt-6 max-w-2xl rounded-2xl border border-[color:var(--ds-outline-variant)] bg-white p-6 text-left">
            <p className="text-sm text-[color:var(--ds-on-surface-variant)]">N° commande</p>
            <strong className="text-2xl">#ORD-4521</strong>
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
            <Button variant="primary">Suivre ma commande</Button>
            <Button variant="secondary">Retourner à la boutique</Button>
          </div>
        </Card>
      </section>
    </main>
  );
}
