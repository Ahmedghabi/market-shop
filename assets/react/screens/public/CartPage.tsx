import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { useEffect, useState } from 'react';
import { motion } from 'framer-motion';
import { ImageWithFallback } from '../../components/ImageWithFallback';
import { Minus, Plus, Trash2 } from 'lucide-react';
import { appIcons } from '../../icons/fontAwesome';
import { Badge, Button, Card } from '../../components/ui';
import { authHeaders, boutiqueLink, boutiqueQuery, resolveBoutiqueSlug } from './boutiqueRouting';
import { applyStorefrontTheme, resetStorefrontTheme, type StorefrontThemeData } from '../../theme/storefrontThemeRoot';
import { StorefrontHeader } from './storefront/StorefrontHeader';
import type { StoreBoutique } from './storefront/StorefrontTheme';

type BoutiqueItem = StorefrontThemeData & {
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
  const boutiqueSlug = resolveBoutiqueSlug(/^\/boutiques\/([^/]+)\/cart/);
  const [boutique, setBoutique] = useState<BoutiqueItem | null>(null);
  const [cart, setCart] = useState<CartOutput | null>(null);
  const [removingItemId, setRemovingItemId] = useState<string | null>(null);
  const [updatingItemId, setUpdatingItemId] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (!boutiqueSlug) return;
    window.__boutiqueSlug__ = boutiqueSlug;
    resetStorefrontTheme();
    const headers = authHeaders();
    fetch(`/api/boutiques/${boutiqueSlug}`, { headers })
      .then((response) => response.ok ? response.json() : null)
      .then((payload: BoutiqueItem | null) => {
        if (payload) applyStorefrontTheme(payload);
        setBoutique(payload);
        if (!payload) return null;

         return fetch(`/api/cart${boutiqueQuery(boutiqueSlug)}`, { headers });
      })
      .then((response) => response && response.ok ? response.json() : null)
      .then((payload: CartOutput | null) => {
        if (payload) setCart(payload);
      })
      .catch(() => {});

    return resetStorefrontTheme;
  }, [boutiqueSlug]);

  const removeItem = async (itemId: string) => {
    setRemovingItemId(itemId);
    setError(null);
    try {
      const response = await fetch(`/api/cart/items/${itemId}${boutiqueQuery(boutiqueSlug)}`, {
        method: 'DELETE',
        headers: authHeaders(),
      });
      if (!response.ok) throw new Error('Impossible de supprimer cet article.');

      const refreshedCart = await fetch(`/api/cart${boutiqueQuery(boutiqueSlug)}`, {
        headers: authHeaders(),
      });
      if (!refreshedCart.ok) throw new Error('Impossible de recharger le panier.');
      setCart(await refreshedCart.json() as CartOutput);
    } catch (removeError) {
      setError(removeError instanceof Error ? removeError.message : 'Impossible de supprimer cet article.');
    } finally {
      setRemovingItemId(null);
    }
  };

  const updateQuantity = async (itemId: string, quantity: number) => {
    if (quantity < 1) {
      await removeItem(itemId);
      return;
    }

    setUpdatingItemId(itemId);
    setError(null);
    try {
      const response = await fetch(`/api/cart/items/${itemId}${boutiqueQuery(boutiqueSlug)}`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/merge-patch+json', ...authHeaders() },
        body: JSON.stringify({ quantity }),
      });
      if (!response.ok) throw new Error('Impossible de modifier la quantité.');

      const refreshedCart = await fetch(`/api/cart${boutiqueQuery(boutiqueSlug)}`, {
        headers: authHeaders(),
      });
      if (!refreshedCart.ok) throw new Error('Impossible de recharger le panier.');
      setCart(await refreshedCart.json() as CartOutput);
    } catch (updateError) {
      setError(updateError instanceof Error ? updateError.message : 'Impossible de modifier la quantité.');
    } finally {
      setUpdatingItemId(null);
    }
  };

  const items = cart?.items ?? [];
  const currency = cart?.currency ?? 'TND';
  const total = ((cart?.totalCents ?? 0) / 100).toFixed(2);

  return (
    <main className="ds-shell min-h-screen bg-[color:var(--sf-bg,#f6f2eb)] text-[color:var(--sf-text,#171717)]">
      {boutique && <StorefrontHeader boutique={boutique as StoreBoutique} showCart={false} />}
      <section className="ds-page py-8 md:py-12">
        <div className="ds-grid ds-grid--split">
          <Card>
            <div className="mb-5 flex items-center justify-between">
              <div>
                <p className="ds-hero__eyebrow">Panier</p>
                <div className="mt-2 flex items-center gap-3">
                  {boutique && <ImageWithFallback src={boutique.logoUrl} alt={boutique.logoUrl ? boutique.name : 'Hanooti'} className="h-10 w-10 rounded-full object-cover" />}
                  <h1 className="text-3xl font-bold">{boutique?.name ?? 'Boutique'}</h1>
                </div>
              </div>
              <Badge tone="neutral">{cart?.itemsCount ?? 0} article{(cart?.itemsCount ?? 0) > 1 ? 's' : ''}</Badge>
            </div>

            {error && <p role="alert" className="mb-4 rounded-xl bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">{error}</p>}
            <div className="space-y-4">
              {items.length > 0 ? items.map((item, index) => (
                <motion.div
                  key={item.id}
                  initial={{ opacity: 0, x: -12 }}
                  animate={{ opacity: 1, x: 0 }}
                  transition={{ delay: index * 0.05, duration: 0.3 }}
                  className="flex items-center gap-4 rounded-2xl border border-[color:var(--sf-outline,#d8d0c4)] bg-[color:var(--sf-surface,#ffffff)] p-4"
                >
                  <div className="flex h-20 w-20 items-center justify-center rounded-xl bg-[color:var(--sf-surface-muted,#ece5d9)] text-[color:var(--sf-accent,#111111)]">
                    <FontAwesomeIcon icon={appIcons.products} />
                  </div>
                  <div className="flex-1">
                    <strong>{item.productName ?? 'Produit'}</strong>
                    <div className="mt-2 flex items-center gap-2 text-sm text-[color:var(--sf-text-muted,#6b6560)]">
                      <span>Qté:</span>
                      <button
                        type="button"
                        className="grid h-8 w-8 cursor-pointer place-items-center rounded-full border border-[color:var(--sf-outline,var(--ds-outline-variant))] text-[color:var(--sf-text,var(--ds-on-surface))] transition-colors hover:bg-[color:var(--sf-surface-muted,var(--ds-surface-container))] disabled:cursor-not-allowed disabled:opacity-50"
                        aria-label={`Diminuer la quantité de ${item.productName ?? 'cet article'}`}
                        disabled={removingItemId === item.id || updatingItemId === item.id}
                        onClick={() => { void updateQuantity(item.id, item.quantity - 1); }}
                      >
                        <Minus className="h-3.5 w-3.5" aria-hidden="true" />
                      </button>
                      <strong className="min-w-6 text-center text-[color:var(--sf-text,var(--ds-on-surface))]">{item.quantity}</strong>
                      <button
                        type="button"
                        className="grid h-8 w-8 cursor-pointer place-items-center rounded-full border border-[color:var(--sf-outline,var(--ds-outline-variant))] text-[color:var(--sf-text,var(--ds-on-surface))] transition-colors hover:bg-[color:var(--sf-surface-muted,var(--ds-surface-container))] disabled:cursor-not-allowed disabled:opacity-50"
                        aria-label={`Augmenter la quantité de ${item.productName ?? 'cet article'}`}
                        disabled={removingItemId === item.id || updatingItemId === item.id}
                        onClick={() => { void updateQuantity(item.id, item.quantity + 1); }}
                      >
                        <Plus className="h-3.5 w-3.5" aria-hidden="true" />
                      </button>
                    </div>
                  </div>
                  <div className="text-right">
                    <strong>{(item.totalCents / 100).toFixed(2)} {currency}</strong>
                    <p className="text-sm text-[color:var(--sf-text-muted,#6b6560)]">Sous-total</p>
                    <Button
                      type="button"
                      variant="ghost"
                      className="mt-2 min-h-0 px-2 py-1 text-xs"
                      disabled={removingItemId === item.id}
                      onClick={() => { void removeItem(item.id); }}
                      aria-label={`Supprimer ${item.productName ?? 'cet article'}`}
                    >
                      <Trash2 className="h-3.5 w-3.5" aria-hidden="true" />
                      {removingItemId === item.id ? 'Suppression...' : 'Supprimer'}
                    </Button>
                  </div>
                </motion.div>
              )) : (
                <div className="rounded-2xl border border-dashed border-[color:var(--sf-outline,#d8d0c4)] bg-[color:var(--sf-surface,#ffffff)] p-8 text-center text-[color:var(--sf-text-muted,#6b6560)]">
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
               <Button variant="primary" className="w-full" onClick={() => { window.location.href = boutiqueLink('/checkout'); }}><FontAwesomeIcon icon={appIcons.security} /> Commander</Button>
               <Button variant="secondary" className="w-full" onClick={() => { window.location.href = boutiqueLink('/quote'); }}>Demander un devis</Button>
            </div>
          </Card>
        </div>
      </section>
    </main>
  );
}
