import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { useEffect, useState } from 'react';
import { appIcons } from '../../icons/fontAwesome';
import { Badge, Button, Card } from '../../components/ui';

type BoutiqueItem = {
  id: string;
  name: string;
  slug: string;
};

type CartItem = {
  id: string;
  productName: string | null;
  quantity: number;
  unitPriceCents: number;
  totalCents: number;
};

type CartOutput = {
  id: string;
  boutiqueId: string;
  currency: string;
  itemsCount: number;
  totalCents: number;
  items: CartItem[];
};

export function CartPage() {
  const boutiqueSlug = window.location.pathname.match(/^\/boutiques\/([^/]+)\/cart/)?.[1] ?? '';
  const [boutique, setBoutique] = useState<BoutiqueItem | null>(null);
  const [cart, setCart] = useState<CartOutput | null>(null);

  useEffect(() => {
    if (!boutiqueSlug) return;
    fetch(`/api/boutiques/${boutiqueSlug}`)
      .then((response) => response.ok ? response.json() : null)
      .then((payload: BoutiqueItem | null) => {
        setBoutique(payload);
        if (!payload) return null;

        return fetch(`/api/boutiques/${payload.id}/cart`);
      })
      .then((response) => response && response.ok ? response.json() : null)
      .then((payload: CartOutput | null) => {
        if (payload) setCart(payload);
      })
      .catch(() => {});
  }, [boutiqueSlug]);

  const items = cart?.items ?? [];
  const currency = cart?.currency ?? 'EUR';
  const total = ((cart?.totalCents ?? 0) / 100).toFixed(2);

  return (
    <main className="ds-shell">
      <section className="ds-page py-8 md:py-12">
        <div className="ds-grid ds-grid--split">
          <Card>
            <div className="mb-5 flex items-center justify-between">
              <div>
                <p className="ds-hero__eyebrow">Panier</p>
                <h1 className="mt-2 text-3xl font-bold">{boutique?.name ?? 'Boutique'}</h1>
              </div>
              <Badge tone="neutral">{cart?.itemsCount ?? 0} article{(cart?.itemsCount ?? 0) > 1 ? 's' : ''}</Badge>
            </div>

            <div className="space-y-4">
              {items.length > 0 ? items.map((item) => (
                <div key={item.id} className="flex items-center gap-4 rounded-2xl border border-[color:var(--ds-outline-variant)] bg-white p-4">
                  <div className="flex h-20 w-20 items-center justify-center rounded-xl bg-[color:var(--ds-surface-container-low)] text-[color:var(--ds-primary)]">
                    <FontAwesomeIcon icon={appIcons.products} />
                  </div>
                  <div className="flex-1">
                    <strong>{item.productName ?? 'Produit'}</strong>
                    <p className="text-sm text-[color:var(--ds-on-surface-variant)]">Qté: {item.quantity}</p>
                  </div>
                  <div className="text-right">
                    <strong>{(item.totalCents / 100).toFixed(2)} {currency}</strong>
                    <p className="text-sm text-[color:var(--ds-on-surface-variant)]">Sous-total</p>
                  </div>
                </div>
              )) : (
                <div className="rounded-2xl border border-dashed border-[color:var(--ds-outline-variant)] bg-white p-8 text-center text-[color:var(--ds-on-surface-variant)]">
                  Votre panier boutique est vide.
                </div>
              )}
            </div>
          </Card>

          <Card>
            <h2 className="text-xl font-bold">Récapitulatif</h2>
            <div className="mt-4 space-y-3 text-sm">
              <div className="flex justify-between"><span>Sous-total</span><strong>{total} {currency}</strong></div>
              <div className="flex justify-between"><span>Livraison</span><strong>Calculée au checkout</strong></div>
              <div className="flex justify-between border-t border-[color:var(--ds-outline-variant)] pt-3 text-base"><span>Total</span><strong>{total} {currency}</strong></div>
            </div>
            <div className="mt-6 space-y-3">
              <Button variant="primary" className="w-full" onClick={() => { window.location.href = `/checkout?boutique=${boutiqueSlug}`; }}><FontAwesomeIcon icon={appIcons.security} /> Commander</Button>
              <Button variant="secondary" className="w-full">Demander un devis</Button>
            </div>
          </Card>
        </div>
      </section>
    </main>
  );
}
