import { type ReactNode, useEffect, useMemo, useState } from 'react';
import { AnimatePresence, motion } from 'framer-motion';
import {
  ArrowRight,
  Mail,
  Menu,
  Search,
  ShieldCheck,
  Star,
  Sparkles,
  Truck,
  X,
} from 'lucide-react';
import { CartSheet } from './CartSheet';
import { boutiqueLink } from '../boutiqueRouting';
import { type StoreProduct } from './ProductCard';
import type { StoreCategory, StoreFilter } from './catalogueTypes';
import { AboutPage, ContactPage, ProductListing, ReviewsPage } from './hanooti-marketplace/pages';
import { buildCategories, findCategoryBySlug, isPromotion, productMatchesCategory, resolvePage, resolvePathParam } from './hanooti-marketplace/utils';
import { CookieConsentModal } from '../../../components/CookieConsentModal';
import { ImageWithFallback } from '../../../components/ImageWithFallback';
import { BoutiqueAccountLink } from '../BoutiqueCustomerAccount';
import { useCartAdd } from './useCartAdd';
import { formatStoreReviewDate, storeReviewInitial, useStorefrontReviews, type StoreReview } from './reviews';
import { getStorefrontThemePreset } from '../../../theme/themes';

/** Brand accent used for CTAs/badges — falls back to the editorial black when a boutique has no custom color. */
const BRAND = 'var(--sf-accent, var(--ds-primary, #111111))';
const DEFAULT_HERO_BACKGROUND = '#111111';
const DEFAULT_HERO_TEXT = '#ffffff';

function resolveHeroBackground(boutique: StoreBoutique): string {
  const configuredBackground = boutique.backgroundColor?.trim() || boutique.colorPalette?.background?.trim();
  if (!configuredBackground) return DEFAULT_HERO_BACKGROUND;

  const presetBackground = getStorefrontThemePreset(boutique.theme)?.colorPalette.background;
  if (presetBackground?.toLowerCase() === configuredBackground.toLowerCase()) {
    return DEFAULT_HERO_BACKGROUND;
  }

  return configuredBackground;
}

function resolveHeroTextColor(boutique: StoreBoutique): string {
  const configuredText = boutique.colorPalette?.text?.trim();
  if (!configuredText) return DEFAULT_HERO_TEXT;

  const presetText = getStorefrontThemePreset(boutique.theme)?.colorPalette.text;
  if (presetText?.toLowerCase() === configuredText.toLowerCase()) {
    return DEFAULT_HERO_TEXT;
  }

  return configuredText;
}
const easeBrand = [0.16, 1, 0.3, 1] as const;
const fadeUp = {
  hidden: { opacity: 0, y: 18 },
  show: { opacity: 1, y: 0, transition: { duration: 0.5, ease: easeBrand } },
};
const staggerContainer = {
  hidden: {},
  show: { transition: { staggerChildren: 0.08 } },
};

export type { StoreProduct } from './ProductCard';

export type StoreBoutique = {
  id: string;
  name: string;
  slug: string;
  logoUrl?: string | null;
  description?: string | null;
  coverImage?: string | null;
  backgroundColor?: string | null;
  colorPalette?: Record<string, string> | null;
  email?: string | null;
  address?: string | null;
  primaryColor?: string;
  heroTitle?: string;
  heroSubtitle?: string;
  theme?: string | null;
  fontFamily?: string | null;
  fontSize?: string | null;
  reviewsEnabled?: boolean;
};

