import { useState } from 'react';
import { ShoppingBag, X, Plus, Minus } from 'lucide-react';
import type { StoreProduct } from './ProductCard';
import { boutiqueLink } from '../boutiqueRouting';

type CartItem = { product: StoreProduct; qty: number };

export function CartSheet({
  items,
  onSetQty,
  onRemove,
}: {
  items: CartItem[];
  onSetQty: (id: string, qty: number) => void;
  onRemove: (id: string) => void;
}) {
  const [open, setOpen] = useState(false);
  const count = items.reduce((s, i) => s + i.qty, 0);
  const total = items.reduce((s, i) => s + i.qty * i.product.priceCents, 0);

  return (
    <>
      <button onClick={() => setOpen(true)} className="relative flex h-9 w-9 items-center justify-center rounded-full text-[color:var(--ds-on-surface-variant)] hover:bg-[color:var(--ds-surface-container)] hover:text-[color:var(--ds-on-surface)] transition-colors" aria-label="Panier">
        <ShoppingBag className="h-5 w-5" />
        {count > 0 && (
          <span className="absolute -right-0.5 -top-0.5 grid h-4 w-4 place-items-center rounded-full bg-[color:var(--ds-primary)] text-[10px] font-bold text-white">
            {count}
          </span>
        )}
      </button>

      {open && (
        <>
          <div className="fixed inset-0 z-40 bg-black/20 backdrop-blur-sm" onClick={() => setOpen(false)} />
          <div className="fixed inset-y-0 right-0 z-50 flex w-full max-w-md flex-col border-l border-[color:var(--ds-outline-variant)] bg-[color:var(--ds-surface)] shadow-2xl">
            <div className="flex items-center justify-between border-b border-[color:var(--ds-outline-variant)] px-5 py-4">
              <h2 className="text-lg font-bold text-[color:var(--ds-on-surface)]">Votre panier ({count})</h2>
              <button onClick={() => setOpen(false)} className="rounded-lg p-1.5 text-[color:var(--ds-on-surface-variant)] hover:bg-[color:var(--ds-surface-container)]">
                <X className="h-5 w-5" />
              </button>
            </div>

            <div className="flex-1 overflow-y-auto px-5 py-4 space-y-4">
              {items.length === 0 && (
                <p className="py-12 text-center text-sm text-[color:var(--ds-on-surface-variant)]">Votre panier est vide.</p>
              )}
              {items.map((i) => (
                <div key={i.product.id} className="flex gap-3 border-b border-[color:var(--ds-outline-variant)] pb-4">
                  <div className="h-20 w-20 shrink-0 overflow-hidden rounded-lg bg-[color:var(--ds-surface-container)]">
                    {(() => {
                      const imgUrl = Array.isArray(i.product.images)
                        ? typeof i.product.images[0] === 'string'
                          ? i.product.images[0]
                          : (i.product.images[0] as any)?.url ?? ''
                        : '';
                      return imgUrl ? (
                        <img src={imgUrl} alt={i.product.name} className="h-full w-full object-cover" />
                      ) : (
                        <div className="flex h-full items-center justify-center text-xs text-[color:var(--ds-on-surface-variant)]">N/A</div>
                      );
                    })()}
                  </div>
                  <div className="flex-1 min-w-0">
                    <div className="truncate text-sm font-medium text-[color:var(--ds-on-surface)]">{i.product.name}</div>
                    <div className="mt-0.5 text-xs text-[color:var(--ds-on-surface-variant)]">{(i.product.priceCents / 100).toFixed(2)} {i.product.currency}</div>
                    <div className="mt-2 flex items-center gap-1">
                      <button onClick={() => onSetQty(i.product.id, i.qty - 1)} className="grid h-7 w-7 place-items-center rounded-full border border-[color:var(--ds-outline-variant)] text-[color:var(--ds-on-surface)] hover:bg-[color:var(--ds-surface-container)] transition-colors">
                        <Minus className="h-3 w-3" />
                      </button>
                      <span className="w-8 text-center text-sm font-medium text-[color:var(--ds-on-surface)]">{i.qty}</span>
                      <button onClick={() => onSetQty(i.product.id, i.qty + 1)} className="grid h-7 w-7 place-items-center rounded-full border border-[color:var(--ds-outline-variant)] text-[color:var(--ds-on-surface)] hover:bg-[color:var(--ds-surface-container)] transition-colors">
                        <Plus className="h-3 w-3" />
                      </button>
                      <button onClick={() => onRemove(i.product.id)} className="ml-auto text-xs text-[color:var(--ds-on-surface-variant)] hover:text-red-600">Retirer</button>
                    </div>
                  </div>
                  <div className="text-sm font-bold text-[color:var(--ds-on-surface)]">{(i.qty * i.product.priceCents / 100).toFixed(0)} {i.product.currency}</div>
                </div>
              ))}
            </div>

            <div className="border-t border-[color:var(--ds-outline-variant)] px-5 py-4 space-y-3">
              <div className="flex items-center justify-between text-sm">
                <span className="text-[color:var(--ds-on-surface-variant)]">Sous-total</span>
                <span className="font-bold text-[color:var(--ds-on-surface)]">{(total / 100).toFixed(2)} {items[0]?.product.currency || 'TND'}</span>
              </div>
              <a
                href={boutiqueLink('/cart')}
                className={`flex w-full items-center justify-center rounded-xl px-4 py-3 text-sm font-bold text-white transition-colors ${
                  items.length === 0 ? 'pointer-events-none bg-gray-300' : 'bg-[color:var(--ds-primary)] hover:bg-[color:var(--ds-primary)]/90'
                }`}
              >
                Passer commande
              </a>
            </div>
          </div>
        </>
      )}
    </>
  );
}
