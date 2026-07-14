import { ArrowRight, Mail, MapPin, Search, ShoppingCart, SlidersHorizontal, Star, Tag, X } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';
import { boutiqueLink } from '../../boutiqueRouting';
import { ImageWithFallback } from '../../../../components/ImageWithFallback';
import { ReviewSection } from '../../../../components/ReviewSection';
import { getProductImageUrl, type StoreProduct } from '../ProductCard';
import type { StoreBoutique } from '../StorefrontTheme';
import { ContactLine, EmptyPanel, Field, Metric, PageHero, Section, TrustGrid, BrandMark } from './components';
import type { StoreCategory, StoreFilter } from './types';
import { filterProducts, findCategoryParent, formatMoney, formatPrice, isPromotion, productMatchesCategory, sortProducts } from './utils';
import { formatStoreReviewDate, storeReviewInitial, useStorefrontReviews } from '../reviews';

export function HomePage({ boutique, categories, featured, promos, bestSellers, onAdd }: { boutique: StoreBoutique; categories: StoreCategory[]; featured: StoreProduct | null; promos: StoreProduct[]; bestSellers: StoreProduct[]; onAdd: (product: StoreProduct) => void }) {
  return (
    <main>
      <section className="relative overflow-hidden px-4 py-10 sm:px-6 lg:px-8 lg:py-16">
        <div className="absolute inset-x-0 top-0 -z-10 h-96 bg-[radial-gradient(circle_at_20%_20%,rgba(124,58,237,.20),transparent_30%),radial-gradient(circle_at_80%_10%,rgba(34,197,94,.18),transparent_28%)]" />
        <div className="mx-auto grid max-w-7xl gap-8 lg:grid-cols-[minmax(0,1.2fr)_420px]">
          <div className="rounded-[2rem] border border-white/70 bg-white/80 p-7 shadow-xl shadow-purple-950/5 backdrop-blur-xl sm:p-10 lg:p-12">
            <div className="inline-flex items-center gap-2 rounded-full bg-[color:var(--sf-surface-muted,#F3E8FF)] px-4 py-2 text-xs font-black uppercase tracking-[0.18em] text-[color:var(--sf-accent,#7C3AED)]">
              Boutique verifiee
            </div>
            <h1 className="mt-7 max-w-3xl text-4xl font-black leading-[0.95] tracking-[-0.05em] text-slate-950 sm:text-6xl lg:text-7xl">
              {boutique.name}, votre boutique premium en ligne.
            </h1>
            <p className="mt-6 max-w-2xl text-base leading-8 text-slate-600 sm:text-lg">
              {boutique.description || 'Explorez une selection soignee de produits, categories et offres pensees pour une experience rapide, claire et fiable.'}
            </p>
            <div className="mt-8 flex flex-wrap gap-3">
              <a href={boutiqueLink('/catalogue')} className="inline-flex items-center gap-2 rounded-full bg-[color:var(--sf-accent,#111111)] px-6 py-3 text-sm font-black text-white shadow-lg shadow-green-900/10 transition-colors hover:opacity-90">
                Explorer le catalogue <ArrowRight className="h-4 w-4" />
              </a>
              <a href={boutiqueLink('/promotions')} className="inline-flex items-center gap-2 rounded-full border border-[color:var(--sf-outline,#DDD6FE)] bg-white px-6 py-3 text-sm font-black text-slate-800 transition-colors hover:bg-[color:var(--sf-surface-muted,#F3E8FF)]">
                Voir les promotions <Tag className="h-4 w-4" />
              </a>
            </div>
          </div>
          <div className="grid gap-4">
            {featured ? <HeroProduct product={featured} onAdd={onAdd} /> : <EmptyPanel title="Aucun produit" text="Ajoutez des produits depuis le back-office." />}
            <TrustGrid />
          </div>
        </div>
      </section>

      <Section title="Categories" href={boutiqueLink('/catalogue')}>
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">{categories.slice(0, 8).map((category) => <CategoryCard key={category.slug} category={category} />)}</div>
      </Section>
      <Section title="Offres du moment" href={boutiqueLink('/promotions')}>
        <ProductGrid products={promos.length > 0 ? promos : bestSellers} onAdd={onAdd} />
      </Section>
      <Section title="Best-sellers" href={boutiqueLink('/catalogue')}>
        <ProductGrid products={bestSellers} onAdd={onAdd} />
      </Section>
    </main>
  );
}