type CartItem = { product: StoreProduct; qty: number };
export function StorefrontTheme({
  boutique,
  products: initial,
  categories: loadedCategories = [],
  filters,
  reviewsEnabled = false,
}: {
  boutique: StoreBoutique;
  products: StoreProduct[];
  categories?: StoreCategory[];
  filters: StoreFilter[];
  reviewsEnabled?: boolean;
}) {
  const [cart, setCart] = useState<CartItem[]>([]);
  const [mobileMenu, setMobileMenu] = useState(false);
  const [searchOpen, setSearchOpen] = useState(false);
  const [searchQuery, setSearchQuery] = useState('');
  const [coverImageFailed, setCoverImageFailed] = useState(false);

  const heroColor = resolveHeroBackground(boutique);
  const heroTextColor = resolveHeroTextColor(boutique);

  useEffect(() => {
    setCoverImageFailed(false);
  }, [boutique.coverImage]);

  window.__boutiqueSlug__ = boutique.slug;

  const categories = useMemo<StoreCategory[]>(() => loadedCategories.length > 0 ? loadedCategories : buildFallbackCategories(initial), [initial, loadedCategories]);

  const filteredProducts = useMemo(
    () =>
      searchQuery
        ? initial.filter(
            (product) =>
              product.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
              product.description?.toLowerCase().includes(searchQuery.toLowerCase())
          )
        : initial,
    [initial, searchQuery]
  );

  const featuredProduct = filteredProducts[0] ?? initial[0] ?? null;
  const spotlightProduct =
    filteredProducts.find((product) => (product.comparePriceCents ?? 0) > product.priceCents) ??
    filteredProducts[1] ??
    featuredProduct;
  const newArrivals = filteredProducts.slice(0, 4);
  const bestSellers = [...filteredProducts]
    .sort((left, right) => (right.rating ?? 0) - (left.rating ?? 0))
    .slice(0, 2);
  const heroCategories = categories.slice(0, 2);
  const collectionCategories = categories.length > 0 ? categories : buildFallbackCategories(initial);
  const routePage = resolvePage();
  const categorySlug = resolvePathParam('categories');
  const currentCategory = findCategoryBySlug(categories, categorySlug);
  const routeProducts = routePage === 'category' && currentCategory
    ? initial.filter((product) => productMatchesCategory(product, currentCategory))
    : routePage === 'promotions'
      ? initial.filter(isPromotion)
      : initial;

  const addLocalToCart = (product: StoreProduct) => {
    setCart((current) => {
      const existing = current.find((item) => item.product.id === product.id);
      if (existing) {
        return current.map((item) =>
          item.product.id === product.id ? { ...item, qty: item.qty + 1 } : item
        );
      }

      return [...current, { product, qty: 1 }];
    });
  };
  const { add: handleAddToCart, consentOpen, acceptConsent, error: cartError } = useCartAdd({ boutiqueSlug: boutique.slug, onAdded: addLocalToCart });
  const { reviews, isLoading: reviewsLoading } = useStorefrontReviews(reviewsEnabled);
  const featuredReviews = reviews.slice(0, 3);
  const productNames = new Map(initial.map((product) => [product.id, product.name]));

  const handleSetQty = (id: string, qty: number) => {
    if (qty <= 0) {
      setCart((current) => current.filter((item) => item.product.id !== id));
      return;
    }

    setCart((current) =>
      current.map((item) => (item.product.id === id ? { ...item, qty } : item))
    );
  };

  const handleRemove = (id: string) => {
    setCart((current) => current.filter((item) => item.product.id !== id));
  };

  const navItems = [
    { label: 'Accueil', href: boutiqueLink('/') },
    { label: 'Catalogue', href: boutiqueLink('/catalogue') },
    { label: 'Promotions', href: boutiqueLink('/promotions') },
    { label: 'A propos', href: boutiqueLink('/a-propos') },
    { label: 'Contact', href: boutiqueLink('/contact') },
  ];

  return (
    <div
      className="min-h-screen bg-[color:var(--sf-bg,#f6f2eb)] text-[color:var(--sf-text,#171717)]"
      style={{
        fontFamily: 'var(--ds-font-family, Inter), system-ui, sans-serif',
        fontSize: 'var(--ds-font-size, 16px)',
      }}
    >
      <CookieConsentModal open={consentOpen} onAccept={acceptConsent} />
      {cartError && <div role="alert" className="fixed bottom-5 left-1/2 z-[90] -translate-x-1/2 rounded-full bg-red-600 px-5 py-3 text-sm font-semibold text-white shadow-xl">{cartError}</div>}
      <TopRibbon boutique={boutique} />

      <header className="sticky top-0 z-30 border-b border-black/10 bg-[color:var(--sf-bg,#f6f2eb)]/90 backdrop-blur-xl">
        <div className="mx-auto flex h-20 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
          <button
            className="sf-menu-toggle rounded-full p-2 text-[#171717] lg:hidden"
            onClick={() => setMobileMenu(true)}
            aria-label="Menu"
          >
            <Menu className="h-5 w-5" />
          </button>

          <a href={boutiqueLink('/')} className="flex items-center gap-3">
            <ImageWithFallback src={boutique.logoUrl} alt={boutique.logoUrl ? boutique.name : 'Hanooti'} className="h-10 w-10 rounded-full object-cover" />
            <div>
              <div className="text-xs uppercase tracking-[0.28em] text-black/50">N Collection</div>
              <div className="text-lg font-semibold tracking-tight">{boutique.name}</div>
            </div>
          </a>

          <nav className="sf-desktop-nav hidden items-center gap-8 lg:flex">
            {navItems.map((item) => (
              <a key={item.label} href={item.href} className="text-sm font-medium text-black/70 transition hover:text-black">
                {item.label}
              </a>
            ))}
          </nav>

          <div className="flex items-center gap-2">
            <button
              onClick={() => setSearchOpen(true)}
              className="rounded-full border border-black/10 p-2 text-black transition hover:bg-white"
              aria-label="Rechercher"
            >
              <Search className="h-4 w-4" />
            </button>
            <BoutiqueAccountLink boutiqueSlug={boutique.slug} />
            <CartSheet items={cart} onSetQty={handleSetQty} onRemove={handleRemove} />
          </div>
        </div>
      </header>

      <AnimatePresence>
        {mobileMenu && (
          <>
            <motion.div
              className="fixed inset-0 z-40 bg-black/30 backdrop-blur-sm"
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              exit={{ opacity: 0 }}
              onClick={() => setMobileMenu(false)}
            />
            <motion.div
              className="fixed inset-y-0 left-0 z-50 w-80 bg-[#f6f2eb] px-6 py-8 shadow-2xl"
              initial={{ x: '-100%' }}
              animate={{ x: 0 }}
              exit={{ x: '-100%' }}
              transition={{ type: 'spring', stiffness: 340, damping: 32 }}
            >
              <div className="mb-10 flex items-center justify-between">
                <div>
                  <div className="text-xs uppercase tracking-[0.28em] text-black/50">Navigation</div>
                  <div className="text-xl font-semibold">{boutique.name}</div>
                </div>
                <button className="rounded-full border border-black/10 p-2" onClick={() => setMobileMenu(false)}>
                  <X className="h-4 w-4" />
                </button>
              </div>
              <nav className="space-y-4">
                {navItems.map((item) => (
                  <a key={item.label} href={item.href} className="block text-lg font-medium text-black/80">
                    {item.label}
                  </a>
                ))}
              </nav>
            </motion.div>
          </>
        )}
      </AnimatePresence>

      <AnimatePresence>
        {searchOpen && (
          <SearchOverlay query={searchQuery} onQuery={setSearchQuery} onClose={() => setSearchOpen(false)} />
        )}
      </AnimatePresence>

        <main>
          {routePage === 'catalogue' || routePage === 'category' || routePage === 'promotions' ? (
            <ProductListing
              title={routePage === 'category' ? (currentCategory?.name ?? 'Catégorie') : routePage === 'promotions' ? 'Promotions' : 'Catalogue'}
              subtitle={routePage === 'category' ? 'Produits filtrés par catégorie et sous-catégorie.' : routePage === 'promotions' ? 'Offres limitées, remises et coups de coeur.' : 'Tous les produits disponibles dans cette boutique.'}
              products={routeProducts}
              categories={categories}
              filters={filters}
              category={currentCategory}
              promotionsOnly={routePage === 'promotions'}
              query={searchQuery}
              onQuery={setSearchQuery}
              onAdd={handleAddToCart}
            />
          ) : routePage === 'about' ? (
            <AboutPage boutique={boutique} categories={categories} />
          ) : routePage === 'contact' ? (
            <ContactPage boutique={boutique} />
          ) : routePage === 'reviews' ? (
            <ReviewsPage products={initial} reviewsEnabled={reviewsEnabled} />
          ) : (
          <>
        <section className="px-4 pb-10 pt-6 sm:px-6 lg:px-8 lg:pb-16 lg:pt-8">
          <motion.div
            className="mx-auto grid max-w-7xl gap-6 lg:grid-cols-[minmax(0,1.35fr)_430px]"
            variants={staggerContainer}
            initial="hidden"
            animate="show"
          >
             <motion.div
               variants={fadeUp}
               className="relative overflow-hidden rounded-[2rem] bg-[#111111] px-8 py-8 text-white sm:px-10 sm:py-10 lg:min-h-[620px] lg:px-14 lg:py-16"
               style={{ backgroundColor: heroColor }}
             >
               {boutique.coverImage && !coverImageFailed ? (
                 <>
                   <ImageWithFallback src={boutique.coverImage} alt="" aria-hidden="true" onError={() => setCoverImageFailed(true)} className="absolute inset-0 h-full w-full object-cover opacity-65" />
                   <div className="absolute inset-0 bg-gradient-to-br from-[#111111]/95 via-[#111111]/75 to-[#111111]/35" aria-hidden="true" />
                 </>
               ) : <div className="absolute inset-0 bg-black/20" aria-hidden="true" />}

               <div className="relative z-10">
                 <div className="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-4 py-2 text-xs uppercase tracking-[0.22em] text-white/70">
                   <Sparkles className="h-3.5 w-3.5" style={{ color: BRAND }} /> Midnight Collection 2026
                 </div>

                  <h1
                    className="mt-8 max-w-3xl text-4xl font-semibold uppercase leading-[0.92] tracking-[-0.05em] sm:text-6xl lg:text-7xl"
                    style={{ color: heroTextColor }}
                  >
                   {formatHeroTitle(boutique.name)}
                 </h1>

                 <p className="mt-6 max-w-xl text-sm leading-7 text-white/72 sm:text-base">
                   {boutique.heroSubtitle || boutique.description || 'Precision minimaliste, matieres fortes et silhouettes pensees pour une boutique contemporaine.'}
                 </p>

                 <div className="mt-9 flex flex-wrap gap-3">
                   <motion.a
                     whileHover={{ y: -2 }}
                     whileTap={{ scale: 0.97 }}
                     href={boutiqueLink('/catalogue')}
                     className="inline-flex items-center gap-2 rounded-full px-6 py-3 text-sm font-semibold text-white transition"
                     style={{ backgroundColor: BRAND }}
                   >
                     Explorer la boutique
                     <ArrowRight className="h-4 w-4" />
                   </motion.a>
                   <motion.a
                     whileHover={{ y: -2 }}
                     whileTap={{ scale: 0.97 }}
                     href={boutiqueLink('/promotions')}
                      className="inline-flex items-center gap-2 rounded-full border border-[color:var(--sf-accent,var(--ds-primary,#111111))] px-6 py-3 text-sm font-semibold text-[color:var(--sf-accent,var(--ds-primary,#111111))] transition hover:bg-[color:var(--sf-accent,var(--ds-primary,#111111))] hover:text-white"
                   >
                     Voir les drops
                   </motion.a>
                 </div>
               </div>
            </motion.div>

            <div className="grid gap-4 lg:grid-rows-[1fr_1fr_auto_auto]">
              {heroCategories.map((category, i) => (
                <motion.div key={category.name} variants={fadeUp} custom={i}>
                  <CategoryAccentCard category={category} />
                </motion.div>
              ))}

              {featuredProduct && (
                <motion.div variants={fadeUp}>
                  <FeaturedProductPanel product={featuredProduct} onAddToCart={handleAddToCart} />
                </motion.div>
              )}

              <div className="rounded-[1.6rem] border border-black/10 bg-white px-6 py-5">
                <div className="text-3xl font-semibold tracking-[-0.05em]">24h</div>
                <div className="mt-1 text-sm text-black/60">Express delivery</div>
              </div>

              <div className="rounded-[1.6rem] border border-black/10 bg-white px-6 py-6">
                <div className="text-lg font-semibold">Join the Collective</div>
                <p className="mt-2 text-sm leading-6 text-black/60">-15% sur votre premiere commande + acces aux drops limites.</p>
                <form className="mt-4 flex gap-2" onSubmit={(event) => event.preventDefault()}>
                  <input
                    type="email"
                    placeholder="Votre email"
                    className="min-w-0 flex-1 rounded-full border border-black/10 bg-[#f8f5ef] px-4 py-3 text-sm outline-none"
                  />
                  <button type="submit" className="rounded-full px-5 py-3 text-sm font-semibold text-white" style={{ backgroundColor: BRAND }}>
                    Rejoindre
                  </button>
                </form>
              </div>
            </div>
          </motion.div>
        </section>

        <FeatureTicker />

        <section id="story" className="px-4 py-14 sm:px-6 lg:px-8">
          <div className="mx-auto grid max-w-7xl gap-10 lg:grid-cols-[260px_minmax(0,1fr)]">
            <div>
              <div className="text-xs uppercase tracking-[0.25em] text-black/45">Explorer</div>
              <h2 className="mt-3 text-3xl font-semibold tracking-[-0.04em]">Nos univers</h2>
            </div>
            <motion.div
              className="grid gap-5 sm:grid-cols-2 xl:grid-cols-5"
              variants={staggerContainer}
              initial="hidden"
              whileInView="show"
              viewport={{ once: true, margin: '-80px' }}
            >
              {collectionCategories.map((category) => (
                <motion.a
                  key={category.name}
                  variants={fadeUp}
                  whileHover={{ y: -4 }}
                  href={boutiqueLink(`/categories/${category.slug}`)}
                  className="group overflow-hidden rounded-[1.8rem] border border-black/10 bg-white"
                >
                  <div className="aspect-[0.9] overflow-hidden bg-[#e7e0d6]">
                    {category.image ? (
                      <ImageWithFallback src={category.image} alt={category.name} className="h-full w-full object-cover transition duration-500 group-hover:scale-105" />
                    ) : (
                      <div className="flex h-full items-center justify-center text-sm text-black/45">{category.name}</div>
                    )}
                  </div>
                  <div className="flex items-center justify-between px-5 py-4">
                    <div>
                      <div className="text-sm font-semibold">{category.name}</div>
                      <div className="mt-1 text-xs text-black/50">{category.count} items</div>
                    </div>
                    <ArrowRight className="h-4 w-4 text-black/40 transition group-hover:translate-x-1 group-hover:text-black" />
                  </div>
                </motion.a>
              ))}
            </motion.div>
          </div>
        </section>

        <section id="catalogue" className="px-4 py-14 sm:px-6 lg:px-8">
          <div className="mx-auto max-w-7xl">
            <SectionHeading eyebrow="Just dropped" title="Nouveautes" href={boutiqueLink('/catalogue')} />
            <motion.div
              className="mt-8 grid gap-6 md:grid-cols-2 xl:grid-cols-4"
              variants={staggerContainer}
              initial="hidden"
              whileInView="show"
              viewport={{ once: true, margin: '-80px' }}
            >
              {newArrivals.map((product) => (
                <motion.div key={product.id} variants={fadeUp}>
                  <ProductEditorialCard product={product} onAddToCart={handleAddToCart} />
                </motion.div>
              ))}
            </motion.div>
          </div>
        </section>

        <section id="drops" className="px-4 pb-14 sm:px-6 lg:px-8" />

        {spotlightProduct && (
          <section className="px-4 py-8 sm:px-6 lg:px-8">
            <div className="mx-auto grid max-w-7xl gap-6 lg:grid-cols-[minmax(0,1fr)_360px]">
              <div className="rounded-[2rem] bg-[#111111] px-8 py-8 text-white sm:px-10 sm:py-10" style={{ backgroundColor: heroColor }}>
                <div className="text-xs uppercase tracking-[0.24em] text-white/55">Offre limitee</div>
                <h3 className="mt-3 text-3xl font-semibold tracking-[-0.04em]">-30% sur la selection</h3>
                <p className="mt-3 max-w-xl text-sm leading-7 text-white/70">
                  Pieces fortes, paniers soignes et une direction visuelle proche du template Nordic que vous avez envoye.
                </p>
                <motion.a
                  whileHover={{ y: -2 }}
                  whileTap={{ scale: 0.97 }}
                  href={boutiqueLink('/promotions')}
                   className="mt-6 inline-flex items-center gap-2 rounded-full border border-[color:var(--sf-accent,var(--ds-primary,#111111))] px-6 py-3 text-sm font-semibold text-[color:var(--sf-accent,var(--ds-primary,#111111))] transition hover:bg-[color:var(--sf-accent,var(--ds-primary,#111111))] hover:text-white"
                >
                  J&apos;en profite
                  <ArrowRight className="h-4 w-4" />
                </motion.a>
              </div>

              <div className="overflow-hidden rounded-[2rem] border border-[color:var(--sf-outline,var(--ds-outline-variant))] bg-[color:var(--sf-surface,var(--ds-surface-container-lowest))]">
                <div className="aspect-[1.05] overflow-hidden bg-[color:var(--sf-surface-muted,var(--ds-surface-container))]">
                  {getProductImage(spotlightProduct) ? (
                     <ImageWithFallback src={getProductImage(spotlightProduct)} alt={spotlightProduct.name} className="h-full w-full object-cover" />
                  ) : (
                    <div className="flex h-full items-center justify-center text-sm text-black/45">Produit</div>
                  )}
                </div>
                <div className="px-6 py-5">
                  <div className="text-xs uppercase tracking-[0.22em] text-black/45">Coup de coeur</div>
                  <div className="mt-2 text-xl font-semibold">{spotlightProduct.name}</div>
                  <div className="mt-3 text-sm text-black/55">{formatPrice(spotlightProduct)}</div>
                  <motion.button
                    whileHover={{ y: -2 }}
                    whileTap={{ scale: 0.96 }}
                    onClick={() => handleAddToCart(spotlightProduct)}
                    className="mt-4 rounded-full px-5 py-3 text-sm font-semibold text-white"
                    style={{ backgroundColor: BRAND }}
                  >
                    Ajouter au panier
                  </motion.button>
                </div>
              </div>
            </div>
          </section>
        )}

        <section className="px-4 py-14 sm:px-6 lg:px-8">
          <div className="mx-auto max-w-7xl">
            <SectionHeading eyebrow="Community picks" title="Best-sellers" href={boutiqueLink('/catalogue')} />
            <motion.div
              className="mt-8 grid gap-6 md:grid-cols-2"
              variants={staggerContainer}
              initial="hidden"
              whileInView="show"
              viewport={{ once: true, margin: '-80px' }}
            >
              {bestSellers.map((product) => (
                <motion.div key={product.id} variants={fadeUp}>
                  <ProductEditorialCard product={product} onAddToCart={handleAddToCart} compact />
                </motion.div>
              ))}
            </motion.div>
          </div>
        </section>

        {reviewsEnabled && <section id="avis" className="px-4 py-14 sm:px-6 lg:px-8">
          <div className="mx-auto max-w-7xl">
            <div className="flex flex-wrap items-end justify-between gap-4">
              <div>
                <div className="text-xs uppercase tracking-[0.24em] text-black/45">Avis clients</div>
                <h2 className="mt-3 text-3xl font-semibold tracking-[-0.04em]">Ce que disent nos clients</h2>
              </div>
              <a href={boutiqueLink('/avis')} className="inline-flex items-center gap-2 text-sm font-semibold text-black/70 transition hover:text-black">
                Voir tous les avis <ArrowRight className="h-4 w-4" />
              </a>
            </div>
            <div className="mt-8 grid gap-5 md:grid-cols-3">
              {reviewsLoading ? <ReviewLoadingCard /> : featuredReviews.length > 0 ? featuredReviews.map((review) => <StorefrontReviewCard key={review.id} review={review} productName={review.productId ? productNames.get(review.productId) : undefined} />) : <EmptyReviewCard />}
            </div>
          </div>
        </section>}

        <section id="contact" className="px-4 py-10 sm:px-6 lg:px-8">
          <div className="mx-auto grid max-w-7xl gap-4 md:grid-cols-2 xl:grid-cols-4">
            <ServiceCard icon={<Truck className="h-5 w-5" />} title="Livraison offerte" description="Des 60 DT d'achat" />
            <ServiceCard icon={<ArrowRight className="h-5 w-5" />} title="Retours 30 jours" description="Satisfait ou rembourse" />
            <ServiceCard icon={<ShieldCheck className="h-5 w-5" />} title="Paiement securise" description="Carte, wallet et paiement livre" />
            <ServiceCard icon={<Mail className="h-5 w-5" />} title="Support 7j/7" description={boutique.email || 'Une equipe dediee'} />
          </div>
        </section>
          </>
          )}
        </main>

      <footer className="mt-10 bg-[#111111] text-white">
        <div className="mx-auto grid max-w-7xl gap-10 px-4 py-14 sm:px-6 lg:grid-cols-[1.3fr_repeat(3,minmax(0,1fr))] lg:px-8">
          <div>
            <div className="flex items-center gap-3">
              <ImageWithFallback src={boutique.logoUrl} alt={boutique.logoUrl ? boutique.name : 'Hanooti'} className="h-11 w-11 rounded-full object-cover" />
              <div>
                <div className="text-lg font-semibold">{boutique.name}</div>
                <div className="text-sm text-white/45">Boutique editoriale</div>
              </div>
            </div>
            <p className="mt-5 max-w-sm text-sm leading-7 text-white/62">
              {boutique.description || 'Design fort, produits choisis et experience inspiree du template Nordic Store.'}
            </p>
          </div>

          <FooterLinks title="Boutique" links={[
            { label: 'Catalogue', href: boutiqueLink('/catalogue') },
            { label: 'Nouveautes', href: boutiqueLink('/catalogue') },
            { label: 'Promotions', href: boutiqueLink('/promotions') },
          ]} />

          <FooterLinks title="Aide" links={[
            { label: 'Contact', href: boutiqueLink('/contact') },
            { label: 'CGV', href: boutiqueLink('') },
            { label: 'Livraison & retours', href: boutiqueLink('/contact') },
          ]} />

          <div>
            <div className="text-sm font-semibold">Newsletter</div>
            <p className="mt-3 text-sm text-white/55">-10% sur votre premiere commande.</p>
            <form className="mt-4 flex gap-2" onSubmit={(event) => event.preventDefault()}>
              <input
                type="email"
                placeholder="Votre email"
                className="min-w-0 flex-1 rounded-full border border-white/12 bg-white/7 px-4 py-3 text-sm text-white outline-none placeholder:text-white/35"
              />
              <button type="submit" className="rounded-full bg-white px-5 py-3 text-sm font-semibold text-[#111111]">
                OK
              </button>
            </form>
          </div>
        </div>
        <div className="border-t border-white/10">
          <div className="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3 px-4 py-5 text-xs text-white/45 sm:px-6 lg:px-8">
            <span>© 2026 {boutique.name}. Tous droits reserves.</span>
            <span>{boutique.address || 'Tunisie'}</span>
          </div>
        </div>
      </footer>
    </div>
  );
}

