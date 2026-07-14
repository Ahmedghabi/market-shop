import { useId } from 'react';

export function CookieConsentModal({ open, onAccept }: { open: boolean; onAccept: () => void }) {
  const titleId = useId();

  if (!open) return null;

  return (
    <div className="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/55 px-4 py-6 backdrop-blur-sm" role="presentation">
      <section
        className="w-full max-w-md rounded-3xl border border-white/70 bg-white p-6 shadow-2xl sm:p-8"
        role="dialog"
        aria-modal="true"
        aria-labelledby={titleId}
      >
        <div className="mb-5 grid h-12 w-12 place-items-center rounded-2xl bg-[color:var(--ds-primary,#3525cd)] text-white">
          <span className="material-symbols-outlined" aria-hidden="true">shopping_bag</span>
        </div>
        <h2 id={titleId} className="text-2xl font-bold text-slate-950">Autoriser le panier invité</h2>
        <p className="mt-3 text-sm leading-6 text-slate-600">
          Pour conserver votre panier sans créer de compte, Hanooti utilise un cookie fonctionnel lié à cette boutique.
          Vous devez accepter son utilisation avant d&apos;ajouter un article.
        </p>
        <button
          type="button"
          autoFocus
          onClick={onAccept}
          className="mt-6 w-full rounded-2xl bg-[color:var(--ds-primary,#3525cd)] px-5 py-3.5 text-sm font-bold text-white transition hover:opacity-90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[color:var(--ds-primary,#3525cd)]"
        >
          Accepter et ajouter au panier
        </button>
      </section>
    </div>
  );
}