export function ProductListing({ title, subtitle, products, categories, filters, query, onQuery, onAdd, category = null, promotionsOnly = false }: { title: string; subtitle: string; products: StoreProduct[]; categories: StoreCategory[]; filters: StoreFilter[]; query: string; onQuery: (value: string) => void; onAdd: (product: StoreProduct) => void; category?: StoreCategory | null; promotionsOnly?: boolean }) {
  const [minPrice, setMinPrice] = useState(() => readCatalogueParam('min'));
  const [maxPrice, setMaxPrice] = useState(() => readCatalogueParam('max'));
  const [brand, setBrand] = useState(() => readCatalogueParam('brand'));
  const [stockOnly, setStockOnly] = useState(() => readCatalogueParam('stock') === '1');
  const [sort, setSort] = useState(() => readCatalogueParam('sort') || 'relevance');
  const [selectedValues, setSelectedValues] = useState<Record<string, string[]>>(readCatalogueFilters);
  const [page, setPage] = useState(1);
  const [mobileFiltersOpen, setMobileFiltersOpen] = useState(false);
  const pageSize = 24;

  const brands = useMemo(
    () => Array.from(new Set(products.map((product) => product.brandName).filter((value): value is string => Boolean(value)))).sort((a, b) => a.localeCompare(b, 'fr')),
    [products],
  );

  const selectedValuesKey = JSON.stringify(selectedValues);
  const visibleProducts = useMemo(() => {
    let result = filterProducts(products, query);

    if (category) result = result.filter((product) => productMatchesCategory(product, category));
    if (promotionsOnly) result = result.filter(isPromotion);

    const minimum = Number(minPrice);
    const maximum = Number(maxPrice);
    if (minPrice && Number.isFinite(minimum)) result = result.filter((product) => product.priceCents >= minimum * 100);
    if (maxPrice && Number.isFinite(maximum)) result = result.filter((product) => product.priceCents <= maximum * 100);
    if (brand) result = result.filter((product) => product.brandName === brand);
    if (stockOnly) result = result.filter((product) => (product.stockQuantity ?? 0) > 0);

    Object.entries(selectedValues).forEach(([filterId, values]) => {
      if (values.length === 0) return;
      result = result.filter((product) => {
        const productValues = product.filterValues ?? [];
        return values.some((value) => productValues.some((item) => (item.filterId === filterId || item.filterSlug === filterId) && item.value === value));
      });
    });

    return sortProducts(result, sort);
  }, [brand, category, maxPrice, minPrice, products, promotionsOnly, query, selectedValuesKey, sort, stockOnly]);

  useEffect(() => {
    setPage(1);
  }, [brand, category?.id, maxPrice, minPrice, promotionsOnly, query, selectedValuesKey, sort, stockOnly]);

  useEffect(() => {
    const params = new URLSearchParams(window.location.search);
    ['min', 'max', 'brand', 'stock', 'sort', 'q'].forEach((key) => params.delete(key));
    Array.from(params.keys()).filter((key) => key.startsWith('filter_')).forEach((key) => params.delete(key));
    if (minPrice) params.set('min', minPrice);
    if (maxPrice) params.set('max', maxPrice);
    if (brand) params.set('brand', brand);
    if (stockOnly) params.set('stock', '1');
    if (sort !== 'relevance') params.set('sort', sort);
    if (query) params.set('q', query);
    Object.entries(selectedValues).forEach(([filterId, values]) => {
      if (values.length > 0) params.set(`filter_${filterId}`, values.join('|'));
    });

    const search = params.toString();
    window.history.replaceState(null, '', `${window.location.pathname}${search ? `?${search}` : ''}${window.location.hash}`);
  }, [brand, maxPrice, minPrice, query, selectedValuesKey, sort, stockOnly]);

  useEffect(() => {
    const urlQuery = readCatalogueParam('q');
    if (urlQuery && !query) onQuery(urlQuery);
  }, [onQuery, query]);

  const totalPages = Math.max(1, Math.ceil(visibleProducts.length / pageSize));
  const pagedProducts = visibleProducts.slice((page - 1) * pageSize, page * pageSize);
  const activeFilterCount = Number(Boolean(minPrice)) + Number(Boolean(maxPrice)) + Number(Boolean(brand)) + Number(stockOnly) + Object.values(selectedValues).flat().length;

  const toggleFilterValue = (filterId: string, value: string) => {
    setSelectedValues((current) => {
      const values = current[filterId] ?? [];
      const nextValues = values.includes(value) ? values.filter((item) => item !== value) : [...values, value];
      return { ...current, [filterId]: nextValues };
    });
  };

  const clearFilters = () => {
    setMinPrice('');
    setMaxPrice('');
    setBrand('');
    setStockOnly(false);
    setSort('relevance');
    setSelectedValues({});
  };

  const filterPanel = (
    <div className="space-y-6">
      <div>
        <div className="mb-3 flex items-center justify-between">
          <div className="text-xs font-black uppercase tracking-[0.18em] text-slate-400">Filtres</div>
          {activeFilterCount > 0 && <button type="button" onClick={clearFilters} className="text-xs font-black text-[color:var(--sf-accent,#7C3AED)]">Réinitialiser</button>}
        </div>
        <label className="flex items-center gap-2 rounded-full border border-[color:var(--sf-outline,#DDD6FE)] px-4 py-3">
          <Search className="h-4 w-4 text-slate-400" />
          <input value={query} onChange={(event) => onQuery(event.target.value)} placeholder="Produit..." className="min-w-0 flex-1 bg-transparent text-sm outline-none" />
        </label>
      </div>

      <div>
        <div className="mb-3 text-xs font-black uppercase tracking-[0.18em] text-slate-400">Catégories</div>
        <div className="space-y-1">
          <a href={boutiqueLink('/catalogue')} className="block rounded-xl px-3 py-2 text-sm font-bold text-slate-700 transition-colors hover:bg-[color:var(--sf-surface-muted,#F3E8FF)]">Toutes</a>
          {categories.map((item) => <CategoryLinks key={item.id} category={item} activeSlug={category?.slug ?? ''} />)}
        </div>
      </div>

      <div>
        <div className="mb-3 text-xs font-black uppercase tracking-[0.18em] text-slate-400">Prix (DT)</div>
        <div className="grid grid-cols-2 gap-2">
          <input type="number" min="0" value={minPrice} onChange={(event) => setMinPrice(event.target.value)} placeholder="Min" className="w-full rounded-xl border border-[color:var(--sf-outline,#DDD6FE)] px-3 py-2 text-sm outline-none" />
          <input type="number" min="0" value={maxPrice} onChange={(event) => setMaxPrice(event.target.value)} placeholder="Max" className="w-full rounded-xl border border-[color:var(--sf-outline,#DDD6FE)] px-3 py-2 text-sm outline-none" />
         </div>
       </div>

      {brands.length > 0 && (
        <label className="block">
          <span className="mb-3 block text-xs font-black uppercase tracking-[0.18em] text-slate-400">Marque</span>
          <select value={brand} onChange={(event) => setBrand(event.target.value)} className="w-full rounded-xl border border-[color:var(--sf-outline,#DDD6FE)] bg-white px-3 py-2 text-sm outline-none">
            <option value="">Toutes les marques</option>
            {brands.map((item) => <option key={item} value={item}>{item}</option>)}
          </select>
        </label>
      )}

      <label className="flex cursor-pointer items-center gap-3 text-sm font-bold text-slate-700">
        <input type="checkbox" checked={stockOnly} onChange={(event) => setStockOnly(event.target.checked)} className="h-4 w-4 accent-[color:var(--sf-accent,#7C3AED)]" />
        Disponible uniquement
      </label>

      {filters.map((filter) => filter.values.length > 0 && (
        <div key={filter.id}>
          <div className="mb-3 text-xs font-black uppercase tracking-[0.18em] text-slate-400">{filter.name}</div>
          <div className="space-y-2">
            {filter.values.map((value) => (
              <label key={value.id} className="flex cursor-pointer items-center gap-3 text-sm text-slate-700">
                <input type="checkbox" checked={(selectedValues[filter.id] ?? []).includes(value.value)} onChange={() => toggleFilterValue(filter.id, value.value)} className="h-4 w-4 accent-[color:var(--sf-accent,#7C3AED)]" />
                {value.value}
              </label>
            ))}
          </div>
        </div>
      ))}
    </div>
  );

  return (
    <main className="px-4 py-10 sm:px-6 lg:px-8">
      <div className="mx-auto max-w-7xl">
        <PageHero title={title} subtitle={subtitle} />
        {category && <CategoryBreadcrumb categories={categories} category={category} />}
        {category?.children.length ? <div className="mt-6 flex flex-wrap gap-3">{category.children.map((child) => <a key={child.id} href={boutiqueLink(`/categories/${child.slug}`)} className="rounded-full border border-[color:var(--sf-outline,#DDD6FE)] bg-white px-4 py-2 text-sm font-bold text-slate-700 hover:bg-[color:var(--sf-surface-muted,#F3E8FF)]">{child.name} <span className="text-slate-400">({child.count})</span></a>)}</div> : null}
        <button type="button" onClick={() => setMobileFiltersOpen(true)} className="mt-6 inline-flex items-center gap-2 rounded-full border border-[color:var(--sf-outline,#DDD6FE)] bg-white px-4 py-2 text-sm font-black text-slate-700 lg:hidden"><SlidersHorizontal className="h-4 w-4" /> Filtres{activeFilterCount > 0 ? ` (${activeFilterCount})` : ''}</button>
        <div className="mt-8 grid gap-6 lg:grid-cols-[260px_minmax(0,1fr)]">
          <aside className="hidden h-max rounded-[1.6rem] border border-[color:var(--sf-outline,#DDD6FE)] bg-white/85 p-5 shadow-sm lg:block">{filterPanel}</aside>
          {mobileFiltersOpen && <div className="fixed inset-0 z-50 bg-slate-950/40 p-4 lg:hidden"><div className="ml-auto h-full max-w-sm overflow-y-auto rounded-[1.6rem] bg-white p-5 shadow-2xl"><div className="mb-5 flex items-center justify-between"><strong>Filtrer les produits</strong><button type="button" onClick={() => setMobileFiltersOpen(false)} aria-label="Fermer les filtres"><X className="h-5 w-5" /></button></div>{filterPanel}</div></div>}
          <div>
            <div className="mb-5 flex flex-wrap items-center justify-between gap-4">
              <p className="text-sm font-bold text-slate-500">{visibleProducts.length} produit(s)</p>
              <label className="flex items-center gap-2 text-sm font-bold text-slate-500">Trier par <select value={sort} onChange={(event) => setSort(event.target.value)} className="rounded-xl border border-[color:var(--sf-outline,#DDD6FE)] bg-white px-3 py-2 text-sm text-slate-700 outline-none"><option value="relevance">Pertinence</option><option value="newest">Nouveautés</option><option value="price-asc">Prix croissant</option><option value="price-desc">Prix décroissant</option><option value="name">Nom</option><option value="rating">Mieux notés</option></select></label>
              <a href={boutiqueLink('/contact')} className="text-sm font-black text-[color:var(--sf-accent,#7C3AED)]">Besoin d'aide ?</a>
            </div>
            <ProductGrid products={pagedProducts} onAdd={onAdd} />
            {totalPages > 1 && <Pagination page={page} totalPages={totalPages} onPageChange={setPage} />}
          </div>
        </div>
      </div>
    </main>
  );
}