function TopRibbon({ boutique }: { boutique: StoreBoutique }) {
  return (
    <div className="border-b border-black/8 bg-[#ece5d9] text-[#171717]">
      <div className="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3 px-4 py-2 text-xs sm:px-6 lg:px-8">
        <div className="font-medium">Livraison offerte des 60 DT · Retours 30 jours</div>
        <div className="flex items-center gap-4 text-black/65">
          {boutique.email && (
            <a href={`mailto:${boutique.email}`} className="inline-flex items-center gap-2 hover:text-black">
              <Mail className="h-3.5 w-3.5" />
              {boutique.email}
            </a>
          )}
          <span className="hidden sm:inline">Support 7j/7</span>
        </div>
      </div>
    </div>
  );
}

function ReviewLoadingCard() {
  return <div className="h-48 animate-pulse rounded-[1.5rem] border border-black/10 bg-white/70 md:col-span-3" aria-label="Chargement des avis" />;
}

function EmptyReviewCard() {
  return <div className="rounded-[1.5rem] border border-black/10 bg-white p-8 text-center text-sm text-black/55 md:col-span-3">Aucun avis boutique publié pour le moment.</div>;
}

function StorefrontReviewCard({ review, productName }: { review: StoreReview; productName?: string }) {
  return (
    <article className="rounded-[1.5rem] border border-black/10 bg-white p-6 shadow-sm">
      <div className="flex items-start justify-between gap-3">
        <div className="flex items-center gap-3"><span className="grid h-10 w-10 place-items-center rounded-full bg-black/5 text-sm font-bold">{storeReviewInitial(review.authorName)}</span><div><div className="text-sm font-semibold">{review.authorName}</div><div className="text-xs text-black/45">{productName ? `Avis produit · ${productName}` : 'Avis boutique'}</div></div></div>
        <time className="text-xs text-black/45" dateTime={review.createdAt}>{formatStoreReviewDate(review.createdAt)}</time>
      </div>
      <div className="mt-4 flex items-center gap-1 text-amber-500" aria-label={`${review.rating} étoiles sur 5`}>{[1, 2, 3, 4, 5].map((star) => <Star key={star} className={`h-4 w-4 ${star <= review.rating ? 'fill-current' : ''}`} />)}</div>
      <p className="mt-4 text-sm leading-7 text-black/60">{review.comment ?? 'Avis noté par un client.'}</p>
    </article>
  );
}

