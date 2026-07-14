import { useMemo, useState } from 'react';
import { Menu, Search } from 'lucide-react';
import { CartSheet } from './CartSheet';
import { BoutiqueAccountLink } from '../BoutiqueCustomerAccount';
import type { StoreProduct } from './ProductCard';
import type { StoreBoutique } from './StorefrontTheme';
import { boutiqueLink } from '../boutiqueRouting';
import { BrandMark, Footer, MobileMenu, navItems, TopBar } from './hanooti-marketplace/components';
import { AboutPage, ContactPage, HomePage, ProductListing, ReviewsPage } from './hanooti-marketplace/pages';
import type { CartItem } from './hanooti-marketplace/types';
import type { StoreCategory, StoreFilter } from './catalogueTypes';
import { buildCategories, findCategoryBySlug, filterProducts, isPromotion, productMatchesCategory, resolvePage, resolvePathParam } from './hanooti-marketplace/utils';
import { CookieConsentModal } from '../../../components/CookieConsentModal';
import { useCartAdd } from './useCartAdd';

const pageTitle = {
  category: 'Categorie',
};

export function HanootiMarketplaceTemplate({ boutique, products, categories: loadedCategories = [], filters }: { boutique: StoreBoutique; products: StoreProduct[]; categories?: StoreCategory[]; filters: StoreFilter[] }) {
  const [cart, setCart] = useState<CartItem[]>([]);
  const [menuOpen, setMenuOpen] = useState(false);
  const [query, setQuery] = useState('');
  const page = resolvePage();
  const categorySlug = resolvePathParam('categories');
  window.__boutiqueSlug__ = boutique.slug;

  const categories = useMemo(() => loadedCategories.length > 0 ? loadedCategories : buildCategories(products), [loadedCategories, products]);
  const currentCategory = findCategoryBySlug(categories, categorySlug);
  const searchedProducts = useMemo(() => filterProducts(products, query), [products, query]);
  const pageProducts = useMemo(() => {
    if (page === 'category' && currentCategory) {
      return searchedProducts.filter((product) => productMatchesCategory(product, currentCategory));
    }

    if (page === 'promotions') {
      return searchedProducts.filter(isPromotion);
    }

    return searchedProducts;
  }, [currentCategory, page, searchedProducts]);

  const featured = products.find((product) => product.badge || product.rating) ?? products[0] ?? null;
  const promos = products.filter(isPromotion).slice(0, 4);
  const bestSellers = [...products].sort((a, b) => (b.rating ?? 0) - (a.rating ?? 0)).slice(0, 4);

  const addLocalToCart = (product: StoreProduct) => {
    setCart((items) => {
      const existing = items.find((item) => item.product.id === product.id);
      return existing
        ? items.map((item) => item.product.id === product.id ? { ...item, qty: item.qty + 1 } : item)
        : [...items, { product, qty: 1 }];
    });
  };
  const { add: addToCart, consentOpen, acceptConsent, error: cartError } = useCartAdd({ boutiqueSlug: boutique.slug, onAdded: addLocalToCart });

  const setQty = (id: string, qty: number) => {
    setCart((items) => qty <= 0 ? items.filter((item) => item.product.id !== id) : items.map((item) => item.product.id === id ? { ...item, qty } : item));
  };

  return (
    <div
      className="min-h-screen bg-[color:var(--sf-bg,#FAF5FF)] text-[color:var(--sf-text,#0F172A)]"
      style={{
        fontFamily: 'var(--ds-font-family, Inter), system-ui, sans-serif',
        fontSize: 'var(--ds-font-size, 16px)',
      }}
    >
      <CookieConsentModal open={consentOpen} onAccept={acceptConsent} />
      {cartError && <div role="alert" className="fixed bottom-5 left-1/2 z-[90] -translate-x-1/2 rounded-full bg-red-600 px-5 py-3 text-sm font-semibold text-white shadow-xl">{cartError}</div>}
      <TopBar boutique={boutique} />
      <header className="sticky top-0 z-40 border-b border-[color:var(--sf-outline,#DDD6FE)] bg-white/85 backdrop-blur-xl">
        <div className="mx-auto flex h-20 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
          <a href={boutiqueLink('/')} className="flex items-center gap-3 rounded-full focus:outline-none focus:ring-2 focus:ring-[color:var(--sf-accent,#22C55E)]">
            <BrandMark boutique={boutique} />
            <div>
              <div className="text-xs font-bold uppercase tracking-[0.24em] text-[color:var(--sf-accent,#22C55E)]">Marketplace</div>
              <div className="text-lg font-black tracking-tight">{boutique.name}</div>
            </div>
          </a>

           <nav className="sf-desktop-nav hidden items-center gap-1 rounded-full border border-[color:var(--sf-outline,#DDD6FE)] bg-white/70 p-1 lg:flex" aria-label="Navigation boutique">
            {navItems().map((item) => <a key={item.href} href={item.href} className="rounded-full px-4 py-2 text-sm font-bold text-slate-600 transition-colors hover:bg-[color:var(--sf-surface-muted,#F3E8FF)] hover:text-[color:var(--sf-accent,#7C3AED)]">{item.label}</a>)}
          </nav>

          <div className="flex items-center gap-2">
            <label className="hidden h-11 items-center gap-2 rounded-full border border-[color:var(--sf-outline,#DDD6FE)] bg-white px-4 md:flex">
              <Search className="h-4 w-4 text-slate-400" />
              <input value={query} onChange={(event) => setQuery(event.target.value)} placeholder="Rechercher" className="w-36 bg-transparent text-sm outline-none placeholder:text-slate-400" />
            </label>
            <BoutiqueAccountLink boutiqueSlug={boutique.slug} />
            <CartSheet items={cart} onSetQty={setQty} onRemove={(id) => setCart((items) => items.filter((item) => item.product.id !== id))} />
             <button type="button" onClick={() => setMenuOpen(true)} className="sf-menu-toggle inline-flex h-11 w-11 cursor-pointer items-center justify-center rounded-full border border-[color:var(--sf-outline,#DDD6FE)] bg-white text-slate-700 transition-colors hover:bg-[color:var(--sf-surface-muted,#F3E8FF)] lg:hidden" aria-label="Ouvrir le menu">
              <Menu className="h-5 w-5" />
            </button>
          </div>
        </div>
      </header>

      {menuOpen && <MobileMenu onClose={() => setMenuOpen(false)} />}
      {page === 'home' && <HomePage boutique={boutique} categories={categories} featured={featured} promos={promos} bestSellers={bestSellers} onAdd={addToCart} />}
       {page === 'catalogue' && <ProductListing title="Catalogue" subtitle="Tous les produits disponibles dans cette boutique." products={pageProducts} categories={categories} filters={filters} query={query} onQuery={setQuery} onAdd={addToCart} />}
       {page === 'category' && <ProductListing title={currentCategory?.name ?? pageTitle.category} subtitle="Produits filtres par categorie et sous-categorie." products={pageProducts} categories={categories} filters={filters} category={currentCategory} query={query} onQuery={setQuery} onAdd={addToCart} />}
       {page === 'promotions' && <ProductListing title="Promotions" subtitle="Offres limitees, remises et coups de coeur." products={pageProducts} categories={categories} filters={filters} promotionsOnly query={query} onQuery={setQuery} onAdd={addToCart} />}
      {page === 'reviews' && <ReviewsPage products={products} reviewsEnabled={boutique.reviewsEnabled === true} />}
      {page === 'about' && <AboutPage boutique={boutique} categories={categories} />}
      {page === 'contact' && <ContactPage boutique={boutique} />}
      <Footer boutique={boutique} />
    </div>
  );
}
