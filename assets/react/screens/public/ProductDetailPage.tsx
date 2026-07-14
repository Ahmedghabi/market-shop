import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { useEffect, useState } from 'react';
import { AnimatePresence, motion } from 'framer-motion';
import { appIcons } from '../../icons/fontAwesome';
import { Badge, Button, Card } from '../../components/ui';
import { ReviewSection } from '../../components/ReviewSection';
import { authHeaders, boutiqueLink, resolveBoutiqueSlug } from './boutiqueRouting';

type ProductItem = {
  id: string;
  name: string;
  slug: string;
  sellingPrice: number;
  comparePrice?: number;
  currency: string;
  shortDescription: string | null;
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
  const boutiqueSlug = resolveBoutiqueSlug(/^\/boutiques\/([^/]+)\/products\/[^/]+/);
  const productSlug = pathMatch?.[2] ?? window.location.pathname.match(/^\/products\/([^/]+)/)?.[1] ?? '';
  const [product, setProduct] = useState<ProductItem | null>(null);
  const [boutique, setBoutique] = useState<BoutiqueItem | null>(null);
  const [quantity, setQuantity] = useState(1);

  useEffect(() => {
    if (!boutiqueSlug || !productSlug) return;
    const headers = authHeaders();
    fetch(`/api/boutiques/${boutiqueSlug}`, { headers })
      .then((response) => response.ok ? response.json() : null)
      .then(setBoutique)
      .catch(() => {});

    fetch(`/api/products/${productSlug}`, { headers })
      .then((response) => response.ok ? response.json() : null)
      .then((data) => {
        if (data) {
          setProduct({
            ...data,
            shortDescription: data.shortDescription ?? null,
            description: data.description ?? null,
          } as ProductItem);
        }
      })
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

  const discount = product.comparePrice && product.comparePrice > product.sellingPrice
    ? Math.round((1 - product.sellingPrice / product.comparePrice) * 100)
    : 0;

  async function handleAddToCart() {
    if (!boutique) {
      alert('Boutique introuvable.');
      return;
    }

    try {
      await fetch('/api/cart/items', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ productId: product!.id, quantity }),
      });
      window.location.href = boutiqueLink('/cart');
    } catch {
      alert('Erreur lors de l\'ajout au panier.');
    }
  }

  return (
    <main className="ds-shell">
      <section className="ds-page py-8 md:py-12">
        <Card className="overflow-hidden p-0">
          <div className="grid gap-0 lg:grid-cols-2">
            <div className="overflow-hidden bg-[color:var(--ds-surface-container)]">
              {product.images.length > 0 ? (
                <motion.img
                  initial={{ opacity: 0, scale: 1.04 }}
                  animate={{ opacity: 1, scale: 1 }}
                  transition={{ duration: 0.5, ease: [0.16, 1, 0.3, 1] }}
                  whileHover={{ scale: 1.03 }}
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
                <span className="text-4xl font-bold">{(product.sellingPrice / 100).toFixed(2)} {product.currency}</span>
                {product.comparePrice && product.comparePrice > product.sellingPrice && (
                  <span className="text-lg text-[color:var(--ds-on-surface-variant)] line-through">{(product.comparePrice / 100).toFixed(2)} {product.currency}</span>
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
                  <motion.button whileTap={{ scale: 0.9 }} type="button" className="px-4 py-2 text-lg" onClick={() => setQuantity(Math.max(1, quantity - 1))}>-</motion.button>
                  <span className="min-w-[3rem] text-center font-semibold overflow-hidden">
                    <AnimatePresence mode="wait" initial={false}>
                      <motion.span
                        key={quantity}
                        initial={{ y: 8, opacity: 0 }}
                        animate={{ y: 0, opacity: 1 }}
                        exit={{ y: -8, opacity: 0 }}
                        transition={{ duration: 0.15 }}
                        className="inline-block"
                      >
                        {quantity}
                      </motion.span>
                    </AnimatePresence>
                  </span>
                  <motion.button whileTap={{ scale: 0.9 }} type="button" className="px-4 py-2 text-lg" onClick={() => setQuantity(quantity + 1)}>+</motion.button>
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
        <ReviewSection boutiqueSlug={boutiqueSlug} productId={product.id} />
      </section>
    </main>
  );
}
