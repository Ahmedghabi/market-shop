import { ShoppingCart } from 'lucide-react';
import { boutiqueLink } from '../boutiqueRouting';
import { ImageWithFallback } from '../../../components/ImageWithFallback';

export type StoreProduct = {
  id: string;
  name: string;
  slug: string;
  priceCents: number;
  comparePriceCents?: number | null;
  currency: string;
  description?: string | null;
  images?: Array<{ url: string; alt?: string | null }> | string[];
  categoryName?: string | null;
  categorySlug?: string | null;
  categoryId?: string | null;
  categoryIds?: string[];
  brandName?: string | null;
  filterValues?: Array<{ filterId: string; filterName: string; filterSlug: string; value: string }>;
  stockQuantity?: number;
  badge?: string | null;
  rating?: number;
  reviewsCount?: number;
  createdAt?: string | null;
};

export function getProductImageUrl(product: StoreProduct): string {
  if (!Array.isArray(product.images) || !product.images[0]) return '';

  const [firstImage] = product.images;

  return typeof firstImage === 'string' ? firstImage : firstImage.url;
}

export function ProductCard({
  product,
  onAddToCart,
}: {
  product: StoreProduct;
  onAddToCart: (p: StoreProduct) => void;
}) {
  const imgUrl = getProductImageUrl(product);
  const price = (product.priceCents / 100).toFixed(2);
  const oldPrice = product.comparePriceCents ? (product.comparePriceCents / 100).toFixed(2) : null;
  const isLowStock = product.stockQuantity !== undefined && product.stockQuantity > 0 && product.stockQuantity <= 5;

  return (
    <div className="group">
      <div className="relative aspect-square overflow-hidden rounded-xl bg-[color:var(--ds-surface-container)]">
        <a href={boutiqueLink(`/products/${product.slug}`)} className="block h-full">
          {imgUrl ? (
            <ImageWithFallback src={imgUrl} alt={product.name} className="h-full w-full object-cover transition duration-500 group-hover:scale-105" />
          ) : (
            <div className="flex h-full items-center justify-center text-[color:var(--ds-on-surface-variant)] text-sm">Pas d'image</div>
          )}
          {product.badge && (
            <span className={`absolute left-3 top-3 rounded-full px-2.5 py-0.5 text-xs font-bold text-white ${
              product.badge === 'Promo' ? 'bg-red-600' :
              product.badge === 'Nouveau' ? 'bg-[color:var(--ds-primary)]' :
              'bg-gray-900'
            }`}>
              {product.badge}
            </span>
          )}
          {isLowStock && (
            <span className="absolute right-3 top-3 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800">
              Plus que {product.stockQuantity}
            </span>
          )}
        </a>
        <button
          type="button"
          aria-label={`Ajouter ${product.name} au panier`}
          onClick={(e) => { e.preventDefault(); onAddToCart(product); }}
          className="absolute bottom-3 left-3 right-3 rounded-lg bg-[color:var(--ds-on-surface)] px-3 py-2 text-sm font-medium text-[color:var(--ds-surface)] opacity-100 transition hover:opacity-90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[color:var(--ds-primary)] flex items-center justify-center gap-2"
        >
          <ShoppingCart className="h-4 w-4" />
          Ajouter au panier
        </button>
      </div>
      <div className="mt-3 space-y-1">
        <div className="text-xs text-[color:var(--ds-on-surface-variant)]">{product.categoryName || 'Catégorie'}</div>
        <a
          href={boutiqueLink(`/products/${product.slug}`)}
          className="line-clamp-1 text-sm font-medium text-[color:var(--ds-on-surface)] hover:text-[color:var(--ds-primary)]"
        >
          {product.name}
        </a>
        <div className="flex items-center gap-2">
          <span className="text-sm font-bold text-[color:var(--ds-on-surface)]">{price} {product.currency}</span>
          {oldPrice && <span className="text-xs text-[color:var(--ds-on-surface-variant)] line-through">{oldPrice} {product.currency}</span>}
          {product.rating && (
            <span className="ml-auto text-xs text-[color:var(--ds-on-surface-variant)]">★ {product.rating}</span>
          )}
        </div>
      </div>
    </div>
  );
}
