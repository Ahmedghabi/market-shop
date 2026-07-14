import { Truck, RotateCcw, ShieldCheck, Headphones } from 'lucide-react';

const items = [
  { icon: Truck, title: 'Livraison offerte', desc: 'Dès 60 DT d\'achat' },
  { icon: RotateCcw, title: 'Retours 30 jours', desc: 'Satisfait ou remboursé' },
  { icon: ShieldCheck, title: 'Paiement sécurisé', desc: 'CB, Apple Pay, PayPal' },
  { icon: Headphones, title: 'Support 7j/7', desc: 'Une équipe dédiée' },
];

export function TrustBadges() {
  return (
    <section className="border-y border-[color:var(--ds-outline-variant)] bg-[color:var(--ds-surface-container-low)]/30">
      <div className="mx-auto max-w-7xl px-4 py-6 grid grid-cols-2 md:grid-cols-4 gap-4">
        {items.map((i) => (
          <div key={i.title} className="flex items-center gap-3">
            <div className="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-[color:var(--ds-primary)]/10 text-[color:var(--ds-primary)]">
              <i.icon className="h-5 w-5" />
            </div>
            <div className="min-w-0">
              <div className="truncate text-sm font-semibold text-[color:var(--ds-on-surface)]">{i.title}</div>
              <div className="truncate text-xs text-[color:var(--ds-on-surface-variant)]">{i.desc}</div>
            </div>
          </div>
        ))}
      </div>
    </section>
  );
}