function CategoryLinks({ category, activeSlug, depth = 0 }: { category: StoreCategory; activeSlug: string; depth?: number }) {
  return (
    <div>
      <a href={boutiqueLink(`/categories/${category.slug}`)} className={`flex items-center justify-between rounded-xl px-3 py-2 text-sm font-bold transition-colors hover:bg-[color:var(--sf-surface-muted,#F3E8FF)] ${activeSlug === category.slug ? 'bg-[color:var(--sf-surface-muted,#F3E8FF)] text-[color:var(--sf-accent,#7C3AED)]' : 'text-slate-700'}`} style={{ paddingLeft: `${12 + depth * 14}px` }}>
        <span>{category.name}</span><span className="text-xs text-slate-400">{category.count}</span>
      </a>
      {category.children.map((child) => <CategoryLinks key={child.id} category={child} activeSlug={activeSlug} depth={depth + 1} />)}
    </div>
  );
}

function CategoryBreadcrumb({ categories, category }: { categories: StoreCategory[]; category: StoreCategory }) {
  const parent = findCategoryParent(categories, category.id);

  return <nav aria-label="Fil d'Ariane" className="mt-5 flex flex-wrap items-center gap-2 text-sm font-bold text-slate-500"><a href={boutiqueLink('/catalogue')} className="hover:text-[color:var(--sf-accent,#7C3AED)]">Catalogue</a><span>/</span>{parent && <><a href={boutiqueLink(`/categories/${parent.slug}`)} className="hover:text-[color:var(--sf-accent,#7C3AED)]">{parent.name}</a><span>/</span></>}<span className="text-slate-900">{category.name}</span></nav>;
}

