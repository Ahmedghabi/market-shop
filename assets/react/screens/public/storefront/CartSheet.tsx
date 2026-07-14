import { useEffect, useId, useRef, useState } from 'react';
import { AnimatePresence, motion } from 'framer-motion';
import { ShoppingBag, X, Plus, Minus } from 'lucide-react';
import { getProductImageUrl, type StoreProduct } from './ProductCard';
import { boutiqueLink } from '../boutiqueRouting';
import { ImageWithFallback } from '../../../components/ImageWithFallback';

export type CartItem = { product: StoreProduct; qty: number };

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
  const closeButtonRef = useRef<HTMLButtonElement>(null);
  const titleId = useId();
  const count = items.reduce((s, i) => s + i.qty, 0);
  const total = items.reduce((s, i) => s + i.qty * i.product.priceCents, 0);

  useEffect(() => {
    if (!open) return;
    closeButtonRef.current?.focus();
    const onKeyDown = (event: KeyboardEvent) => {
      if ('Escape' === event.key) setOpen(false);
    };
    document.addEventListener('keydown', onKeyDown);

    return () => document.removeEventListener('keydown', onKeyDown);
  }, [open]);

  return (
    <>
      <button type="button" onClick={() => setOpen(true)} className="relative flex h-11 w-11 shrink-0 cursor-pointer items-center justify-center rounded-full border border-[color:var(--sf-outline,var(--ds-outline-variant))] p-0 text-[color:var(--sf-text-muted,var(--ds-on-surface-variant))] transition-colors hover:bg-[color:var(--sf-surface-muted,var(--ds-surface-container))] hover:text-[color:var(--sf-text,var(--ds-on-surface))] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[color:var(--sf-accent,var(--ds-primary,#111111))]" aria-label="Ouvrir le panier" title="Panier">
        <ShoppingBag className="h-5 w-5" strokeWidth={2.1} aria-hidden="true" />
        {count > 0 && (
          <motion.span
            key={count}
            initial={{ scale: 0.5 }}
            animate={{ scale: 1 }}
            transition={{ type: 'spring', stiffness: 500, damping: 20 }}
            className="absolute -right-1 -top-1 grid h-5 w-5 place-items-center rounded-full bg-[color:var(--sf-accent,var(--ds-primary,#111111))] text-[10px] font-bold text-white ring-2 ring-[color:var(--sf-surface,var(--ds-surface))]"
          >
            {count}
          </motion.span>
        )}
      </button>

      <AnimatePresence>
      {open && (
        <>
           <motion.div
             className="fixed inset-0 z-40 cursor-pointer bg-slate-950/75 backdrop-blur-[3px]"
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            onClick={() => setOpen(false)}
          />
           <motion.div
             className="fixed inset-y-0 right-0 z-50 flex h-[100dvh] min-h-screen w-full max-w-[36rem] flex-col overflow-hidden border-l border-[color:var(--sf-outline,var(--ds-outline-variant))] bg-[color:var(--sf-surface,var(--ds-surface))] text-[color:var(--sf-text,var(--ds-on-surface))] shadow-2xl will-change-transform"
            role="dialog"
            aria-modal="true"
            aria-labelledby={titleId}
            initial={{ x: '100%' }}
            animate={{ x: 0 }}
            exit={{ x: '100%' }}
            transition={{ type: 'spring', stiffness: 340, damping: 32 }}
          >
             <div className="flex items-center justify-between border-b border-[color:var(--sf-outline,var(--ds-outline-variant))] px-6 py-5">
                <h2 id={titleId} className="text-lg font-bold text-[color:var(--sf-text,var(--ds-on-surface))]">Votre panier ({count})</h2>
                <button type="button" ref={closeButtonRef} onClick={() => setOpen(false)} className="cursor-pointer rounded-lg p-2 text-[color:var(--sf-text-muted,var(--ds-on-surface-variant))] transition-colors hover:bg-[color:var(--sf-surface-muted,var(--ds-surface-container))] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[color:var(--sf-accent,var(--ds-primary))]" aria-label="Fermer le panier">
                 <X className="h-5 w-5" aria-hidden="true" />
              </button>
            </div>

             <div className="flex-1 space-y-4 overflow-y-auto overscroll-contain px-6 py-5">
               {items.length === 0 && (
                 <p className="py-16 text-center text-sm text-[color:var(--sf-text-muted,var(--ds-on-surface-variant))]">Votre panier est vide.</p>
              )}
              <AnimatePresence initial={false}>
              {items.map((i) => (
                <motion.div
                  key={i.product.id}
                  layout
                  initial={{ opacity: 0, x: 24 }}
                  animate={{ opacity: 1, x: 0 }}
                  exit={{ opacity: 0, x: 24, height: 0, marginBottom: 0, paddingBottom: 0 }}
                   className="flex gap-3 border-b border-[color:var(--sf-outline,var(--ds-outline-variant))] pb-5"
                >
                   <div className="h-24 w-24 shrink-0 overflow-hidden rounded-xl bg-[color:var(--sf-surface-muted,var(--ds-surface-container))]">
                    {(() => {
                      const imgUrl = getProductImageUrl(i.product);
                      return imgUrl ? (
                        <ImageWithFallback src={imgUrl} alt={i.product.name} className="h-full w-full object-cover" />
                      ) : (
                         <div className="flex h-full items-center justify-center text-xs text-[color:var(--sf-text-muted,var(--ds-on-surface-variant))]">N/A</div>
                      );
                    })()}
                  </div>
                  <div className="flex-1 min-w-0">
                     <div className="truncate text-sm font-medium text-[color:var(--sf-text,var(--ds-on-surface))]">{i.product.name}</div>
                     <div className="mt-0.5 text-xs text-[color:var(--sf-text-muted,var(--ds-on-surface-variant))]">{(i.product.priceCents / 100).toFixed(2)} {i.product.currency}</div>
                     <div className="mt-2 flex items-center gap-1">
                        <button type="button" aria-label={`Diminuer la quantité de ${i.product.name}`} onClick={() => onSetQty(i.product.id, i.qty - 1)} className="grid h-8 w-8 cursor-pointer place-items-center rounded-full border border-[color:var(--sf-outline,var(--ds-outline-variant))] text-[color:var(--sf-text,var(--ds-on-surface))] transition-colors hover:bg-[color:var(--sf-surface-muted,var(--ds-surface-container))] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[color:var(--sf-accent,var(--ds-primary))]">
                         <Minus className="h-3.5 w-3.5" aria-hidden="true" />
                       </button>
                       <span className="w-8 text-center text-sm font-medium text-[color:var(--sf-text,var(--ds-on-surface))]">{i.qty}</span>
                        <button type="button" aria-label={`Augmenter la quantité de ${i.product.name}`} onClick={() => onSetQty(i.product.id, i.qty + 1)} className="grid h-8 w-8 cursor-pointer place-items-center rounded-full border border-[color:var(--sf-outline,var(--ds-outline-variant))] text-[color:var(--sf-text,var(--ds-on-surface))] transition-colors hover:bg-[color:var(--sf-surface-muted,var(--ds-surface-container))] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[color:var(--sf-accent,var(--ds-primary))]">
                         <Plus className="h-3.5 w-3.5" aria-hidden="true" />
                       </button>
                       <button type="button" onClick={() => onRemove(i.product.id)} className="ml-auto cursor-pointer text-xs text-[color:var(--sf-text-muted,var(--ds-on-surface-variant))] transition-colors hover:text-red-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500">Retirer</button>
                    </div>
                  </div>
                   <div className="text-sm font-bold text-[color:var(--sf-text,var(--ds-on-surface))]">{(i.qty * i.product.priceCents / 100).toFixed(0)} {i.product.currency}</div>
                </motion.div>
              ))}
              </AnimatePresence>
            </div>

             <div className="space-y-3 border-t border-[color:var(--sf-outline,var(--ds-outline-variant))] px-6 py-5">
               <div className="flex items-center justify-between text-sm">
                 <span className="text-[color:var(--sf-text-muted,var(--ds-on-surface-variant))]">Sous-total</span>
                 <span className="font-bold text-[color:var(--sf-text,var(--ds-on-surface))]">{(total / 100).toFixed(2)} {items[0]?.product.currency || 'TND'}</span>
              </div>
              <a
                href={boutiqueLink('/cart')}
                 className={`flex w-full items-center justify-center rounded-xl px-4 py-3 text-sm font-bold text-white transition-colors ${
                    items.length === 0 ? 'pointer-events-none bg-gray-300' : 'bg-[color:var(--sf-accent,var(--ds-primary,#111111))] hover:opacity-90'
                }`}
              >
                Passer commande
              </a>
            </div>
          </motion.div>
        </>
      )}
      </AnimatePresence>
    </>
  );
}