function CategoryAccentCard({ category }: { category: StoreCategory }) {
  return (
    <div className="overflow-hidden rounded-[1.6rem] border border-black/10 bg-white">
      <div className="grid grid-cols-[120px_minmax(0,1fr)] items-stretch">
        <div className="aspect-square overflow-hidden bg-[#ddd4c6]">
          {category.image ? (
            <ImageWithFallback src={category.image} alt={category.name} className="h-full w-full object-cover" />
          ) : (
            <div className="flex h-full items-center justify-center text-sm text-black/45">{category.name}</div>
          )}
        </div>
        <div className="flex flex-col justify-center px-5 py-4">
          <div className="text-lg font-semibold">{category.name}</div>
          <div className="mt-1 text-sm text-black/55">{category.count} items</div>
        </div>
      </div>
    </div>
  );
}

function FeaturedProductPanel({ product, onAddToCart }: { product: StoreProduct; onAddToCart: (product: StoreProduct) => void }) {
  return (
    <div className="overflow-hidden rounded-[1.6rem] border border-black/10 bg-white">
      <div className="aspect-[1.15] overflow-hidden bg-[#e6ddcf]">
        {getProductImage(product) ? (
                 <ImageWithFallback src={getProductImage(product)} alt={product.name} className="h-full w-full object-cover" />
        ) : (
          <div className="flex h-full items-center justify-center text-sm text-black/45">Produit</div>
        )}
      </div>
      <div className="px-6 py-5">
        <div className="inline-flex items-center gap-2 rounded-full px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-white" style={{ backgroundColor: BRAND }}>
          Featured
        </div>
        <div className="mt-3 text-xl font-semibold tracking-tight">{product.name}</div>
        <p className="mt-2 text-sm leading-6 text-black/58">{product.description || 'Piece phare de la collection, selectionnee pour incarner le template de reference.'}</p>
        <div className="mt-4 flex items-center justify-between gap-4">
          <div className="text-lg font-semibold">{formatPrice(product)}</div>
          <motion.button
            whileHover={{ y: -2, backgroundColor: BRAND, color: '#ffffff', borderColor: BRAND }}
            whileTap={{ scale: 0.96 }}
            onClick={() => onAddToCart(product)}
            className="rounded-full border border-black/10 px-4 py-2 text-sm font-semibold text-[#111111]"
          >
            Ajouter au panier
          </motion.button>
        </div>
      </div>
    </div>
  );
}