function Pagination({ page, totalPages, onPageChange }: { page: number; totalPages: number; onPageChange: (page: number) => void }) {
  return <div className="mt-8 flex items-center justify-center gap-2"><button type="button" disabled={page === 1} onClick={() => onPageChange(page - 1)} className="rounded-full border border-[color:var(--sf-outline,#DDD6FE)] px-4 py-2 text-sm font-bold disabled:cursor-not-allowed disabled:opacity-40">Précédent</button><span className="px-3 text-sm font-bold text-slate-500">Page {page} / {totalPages}</span><button type="button" disabled={page === totalPages} onClick={() => onPageChange(page + 1)} className="rounded-full border border-[color:var(--sf-outline,#DDD6FE)] px-4 py-2 text-sm font-bold disabled:cursor-not-allowed disabled:opacity-40">Suivant</button></div>;
}

function readCatalogueParam(key: string): string {
  return new URLSearchParams(window.location.search).get(key) ?? '';
}

function readCatalogueFilters(): Record<string, string[]> {
  const filters: Record<string, string[]> = {};
  new URLSearchParams(window.location.search).forEach((value, key) => {
    if (key.startsWith('filter_')) filters[key.slice(7)] = value.split('|').filter(Boolean);
  });

  return filters;
}

export function ReviewsPage({ products, reviewsEnabled }: { products: StoreProduct[]; reviewsEnabled: boolean }) {
  const { reviews, isLoading } = useStorefrontReviews(reviewsEnabled);
  const productNames = new Map(products.map((product) => [product.id, product.name]));

  return (
    <main className="px-4 py-10 sm:px-6 lg:px-8">
      <div className="mx-auto max-w-7xl">
        <PageHero title="Avis clients" subtitle="Retours vérifiés sur la boutique et ses produits." />
        <div className="mt-8 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
          {!reviewsEnabled ? <div className="rounded-[1.5rem] border border-[color:var(--sf-outline,#DDD6FE)] bg-white p-8 text-center text-sm text-slate-500 md:col-span-2 xl:col-span-3">Les avis ne sont pas activés pour cette boutique.</div> : isLoading ? <ReviewLoadingCard /> : reviews.length > 0 ? reviews.map((review) => (
            <article key={review.id} className="rounded-[1.5rem] border border-[color:var(--sf-outline,#DDD6FE)] bg-white p-6 shadow-sm">
              <div className="flex items-start justify-between gap-3">
                <div className="flex items-center gap-3">
                  <span className="grid h-10 w-10 place-items-center rounded-full bg-[color:var(--sf-surface-muted,#F3E8FF)] text-sm font-black text-[color:var(--sf-accent,#7C3AED)]">{storeReviewInitial(review.authorName)}</span>
                  <div><div className="text-sm font-black text-slate-950">{review.authorName}</div><div className="text-xs text-slate-400">{review.productId ? `Produit : ${productNames.get(review.productId) ?? 'Produit'}` : 'Avis boutique'}</div></div>
                </div>
                <time className="text-xs text-slate-400" dateTime={review.createdAt}>{formatStoreReviewDate(review.createdAt)}</time>
              </div>
              <div className="mt-4 flex items-center gap-1 text-amber-500" aria-label={`${review.rating} étoiles sur 5`}>{[1, 2, 3, 4, 5].map((star) => <Star key={star} className={`h-4 w-4 ${star <= review.rating ? 'fill-current' : ''}`} />)}</div>
              {review.comment && <p className="mt-4 text-sm leading-7 text-slate-600">{review.comment}</p>}
              {review.isVerifiedPurchase && <div className="mt-4 text-xs font-bold text-emerald-600">Achat vérifié</div>}
            </article>
          )) : <div className="rounded-[1.5rem] border border-[color:var(--sf-outline,#DDD6FE)] bg-white p-8 text-center text-sm text-slate-500 md:col-span-2 xl:col-span-3">Aucun avis publié pour le moment.</div>}
        </div>
        {reviewsEnabled && <div className="mt-10"><ReviewSection boutiqueSlug="" /></div>}
      </div>
    </main>
  );
}

