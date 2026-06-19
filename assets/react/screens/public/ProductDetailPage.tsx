import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { useEffect, useState } from 'react';
import { appIcons } from '../../icons/fontAwesome';
import { Badge, Button, Card } from '../../components/ui';
import { ReviewSection } from '../../components/ReviewSection';

type ProductItem = {
  id: string;
  name: string;
  slug: string;
  priceCents: number;
  compareAtPriceCents?: number;
  currency: string;
  description: string | null;
  images: Array<{ url: string; smallUrl?: string; largeUrl?: string; alt: string | null }>;
  stockQuantity: number;
  lowStockThreshold: number;
  categoryId: string | null;
  categoryName: string | null;
};

type BoutiqueItem = {
  id: string;
  name: string;
  slug: string;
  primaryColor: string;
};

export function ProductDetailPage({ title }: { title: string }) {
  const pathMatch = window.location.pathname.match(/^\/boutiques\/([^/]+)\/products\/([^/]+)/);
  const boutiqueSlug = pathMatch?.[1] ?? '';
  const productSlug = pathMatch?.[2] ?? '';
  const [product, setProduct] = useState<ProductItem | null>(null);
  const [boutique, setBoutique] = useState<BoutiqueItem | null>(null);
  const [quantity, setQuantity] = useState(1);

  useEffect(() => {
    if (!boutiqueSlug || !productSlug) return;
    fetch(`/api/boutiques/${boutiqueSlug}`)
      .then((response) => response.ok ? response.json() : null)
      .then(setBoutique)
      .catch(() => {});

    fetch(`/api/boutiques/${boutiqueSlug}/products/${productSlug}`)
      .then((response) => response.ok ? response.json() : null)
      .then(setProduct)
      .catch(() => {});
  }, [boutiqueSlug, productSlug]);

  if (!product) {
    return (
      <main className="ds-shell">
        <section className="ds-page py-8 md:py-12">
          <Card className="text-center py-12">
            <FontAwesomeIcon icon={appIcons.products} size="2x" />
            <h2 className="mt-4 text-xl font-bold">Chargement...</h2>
          </Card>
        </section>
      </main>
    );
  }

  const discount = product.compareAtPriceCents && product.compareAtPriceCents > product.priceCents
    ? Math.round((1 - product.priceCents / product.compareAtPriceCents) * 100)
    : 0;

  async function handleAddToCart() {
    if (!boutique) {
      alert('Boutique introuvable.');
      return;
    }

    try {
      await fetch(`/api/boutiques/${boutique.id}/cart/items`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ productId: product!.id, quantity }),
      });
      window.location.href = `/boutiques/${boutiqueSlug}/cart`;
    } catch {
      alert('Erreur lors de l\'ajout au panier.');
    }
  }

  return (
    <main className="ds-shell">
      <section className="ds-page py-8 md:py-12">
        <Card className="overflow-hidden p-0">
          <div className="grid gap-0 lg:grid-cols-2">
            <div className="bg-[color:var(--ds-surface-container)]">
              {product.images.length > 0 ? (
                <img
                  src={product.images[0].largeUrl ?? product.images[0].url}
                  alt={product.images[0].alt ?? product.name}
                  className="h-full w-full object-cover max-h-[500px]"
                />
              ) : (
                <div className="flex h-full min-h-[300px] items-center justify-center text-[color:var(--ds-on-surface-variant)]">
                  <FontAwesomeIcon icon={appIcons.products} size="3x" />
                </div>
              )}
            </div>
            <div className="p-8">
              <div className="flex items-start justify-between gap-4">
                <div>
                  {product.categoryName && (
                    <p className="text-xs font-bold uppercase tracking-[0.15em] text-[color:var(--ds-on-surface-variant)]">{product.categoryName}</p>
                  )}
                  <h1 className="mt-2 text-3xl font-bold">{product.name}</h1>
                </div>
                {discount > 0 && <Badge tone="success">-{discount}%</Badge>}
              </div>

              <div className="mt-6 flex items-baseline gap-3">
                <span className="text-4xl font-bold">{(product.priceCents / 100).toFixed(2)} {product.currency}</span>
                {product.compareAtPriceCents && product.compareAtPriceCents > product.priceCents && (
                  <span className="text-lg text-[color:var(--ds-on-surface-variant)] line-through">{(product.compareAtPriceCents / 100).toFixed(2)} {product.currency}</span>
                )}
              </div>

              <div className="mt-4 flex items-center gap-3">
                <Badge tone={product.stockQuantity > 0 ? 'success' : 'error'}>
                  {product.stockQuantity > 0 ? `En stock (${product.stockQuantity})` : 'Rupture de stock'}
                </Badge>
                {product.lowStockThreshold > 0 && product.stockQuantity > 0 && product.stockQuantity <= product.lowStockThreshold && (
                  <Badge tone="warning">Stock bas</Badge>
                )}
              </div>

              {product.description && (
                <p className="mt-6 text-[color:var(--ds-on-surface-variant)] leading-relaxed">{product.description}</p>
              )}

              <div className="mt-8 flex items-center gap-4">
                <div className="flex items-center rounded-xl border border-[color:var(--ds-outline-variant)]">
                  <button type="button" className="px-4 py-2 text-lg" onClick={() => setQuantity(Math.max(1, quantity - 1))}>-</button>
                  <span className="min-w-[3rem] text-center font-semibold">{quantity}</span>
                  <button type="button" className="px-4 py-2 text-lg" onClick={() => setQuantity(quantity + 1)}>+</button>
                </div>
                <Button variant="primary" className="flex-1" onClick={handleAddToCart}>
                  <FontAwesomeIcon icon={appIcons.products} /> Ajouter au panier
                </Button>
              </div>

              <div className="mt-6 flex gap-2">
                <Badge tone="neutral">Paiement sécurisé</Badge>
                <Badge tone="neutral">Livraison rapide</Badge>
              </div>
            </div>
          </div>
        </Card>
      </section>

      <section className="ds-page pb-16">
        <ReviewSection boutiqueSlug={boutiqueSlug} productSlug={productSlug} />
      </section>
    </main>
  );
}
