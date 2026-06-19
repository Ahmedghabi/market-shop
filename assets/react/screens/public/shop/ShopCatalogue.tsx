import { useState } from 'react';
import { ShopLayout, CartIcon } from './ShopLayout';

interface Product {
  id: string;
  name: string;
  shop: string;
  price: number;
  originalPrice?: number;
  badge?: string;
  rating: number;
  reviews: number;
  category: string;
}

const allProducts: Product[] = [
  { id: '1', name: 'Sac en cuir tannage végétal', shop: "L'Atelier Cuir", price: 18900, originalPrice: 22000, badge: '-14%', rating: 4.8, reviews: 127, category: 'Mode' },
  { id: '2', name: 'Casque sans fil Pro X2', shop: 'TechDirect B2B', price: 24900, badge: 'Nouveau', rating: 4.6, reviews: 89, category: 'High-Tech' },
  { id: '3', name: "Coffret huile d'olive AOP", shop: 'Gourmet Select', price: 6200, rating: 4.9, reviews: 203, category: 'Alimentation' },
  { id: '4', name: 'Lampe sculpturale chêne', shop: 'Design & Co', price: 32000, badge: 'Nouveau', rating: 4.7, reviews: 156, category: 'Décoration' },
  { id: '5', name: 'Montre connectée ProFit', shop: 'TechDirect B2B', price: 17900, originalPrice: 19900, badge: '-10%', rating: 4.5, reviews: 64, category: 'High-Tech' },
  { id: '6', name: 'Set de couteaux professionnel', shop: 'Gourmet Select', price: 8900, rating: 4.4, reviews: 42, category: 'Alimentation' },
  { id: '7', name: 'Vase en céramique artisanale', shop: "L'Atelier Cuir", price: 14500, badge: 'Édition limitée', rating: 4.9, reviews: 31, category: 'Décoration' },
  { id: '8', name: 'Chaise design scandinave', shop: 'Design & Co', price: 45000, originalPrice: 52000, badge: '-13%', rating: 4.3, reviews: 78, category: 'Décoration' },
  { id: '9', name: 'Enceinte Bluetooth nomade', shop: 'TechDirect B2B', price: 8900, rating: 4.2, reviews: 55, category: 'High-Tech' },
];

const categories = ['Tout', 'High-Tech', 'Mode', 'Alimentation', 'Décoration'];

function StarIcon() {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" stroke="none">
      <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
    </svg>
  );
}

function HeartIcon() {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
    </svg>
  );
}

function SearchIcon() {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
    </svg>
  );
}

function formatPrice(cents: number): string {
  return (cents / 100).toFixed(2).replace('.', ',') + ' €';
}

const priceRanges = [
  { label: '0 € - 50 €', min: 0, max: 5000 },
  { label: '50 € - 100 €', min: 5000, max: 10000 },
  { label: '100 € - 200 €', min: 10000, max: 20000 },
  { label: '200 € - 500 €', min: 20000, max: 50000 },
  { label: '500 € +', min: 50000, max: Infinity },
];

