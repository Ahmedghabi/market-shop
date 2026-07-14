import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { useEffect, useState } from 'react';
import { AnimatePresence, motion } from 'framer-motion';
import { appIcons } from '../../icons/fontAwesome';
import { Badge, Button, Card } from '../../components/ui';
import { CookieConsentModal } from '../../components/CookieConsentModal';
import { ImageWithFallback } from '../../components/ImageWithFallback';
import { ReviewSection } from '../../components/ReviewSection';
import { authHeaders, boutiqueLink, boutiqueQuery, resolveBoutiqueSlug } from './boutiqueRouting';
import { useCartAdd } from './storefront/useCartAdd';
import { CartSheet, type CartItem as CartSheetItem } from './storefront/CartSheet';
import { StorefrontHeader } from './storefront/StorefrontHeader';
import type { StoreProduct } from './storefront/ProductCard';
import type { StoreBoutique } from './storefront/StorefrontTheme';
import { applyStorefrontTheme, resetStorefrontTheme, type StorefrontThemeData } from '../../theme/storefrontThemeRoot';

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

type BoutiqueItem = StorefrontThemeData & {
  id: string;
  name: string;
  slug: string;
  primaryColor: string;
  reviewsEnabled?: boolean;
  coverImage?: string | null;
};

type CartOutput = {
  currency: string;
  items: Array<{
    id: string;
    productId: string | null;
    productName: string | null;
    quantity: number;
    unitPriceCents: number;
  }>;
};

export function ProductDetailPage({ title }: { title: string }) {
  const pathMatch = window.location.pathname.match(/^\/boutiques\/([^/]+)\/(?:produit|products)\/([^/]+)/);
  const boutiqueSlug = resolveBoutiqueSlug(/^\/boutiques\/([^/]+)\/(?:produit|products)\/[^/]+/);
  const productSlug = pathMatch?.[2] ?? window.location.pathname.match(/^\/(?:produit|products)\/([^/]+)/)?.[1] ?? '';
  window.__boutiqueSlug__ = boutiqueSlug;
  const [product, setProduct] = useState<ProductItem | null>(null);
  const [boutique, setBoutique] = useState<BoutiqueItem | null>(null);
  const [cartItems, setCartItems] = useState<CartSheetItem[]>([]);
  const [quantity, setQuantity] = useState(1);
  const { add: addToCart, consentOpen, acceptConsent, error: cartError } = useCartAdd({
    boutiqueSlug,
    onAdded: () => { window.location.href = boutiqueLink('/cart'); },
  });

  async function refreshCart(): Promise<void> {
    const response = await fetch(`/api/cart${boutiqueQuery(boutiqueSlug)}`, { headers: authHeaders() });
    if (!response.ok) return;

    const payload = await response.json() as CartOutput;
    setCartItems(payload.items
      .filter((item) => item.productId !== null)
      .map((item) => ({
        product: {
          id: item.productId as string,
          name: item.productName ?? 'Produit',
          slug: '',
          priceCents: item.unitPriceCents,
          currency: payload.currency,
          images: [],
        },
        qty: item.quantity,
      })));
  }

  async function setCartQuantity(itemId: string, nextQuantity: number): Promise<void> {
    if (nextQuantity < 1) return;
    const response = await fetch(`/api/cart/items/${itemId}${boutiqueQuery(boutiqueSlug)}`, {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/merge-patch+json', ...(authHeaders() ?? {}) },
      body: JSON.stringify({ quantity: nextQuantity }),
    });
    if (response.ok) await refreshCart();
  }

  async function removeCartItem(itemId: string): Promise<void> {
    const response = await fetch(`/api/cart/items/${itemId}${boutiqueQuery(boutiqueSlug)}`, {
      method: 'DELETE',
      headers: authHeaders(),
    });
    if (response.ok) await refreshCart();
  }

  useEffect(() => {
    if (!boutiqueSlug || !productSlug) return;
    const headers = authHeaders();
      void refreshCart();
      fetch(`/api/boutiques/${boutiqueSlug}`, { headers })
      .then((response) => response.ok ? response.json() : null)
      .then((data) => {
        if (!data) return;

        applyStorefrontTheme(data);
        setBoutique({ ...data, reviewsEnabled: data.reviewsEnabled === true });
      })
      .catch(() => {});

     fetch(`/api/products/${productSlug}${boutiqueQuery(boutiqueSlug)}`, { headers })
      .then((response) => response.ok ? response.json() : null)
      .then((data) => {
        if (data) {
          setProduct({
            ...data,
            shortDescription: data.shortDescription ?? null,
            description: data.description ?? null,
          } as ProductItem);
          fetch(`/api/products/${data.id}/view?boutiqueSlug=${encodeURIComponent(boutiqueSlug)}`, { method: 'POST' }).catch(() => {});
        }
      })
      .catch(() => {});
    return resetStorefrontTheme;
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

  function handleAddToCart(): void {
    if (!product || !boutique) return;
    const storeProduct: StoreProduct = {
      id: product.id,
      name: product.name,
      slug: product.slug,
      priceCents: product.sellingPrice,
      currency: product.currency,
      images: product.images.map((image) => ({ url: image.largeUrl ?? image.url, alt: image.alt })),
    };
    addToCart(storeProduct, quantity);
  }

  return (
    <main className="ds-shell">
      {boutique && (
        <StorefrontHeader
          boutique={boutique as StoreBoutique}
          cartItems={cartItems}
          onSetCartQty={(id, qty) => { void setCartQuantity(id, qty); }}
          onRemoveCartItem={(id) => { void removeCartItem(id); }}
        />
      )}
      <CookieConsentModal open={consentOpen} onAccept={acceptConsent} />
      {cartError && <div role="alert" className="fixed bottom-5 left-1/2 z-[90] -translate-x-1/2 rounded-full bg-red-600 px-5 py-3 text-sm font-semibold text-white shadow-xl">{cartError}</div>}
      <section className="ds-page py-8 md:py-12">
        <Card className="overflow-hidden p-0">
          <div className="grid gap-0 lg:grid-cols-2">
            <div className="overflow-hidden bg-[color:var(--ds-surface-container)]">
              {product.images.length > 0 ? (
                  <ImageWithFallback
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

      {boutique?.reviewsEnabled === true && (
        <section className="ds-page pb-16">
          <ReviewSection boutiqueSlug={boutiqueSlug} productId={product.id} />
        </section>
      )}
    </main>
  );
}
