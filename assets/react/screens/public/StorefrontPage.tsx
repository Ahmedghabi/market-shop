import { useEffect, useState } from 'react';
import { ChatBox } from '../../chat/ChatBox';
import { StorefrontTheme, type StoreBoutique, type StoreProduct } from './storefront/StorefrontTheme';
import { applyBoutiqueTheme, applyStorefrontCssVars } from '../../theme/boutiqueTheme';
import { authHeaders, resolveBoutiqueSlug } from './boutiqueRouting';

function applyThemeToRoot(data: any): void {
  if (!data?.colorPalette) return;
  applyBoutiqueTheme({
    boutiqueId: data.id ?? '',
    name: data.name ?? '',
    logoUrl: data.logoUrl ?? undefined,
    colorPalette: data.colorPalette,
    iconSet: data.iconSet ?? {},
    featuredCategories: data.featuredCategories ?? [],
    frontOfficePages: data.frontOfficePages ?? [],
    navigationItems: data.navigationItems ?? [],
  });
  applyStorefrontCssVars(data.colorPalette);
  const root = document.documentElement;
  if (data.theme) root.dataset.storefrontTheme = data.theme;
  if (data.fontFamily) root.style.setProperty('--ds-font-family', data.fontFamily);
  if (data.fontSize) root.style.setProperty('--ds-font-size', data.fontSize);
  if (data.borderRadius) {
    root.style.setProperty('--ds-radius', data.borderRadius);
    root.style.setProperty('--ds-radius-sm', `calc(${data.borderRadius} / 2)`);
    root.style.setProperty('--ds-radius-lg', `calc(${data.borderRadius} * 1.5)`);
  }
}

export function StorefrontPage({ title, description }: { title: string; description: string }) {
  const boutiqueSlug = resolveBoutiqueSlug(/^\/boutiques\/([^/]+)/);
  const [boutique, setBoutique] = useState<StoreBoutique | null>(null);
  const [products, setProducts] = useState<StoreProduct[]>([]);
  const [loaded, setLoaded] = useState(false);

  useEffect(() => {
    if (!boutiqueSlug) return;
    setLoaded(false);
    const headers = authHeaders();

    fetch(`/api/boutiques/${boutiqueSlug}`, { headers })
      .then((r) => r.ok ? r.json() : null)
      .then((data) => {
        if (!data) return;
        applyThemeToRoot(data);
        setBoutique({
          id: data.id,
          name: data.name,
          slug: data.slug,
          logoUrl: data.logoUrl ?? null,
          description: data.description ?? `${title} — ${description}`,
          email: data.contactEmail ?? null,
          address: data.address ?? null,
          primaryColor: data.primaryColor ?? undefined,
          heroTitle: title,
          heroSubtitle: description,
        });
      })
      .catch(() => {})
      .finally(() => setLoaded(true));

    fetch('/api/products', { headers })
      .then((r) => r.ok ? r.json() : [])
      .then((data) => {
        const items = (data as { member?: any[] }).member ?? data;
        setProducts(Array.isArray(items) ? items.map(mapProduct) : []);
      })
      .catch(() => {});
  }, [boutiqueSlug]);

  if (!boutique && !loaded) {
    return (
      <div className="flex min-h-screen items-center justify-center bg-[color:var(--ds-surface)]">
        <div className="h-8 w-8 animate-spin rounded-full border-2 border-[color:var(--ds-primary)] border-t-transparent" />
      </div>
    );
  }

  if (!boutique) {
    return (
      <div className="flex min-h-screen items-center justify-center bg-[color:var(--ds-surface)] px-4 text-center">
        <div>
          <h1 className="text-2xl font-bold text-[color:var(--ds-on-surface)]">Boutique non publiée</h1>
          <p className="mt-2 text-sm text-[color:var(--ds-on-surface-variant)]">Cette boutique n'est pas encore accessible au public.</p>
        </div>
      </div>
    );
  }

  return (
    <>
      <StorefrontTheme boutique={boutique} products={products} />
      {boutique && localStorage.getItem(`hanooty_chat_enabled_${boutique.slug}`) !== 'false' && localStorage.getItem('hanooty_boutique_chat_enabled') !== 'false' && (
        <ChatBox boutiqueId={boutique.id} apiBaseUrl="/api" />
      )}
    </>
  );
}

function mapProduct(item: any): StoreProduct {
  return {
    id: item.id,
    name: item.name,
    slug: item.slug,
    priceCents: item.sellingPrice ?? item.priceCents ?? 0,
    comparePriceCents: item.comparePrice ?? item.comparePriceCents ?? null,
    currency: item.currency,
    description: item.shortDescription ?? item.description ?? null,
    images: item.images ?? [],
    categoryName: item.categoryName ?? null,
    stockQuantity: item.stockQuantity,
    badge: item.badge ?? null,
    rating: item.rating,
    reviewsCount: item.reviewsCount,
  };
}