function FeatureTicker() {
  const items = ['◆ Design nordique', '◆ Livraison 24h', '◆ Paiement securise', '◆ Retours gratuits 30j', '◆ Support 7j/7', '◆ Edition limitee'];
  const repeatedItems = [...items, ...items, ...items];

  return (
    <section className="overflow-hidden border-y border-black/10 bg-white py-4" aria-label="Avantages de la boutique">
      <motion.div
        className="flex w-max whitespace-nowrap text-sm text-black/55"
        animate={{ x: ['0%', '-33.333333%'] }}
        transition={{ duration: 24, ease: 'linear', repeat: Infinity }}
      >
        {repeatedItems.map((item, index) => (
          <span key={`${item}-${index}`} className="mx-4 inline-flex items-center sm:mx-6">
            {item}
          </span>
        ))}
      </motion.div>
    </section>
  );
}

function SectionHeading({ eyebrow, title, href }: { eyebrow: string; title: string; href: string }) {
  return (
    <div className="flex flex-wrap items-end justify-between gap-4">
      <div>
        <div className="text-xs uppercase tracking-[0.24em] text-black/45">{eyebrow}</div>
        <h2 className="mt-3 text-3xl font-semibold tracking-[-0.04em]">{title}</h2>
      </div>
      <a href={href} className="inline-flex items-center gap-2 text-sm font-semibold text-black/70 transition hover:text-black">
        Tout voir
        <ArrowRight className="h-4 w-4" />
      </a>
    </div>
  );
}

