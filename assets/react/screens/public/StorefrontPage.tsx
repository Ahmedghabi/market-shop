import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { useEffect, useState, type CSSProperties } from 'react';
import { appIcons } from '../../icons/fontAwesome';
import { Badge, Button, Card } from '../../components/ui';
import { ReviewSection } from '../../components/ReviewSection';
import { ChatBox } from '../../chat/ChatBox';

type BoutiqueItem = {
  id: string;
  name: string;
  slug: string;
  status: string;
  primaryColor: string;
  secondaryColor: string;
  logoUrl: string | null;
  contactEmail: string | null;
  contactPhone: string | null;
  address: string | null;
  productsCount: number;
};

type ProductItem = {
  id: string;
  name: string;
  slug: string;
  priceCents: number;
  currency: string;
  description: string | null;
  images: Array<{ url: string; alt: string | null }>;
};

export function StorefrontPage({ title, description }: { title: string; description: string }) {
  const boutiqueSlug = window.location.pathname.match(/^\/boutiques\/([^/]+)/)?.[1] ?? '';
  const [boutique, setBoutique] = useState<BoutiqueItem | null>(null);
  const [products, setProducts] = useState<ProductItem[]>([]);

  useEffect(() => {
    if (!boutiqueSlug) return;
    fetch(`/api/boutiques/${boutiqueSlug}`)
      .then((response) => response.ok ? response.json() : null)
      .then(setBoutique)
      .catch(() => {});

    fetch(`/api/boutiques/${boutiqueSlug}/products`)
      .then((response) => response.ok ? response.json() : [])
      .then((data) => {
        const items = (data as { member?: ProductItem[] }).member ?? data;
        setProducts(Array.isArray(items) ? items : []);
      })
      .catch(() => {});
  }, [boutiqueSlug]);

  const accent = boutique?.primaryColor ?? '#3525cd';
  const name = boutique?.name ?? 'Boutique';

  return (
    <main className="ds-shell" style={{ '--boutique-primary': accent, '--boutique-secondary': boutique?.secondaryColor ?? '#505f76' } as CSSProperties}>
      <section className="ds-page py-8 md:py-12">
        <Card className="overflow-hidden p-0">
          <div className="grid gap-0 lg:grid-cols-[1.1fr_0.9fr]">
            <div className="p-8 text-white md:p-12" style={{ background: `linear-gradient(135deg, ${accent}, ${accent}dd)` }}>
              <Badge tone="neutral" className="bg-white/15 text-white">{name}</Badge>
              <h1 className="mt-4 text-4xl font-bold tracking-[-0.04em] md:text-6xl">{title}</h1>
              <p className="mt-4 max-w-2xl text-white/80">{description}</p>
              <div className="mt-8 flex flex-wrap gap-3">
                <Button variant="secondary">Voir les produits</Button>
                <Button variant="ghost" className="border-white/20 text-white">Demander un devis</Button>
              </div>
              {boutique?.contactEmail && (
                <p className="mt-4 text-sm text-white/70">Contact : {boutique.contactEmail}</p>
              )}
            </div>
            <div className="grid gap-4 bg-[color:var(--ds-surface-container-low)] p-6 md:p-8">
              <Card className="bg-white">
                <p className="text-xs font-bold uppercase tracking-[0.15em] text-[color:var(--ds-on-surface-variant)]">Navigation</p>
                <div className="mt-3 grid grid-cols-2 gap-3 text-sm font-semibold">
                  <span>Accueil personnalisé</span><span>Produits ({products.length})</span><span>Offres</span><span>Avis</span>
                </div>
              </Card>
              <Card className="bg-white">
                <p className="text-xs font-bold uppercase tracking-[0.15em] text-[color:var(--ds-on-surface-variant)]">Mise en avant</p>
                <div className="mt-3 flex items-center gap-4">
                  {products.length > 0 ? (
                    <>
                      <img className="h-20 w-20 rounded-2xl object-cover" alt={products[0].name} src={products[0].images?.[0]?.url ?? 'https://via.placeholder.com/80'} />
                      <div>
                        <strong>{products[0].name}</strong>
                        <p className="text-sm text-[color:var(--ds-on-surface-variant)]">{products[0].priceCents / 100} {products[0].currency}</p>
                      </div>
                    </>
                  ) : (
                    <p className="text-sm text-[color:var(--ds-on-surface-variant)]">Aucun produit à la une</p>
                  )}
                </div>
              </Card>
              <Card className="bg-white">
                <p className="text-xs font-bold uppercase tracking-[0.15em] text-[color:var(--ds-on-surface-variant)]">Actions rapides</p>
                <div className="mt-3 flex flex-wrap gap-3">
                  <Button variant="primary"><FontAwesomeIcon icon={appIcons.products} /> Acheter</Button>
                  <Button variant="secondary">Contacter la boutique</Button>
                </div>
              </Card>
            </div>
          </div>
        </Card>
      </section>

      <section className="ds-page pb-16">
        <ReviewSection boutiqueSlug={boutiqueSlug} />
      </section>
      {boutique && localStorage.getItem(`hanooty_chat_enabled_${boutique.slug}`) !== 'false' && localStorage.getItem('hanooty_boutique_chat_enabled') !== 'false' && (
        <ChatBox boutiqueId={boutique.id} apiBaseUrl="/api" />
      )}
    </main>
  );
}