export function ShopCatalogue() {
  const [search, setSearch] = useState('');
  const [selectedCategory, setSelectedCategory] = useState('Tout');
  const [priceFilter, setPriceFilter] = useState<{ min: number; max: number } | null>(null);
  const [sortBy, setSortBy] = useState('popular');
  const [page, setPage] = useState(1);
  const perPage = 6;

  const filtered = allProducts.filter((p) => {
    if (selectedCategory !== 'Tout' && p.category !== selectedCategory) return false;
    if (priceFilter && (p.price < priceFilter.min || p.price >= priceFilter.max)) return false;
    if (search && !p.name.toLowerCase().includes(search.toLowerCase()) && !p.shop.toLowerCase().includes(search.toLowerCase())) return false;
    return true;
  });

  const sorted = [...filtered].sort((a, b) => {
    if (sortBy === 'price-asc') return a.price - b.price;
    if (sortBy === 'price-desc') return b.price - a.price;
    if (sortBy === 'rating') return b.rating - a.rating;
    return b.reviews - a.reviews;
  });

  const totalPages = Math.ceil(sorted.length / perPage);
  const paginated = sorted.slice((page - 1) * perPage, page * perPage);

  return (
    <ShopLayout activePath="/shop/catalogue">
      <section className="shop-section shop-section--alt">
        <div className="shop-section__inner">
          <div className="shop-section__header">
            <h2>Catalogue</h2>
            <p>Découvrez tous nos produits disponibles sur la plateforme.</p>
          </div>

          {/* Search */}
          <div className="shop-search">
            <SearchIcon />
            <input
              type="search"
              placeholder="Rechercher un produit, une boutique..."
              value={search}
              onChange={(e) => { setSearch(e.target.value); setPage(1); }}
            />
          </div>

          {/* Category Pills */}
          <div className="shop-category-pills">
            {categories.map((cat) => (
              <button
                key={cat}
                className={`shop-category-pill ${selectedCategory === cat ? 'shop-category-pill--active' : ''}`}
                onClick={() => { setSelectedCategory(cat); setPage(1); }}
              >
                {cat}
              </button>
            ))}
          </div>

          <div className="shop-catalogue-layout">
            {/* Sidebar */}
            <aside className="shop-catalogue-sidebar">
              <div className="shop-filter-group">
                <h4>Prix</h4>
                {priceRanges.map((range) => (
                  <label key={range.label}>
                    <input
                      type="checkbox"
                      checked={priceFilter?.min === range.min && priceFilter?.max === range.max}
                      onChange={() => {
                        setPriceFilter(priceFilter?.min === range.min ? null : { min: range.min, max: range.max });
                        setPage(1);
                      }}
                    />
                    {range.label}
                  </label>
                ))}
              </div>
              {priceFilter && (
                <button className="shop-btn shop-btn--ghost shop-btn--sm" onClick={() => { setPriceFilter(null); setPage(1); }}>
                  Effacer les filtres
                </button>
              )}
            </aside>

            {/* Main content */}
            <div>
              <div className="shop-catalogue-header">
                <div>
                  <h2>{selectedCategory === 'Tout' ? 'Tous les produits' : selectedCategory}</h2>
                  <span className="shop-catalogue-header__results">{sorted.length} résultat(s)</span>
                </div>
                <div className="shop-catalogue-sort">
                  Trier par :
                  <select value={sortBy} onChange={(e) => setSortBy(e.target.value)}>
                    <option value="popular">Popularité</option>
                    <option value="rating">Meilleures notes</option>
                    <option value="price-asc">Prix croissant</option>
                    <option value="price-desc">Prix décroissant</option>
                  </select>
                </div>
              </div>

              {paginated.length === 0 ? (
                <div className="shop-empty">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                  <h3>Aucun résultat</h3>
                  <p>Essayez de modifier vos filtres ou votre recherche.</p>
                </div>
              ) : (
                <>
                  <div className="shop-catalogue-grid">
                    {paginated.map((product) => (
                      <article className="shop-catalogue-item" key={product.id}>
                        <div className="shop-catalogue-item__media">
                          {product.badge && <span className="shop-catalogue-item__badge">{product.badge}</span>}
                          <button className="shop-catalogue-item__fav" type="button" aria-label="Ajouter aux favoris">
                            <HeartIcon />
                          </button>
                          <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round">
                            <path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/>
                          </svg>
                        </div>
                        <div className="shop-catalogue-item__body">
                          <p className="shop-card__shop">{product.shop}</p>
                          <h3>{product.name}</h3>
                          <div className="shop-rating">
                            <StarIcon />
                            <strong>{product.rating}</strong>
                            ({product.reviews} avis)
                          </div>
                          <div className="shop-catalogue-item__price">
                            <strong>{formatPrice(product.price)}</strong>
                            {product.originalPrice && <s>{formatPrice(product.originalPrice)}</s>}
                          </div>
                          <div className="shop-catalogue-item__actions">
                            <button className="shop-btn shop-btn--primary shop-btn--sm">
                              <CartIcon /> Ajouter
                            </button>
                            <button className="shop-btn shop-btn--outline shop-btn--sm">Détail</button>
                          </div>
                        </div>
                      </article>
                    ))}
                  </div>

                  {totalPages > 1 && (
                    <div className="shop-pagination">
                      <button disabled={page === 1} onClick={() => setPage(page - 1)}>&laquo;</button>
                      {Array.from({ length: totalPages }, (_, i) => i + 1).map((p) => (
                        <button key={p} className={p === page ? 'active' : ''} onClick={() => setPage(p)}>{p}</button>
                      ))}
                      <button disabled={page === totalPages} onClick={() => setPage(page + 1)}>&raquo;</button>
                    </div>
                  )}
                </>
              )}
            </div>
          </div>
        </div>
      </section>
    </ShopLayout>
  );
}