function ProductEditorialCard({
  product,
  onAddToCart,
  compact = false,
}: {
  product: StoreProduct;
  onAddToCart: (product: StoreProduct) => void;
  compact?: boolean;
}) {
  const badge = resolveBadge(product);
  const image = getProductImage(product);

  return (
    <motion.div
      whileHover={{ y: -6 }}
      transition={{ type: 'spring', stiffness: 300, damping: 24 }}
      className="group overflow-hidden rounded-[1.7rem] border border-black/10 bg-white"
    >
      <div className={`relative overflow-hidden bg-[#e7e0d6] ${compact ? 'aspect-[1.2]' : 'aspect-[0.95]'}`}>
        <a href={boutiqueLink(`/products/${product.slug}`)} className="block h-full">
          {image ? (
             <ImageWithFallback src={image} alt={product.name} className="h-full w-full object-cover transition duration-500 group-hover:scale-105" />
          ) : (
            <div className="flex h-full items-center justify-center text-sm text-black/45">Produit</div>
          )}
          <div
            className="absolute left-4 top-4 rounded-full px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] shadow-sm"
            style={badge === 'Promo' ? { backgroundColor: '#dc2626', color: '#fff' } : { backgroundColor: BRAND, color: '#fff' }}
          >
            {badge}
          </div>
        </a>
        <button
          type="button"
          aria-label={`Ajouter ${product.name} au panier`}
          onClick={(event) => {
            event.preventDefault();
            onAddToCart(product);
          }}
          className="absolute bottom-4 right-4 rounded-full px-4 py-2 text-sm font-semibold text-white opacity-100 transition hover:opacity-90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2"
          style={{ backgroundColor: BRAND }}
        >
          Ajouter
        </button>
      </div>
      <div className="px-5 py-5">
        <div className="text-xs uppercase tracking-[0.2em] text-black/45">{product.categoryName || 'Collection'}</div>
        <a href={boutiqueLink(`/products/${product.slug}`)} className="mt-2 block text-lg font-semibold tracking-tight text-[#171717]">
          {product.name}
        </a>
        <div className="mt-3 flex flex-wrap items-center gap-3 text-sm text-black/55">
          <span className="font-semibold text-[#171717]">{formatPrice(product)}</span>
          {product.comparePriceCents && product.comparePriceCents > product.priceCents && (
            <span className="line-through">{(product.comparePriceCents / 100).toFixed(2)} {product.currency}</span>
          )}
          {product.rating ? <span>★ {product.rating.toFixed(1)}</span> : null}
        </div>
      </div>
    </motion.div>
  );
}