function ReviewLoadingCard() {
  return <div className="h-48 animate-pulse rounded-[1.5rem] border border-[color:var(--sf-outline,#DDD6FE)] bg-white/70 md:col-span-3" aria-label="Chargement des avis" />;
}

function EmptyReviewCard() {
  return <div className="rounded-[1.5rem] border border-black/10 bg-white p-8 text-center text-sm text-black/55 md:col-span-3">Aucun avis boutique publié pour le moment.</div>;
}

function StorefrontReviewCard({ review }: { review: import('../reviews').StoreReview }) {
  return (
    <article className="rounded-[1.5rem] border border-black/10 bg-white p-6 shadow-sm">
      <div className="flex items-start justify-between gap-3">
        <div className="flex items-center gap-3"><span className="grid h-10 w-10 place-items-center rounded-full bg-black/5 text-sm font-bold">{storeReviewInitial(review.authorName)}</span><div><div className="text-sm font-semibold">{review.authorName}</div><div className="text-xs text-black/45">Avis boutique</div></div></div>
        <time className="text-xs text-black/45" dateTime={review.createdAt}>{formatStoreReviewDate(review.createdAt)}</time>
      </div>
      <div className="mt-4 flex items-center gap-1 text-amber-500" aria-label={`${review.rating} étoiles sur 5`}>{[1, 2, 3, 4, 5].map((star) => <Star key={star} className={`h-4 w-4 ${star <= review.rating ? 'fill-current' : ''}`} />)}</div>
      <p className="mt-4 text-sm leading-7 text-black/60">{review.comment ?? 'Avis noté par un client.'}</p>
    </article>
  );
}