function ServiceCard({ icon, title, description }: { icon: ReactNode; title: string; description: string }) {
  return (
    <div className="rounded-[1.5rem] border border-black/10 bg-white px-6 py-5">
      <div className="inline-flex h-10 w-10 items-center justify-center rounded-full bg-[#f1ebe1] text-[#111111]">{icon}</div>
      <div className="mt-4 text-base font-semibold">{title}</div>
      <div className="mt-1 text-sm text-black/55">{description}</div>
    </div>
  );
}

function FooterLinks({ title, links }: { title: string; links: Array<{ label: string; href: string }> }) {
  return (
    <div>
      <div className="text-sm font-semibold">{title}</div>
      <ul className="mt-4 space-y-3 text-sm text-white/55">
        {links.map((link) => (
          <li key={link.label}>
            <a href={link.href} className="transition hover:text-white">
              {link.label}
            </a>
          </li>
        ))}
      </ul>
    </div>
  );
}

function SearchOverlay({
  query,
  onQuery,
  onClose,
}: {
  query: string;
  onQuery: (value: string) => void;
  onClose: () => void;
}) {
  return (
    <motion.div
      className="fixed inset-0 z-50 bg-[#111111]/40 backdrop-blur-md"
      initial={{ opacity: 0 }}
      animate={{ opacity: 1 }}
      exit={{ opacity: 0 }}
    >
      <motion.div
        className="mx-auto mt-12 max-w-3xl rounded-[2rem] bg-[#f6f2eb] p-6 shadow-2xl sm:mt-20"
        initial={{ opacity: 0, y: -16, scale: 0.98 }}
        animate={{ opacity: 1, y: 0, scale: 1 }}
        exit={{ opacity: 0, y: -10, scale: 0.98 }}
        transition={{ type: 'spring', stiffness: 360, damping: 30 }}
      >
        <div className="flex items-center gap-4 rounded-full border border-black/10 bg-white px-5 py-4">
          <Search className="h-5 w-5 text-black/45" />
          <input
            autoFocus
            value={query}
            onChange={(event) => onQuery(event.target.value)}
            placeholder="Rechercher un produit..."
            className="min-w-0 flex-1 bg-transparent text-base outline-none placeholder:text-black/35"
          />
          <button onClick={onClose} className="rounded-full border border-black/10 p-2 text-black/60">
            <X className="h-4 w-4" />
          </button>
        </div>
      </motion.div>
    </motion.div>
  );
}

function getProductImage(product: StoreProduct): string {
  const firstImage = product.images?.[0];
  if (!firstImage) {
    return '';
  }

  return typeof firstImage === 'string' ? firstImage : firstImage.url || '';
}

function resolveBadge(product: StoreProduct): string {
  if (product.badge) {
    return product.badge;
  }

  if ((product.comparePriceCents ?? 0) > product.priceCents) {
    return 'Promo';
  }

  if ((product.rating ?? 0) >= 4.7) {
    return 'Best-seller';
  }

  return 'Nouveau';
}

function formatPrice(product: StoreProduct): string {
  return `${(product.priceCents / 100).toFixed(2)} ${product.currency}`;
}

function formatHeroTitle(name: string): string {
  const brand = name.toUpperCase();
  return `${brand} FUTURE COLLECTION`;
}

function buildFallbackCategories(products: StoreProduct[]): StoreCategory[] {
  return buildCategories(products).slice(0, 5);
}