export function AboutPage({ boutique, categories }: { boutique: StoreBoutique; categories: StoreCategory[] }) {
  return (
    <main className="px-4 py-10 sm:px-6 lg:px-8">
      <div className="mx-auto grid max-w-7xl gap-8 lg:grid-cols-[minmax(0,1fr)_360px]">
        <div>
          <PageHero title={`A propos de ${boutique.name}`} subtitle={boutique.description || 'Une boutique independante avec une selection claire, moderne et fiable.'} />
          <div className="mt-8 grid gap-4 sm:grid-cols-3"><Metric label="Categories" value={String(categories.length)} /><Metric label="Support" value="7j/7" /><Metric label="Paiement" value="Securise" /></div>
        </div>
        <div className="rounded-[1.8rem] border border-[color:var(--sf-outline,#DDD6FE)] bg-white p-6 shadow-sm"><BrandMark boutique={boutique} large /><h2 className="mt-5 text-2xl font-black text-slate-950">Notre promesse</h2><p className="mt-3 text-sm leading-7 text-slate-600">Produits organises par slug, navigation rapide, panier accessible et experience personnalisee par theme.</p></div>
      </div>
    </main>
  );
}

export function ContactPage({ boutique }: { boutique: StoreBoutique }) {
  return (
    <main className="px-4 py-10 sm:px-6 lg:px-8">
      <div className="mx-auto grid max-w-7xl gap-8 lg:grid-cols-[420px_minmax(0,1fr)]">
        <div><PageHero title="Contact" subtitle="Une question sur un produit, une commande ou une livraison ? Contactez la boutique." /><div className="mt-8 space-y-3">{boutique.email && <ContactLine icon={<Mail className="h-5 w-5" />} text={boutique.email} href={`mailto:${boutique.email}`} />}{boutique.address && <ContactLine icon={<MapPin className="h-5 w-5" />} text={boutique.address} />}</div></div>
        <form className="rounded-[1.8rem] border border-[color:var(--sf-outline,#DDD6FE)] bg-white p-6 shadow-sm" onSubmit={(event) => event.preventDefault()}>
          <div className="grid gap-4 sm:grid-cols-2"><Field label="Nom" /><Field label="Email" type="email" /></div><Field label="Sujet" />
          <label className="mt-4 block text-sm font-black text-slate-700">Message<textarea rows={6} className="mt-2 w-full rounded-2xl border border-[color:var(--sf-outline,#DDD6FE)] px-4 py-3 font-normal outline-none focus:ring-2 focus:ring-[color:var(--sf-accent,#22C55E)]" /></label>
          <button type="submit" className="mt-5 cursor-pointer rounded-full bg-[color:var(--sf-accent,#111111)] px-6 py-3 text-sm font-black text-white transition-opacity hover:opacity-90">Envoyer</button>
        </form>
      </div>
    </main>
  );
}

function ProductGrid({ products, onAdd }: { products: StoreProduct[]; onAdd: (product: StoreProduct) => void }) {
  if (products.length === 0) return <EmptyPanel title="Aucun produit" text="Aucun produit ne correspond a cette page." />;
  return <div className="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">{products.map((product) => <MarketplaceProductCard key={product.id} product={product} onAdd={onAdd} />)}</div>;
}

function MarketplaceProductCard({ product, onAdd }: { product: StoreProduct; onAdd: (product: StoreProduct) => void }) {
  const image = getProductImageUrl(product);
  return (
    <article className="group overflow-hidden rounded-[1.6rem] border border-[color:var(--sf-outline,#DDD6FE)] bg-white shadow-sm transition-shadow hover:shadow-xl hover:shadow-purple-950/10">
      <a href={boutiqueLink(`/products/${product.slug}`)} className="relative block aspect-[1.02] overflow-hidden bg-[color:var(--sf-surface-muted,#F3E8FF)]"><ImageWithFallback src={image} alt={product.name} className="h-full w-full object-cover transition duration-300 group-hover:scale-105" /><span className="absolute left-4 top-4 rounded-full bg-white/90 px-3 py-1 text-xs font-black text-slate-800 backdrop-blur">{product.badge || (isPromotion(product) ? 'Promo' : 'Nouveau')}</span></a>
      <div className="p-5"><a href={product.categorySlug ? boutiqueLink(`/categories/${product.categorySlug}`) : boutiqueLink('/catalogue')} className="text-xs font-black uppercase tracking-[0.18em] text-[color:var(--sf-accent,#7C3AED)]">{product.categoryName || 'Collection'}</a><a href={boutiqueLink(`/products/${product.slug}`)} className="mt-2 line-clamp-2 block min-h-[3rem] text-base font-black text-slate-950 transition-colors hover:text-[color:var(--sf-accent,#7C3AED)]">{product.name}</a><div className="mt-4 flex items-center gap-2"><strong>{formatPrice(product)}</strong>{isPromotion(product) && product.comparePriceCents ? <span className="text-sm text-slate-400 line-through">{formatMoney(product.comparePriceCents, product.currency)}</span> : null}</div><button type="button" onClick={() => onAdd(product)} className="mt-5 inline-flex w-full cursor-pointer items-center justify-center gap-2 rounded-full bg-[color:var(--sf-accent,#22C55E)] px-4 py-3 text-sm font-black text-white transition-colors hover:bg-green-600"><ShoppingCart className="h-4 w-4" aria-hidden="true" /> Ajouter</button></div>
    </article>
  );
}

function HeroProduct({ product, onAdd }: { product: StoreProduct; onAdd: (product: StoreProduct) => void }) {
  const image = getProductImageUrl(product);
  return <article className="overflow-hidden rounded-[2rem] border border-white/70 bg-white shadow-xl shadow-purple-950/5"><div className="aspect-[1.25] bg-[color:var(--sf-surface-muted,#F3E8FF)]"><ImageWithFallback src={image} alt={product.name} className="h-full w-full object-cover" /></div><div className="p-6"><div className="text-xs font-black uppercase tracking-[0.18em] text-[color:var(--sf-accent,#111111)]">Produit phare</div><h2 className="mt-2 text-xl font-black text-slate-950">{product.name}</h2><div className="mt-3 font-black">{formatPrice(product)}</div><button type="button" onClick={() => onAdd(product)} className="mt-5 w-full cursor-pointer rounded-full bg-[color:var(--sf-accent,#111111)] px-5 py-3 text-sm font-black text-white transition-opacity hover:opacity-90">Ajouter au panier</button></div></article>;
}

function CategoryCard({ category }: { category: StoreCategory }) {
  return <a href={boutiqueLink(`/categories/${category.slug}`)} className="group overflow-hidden rounded-[1.5rem] border border-[color:var(--sf-outline,#DDD6FE)] bg-white shadow-sm transition-shadow hover:shadow-xl hover:shadow-purple-950/10"><div className="aspect-[1.2] bg-[color:var(--sf-surface-muted,#F3E8FF)]"><ImageWithFallback src={category.image} alt={category.name} className="h-full w-full object-cover transition duration-300 group-hover:scale-105" /></div><div className="flex items-center justify-between p-5"><div><div className="font-black text-slate-950">{category.name}</div><div className="mt-1 text-sm text-slate-500">{category.count} produits</div></div><ArrowRight className="h-4 w-4 text-slate-400 transition-transform group-hover:translate-x-1" /></div></a>;
}
