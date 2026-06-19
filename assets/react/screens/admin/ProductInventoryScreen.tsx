import { useEffect, useState, type FormEvent } from 'react';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { appIcons } from '../../icons/fontAwesome';
import { Badge, Button, Card, Input, Select, Textarea } from '../../components/ui';

type BoutiqueOption = { id: string; name: string };
type Category = { id: string; name: string; slug: string; productsCount?: number };
type ProductFilter = { id: string; name: string; slug: string; type: string; active: boolean };
type Product = { id: string; name: string; slug: string; sku?: string | null; priceCents: number; currency: string; stockQuantity: number; lowStockThreshold: number; categoryName?: string | null; active: boolean; filterValues?: Array<{ filterName: string; value: string }> };

function slugify(value: string) {
  return value.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
}

export function ProductInventoryScreen({ boutique, getAccessToken, onNotice }: { boutique?: BoutiqueOption; getAccessToken?: () => string | null; onNotice?: (notice: string) => void }) {
  const [products, setProducts] = useState<Product[]>([]);
  const [categories, setCategories] = useState<Category[]>([]);
  const [filters, setFilters] = useState<ProductFilter[]>([]);
  const [categoryForm, setCategoryForm] = useState({ name: '' });
  const [filterForm, setFilterForm] = useState({ name: '', type: 'select' });
  const [productForm, setProductForm] = useState({ name: '', sku: '', description: '', priceDt: 0, stockQuantity: 0, lowStockThreshold: 5, categoryId: '', active: true });
  const [filterValues, setFilterValues] = useState<Record<string, string>>({});
  const [isLoading, setIsLoading] = useState(false);

  async function loadCatalog() {
    const token = getAccessToken?.();
    if (!token || !boutique) return;
    setIsLoading(true);
    try {
      const [productsResponse, categoriesResponse, filtersResponse] = await Promise.all([
        fetch(`/api/boutiques/${boutique.id}/products`, { headers: { Authorization: `Bearer ${token}` } }),
        fetch(`/api/boutiques/${boutique.id}/categories`, { headers: { Authorization: `Bearer ${token}` } }),
        fetch(`/api/boutiques/${boutique.id}/filters`, { headers: { Authorization: `Bearer ${token}` } }),
      ]);
      const productsPayload = productsResponse.ok ? await productsResponse.json() : [];
      const categoriesPayload = categoriesResponse.ok ? await categoriesResponse.json() : [];
      const filtersPayload = filtersResponse.ok ? await filtersResponse.json() : [];
      setProducts(Array.isArray(productsPayload) ? productsPayload : productsPayload.member ?? productsPayload.items ?? []);
      setCategories(Array.isArray(categoriesPayload) ? categoriesPayload : categoriesPayload.member ?? categoriesPayload.items ?? []);
      setFilters(Array.isArray(filtersPayload) ? filtersPayload : filtersPayload.member ?? filtersPayload.items ?? []);
    } finally {
      setIsLoading(false);
    }
  }

  useEffect(() => { void loadCatalog(); }, [boutique?.id, getAccessToken]);

  async function createCategory(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    const token = getAccessToken?.();
    if (!token || !boutique) return;
    const slug = slugify(categoryForm.name);
    const response = await fetch(`/api/boutiques/${boutique.id}/categories`, { method: 'POST', headers: { Authorization: `Bearer ${token}`, 'Content-Type': 'application/json' }, body: JSON.stringify({ name: categoryForm.name, slug, active: true }) });
    onNotice?.(response.ok ? 'Catégorie créée.' : `Création catégorie impossible (${response.status}).`);
    if (response.ok) { setCategoryForm({ name: '' }); await loadCatalog(); }
  }

  async function createFilter(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    const token = getAccessToken?.();
    if (!token || !boutique) return;
    const response = await fetch(`/api/boutiques/${boutique.id}/filters`, { method: 'POST', headers: { Authorization: `Bearer ${token}`, 'Content-Type': 'application/json' }, body: JSON.stringify({ name: filterForm.name, slug: slugify(filterForm.name), type: filterForm.type, position: filters.length + 1, active: true }) });
    onNotice?.(response.ok ? 'Filtre créé.' : `Création filtre impossible (${response.status}).`);
    if (response.ok) { setFilterForm({ name: '', type: 'select' }); await loadCatalog(); }
  }

  async function createProduct(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    const token = getAccessToken?.();
    if (!token || !boutique) return;
    const response = await fetch(`/api/boutiques/${boutique.id}/products`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${token}`, 'Content-Type': 'application/json' },
      body: JSON.stringify({
        name: productForm.name,
        slug: slugify(productForm.name),
        sku: productForm.sku || slugify(productForm.name),
        description: productForm.description || null,
        priceCents: Math.round(Number(productForm.priceDt) * 100),
        currency: 'TND',
        stockQuantity: Number(productForm.stockQuantity),
        lowStockThreshold: Number(productForm.lowStockThreshold),
        categoryId: productForm.categoryId || null,
        active: productForm.active,
        filterValues,
      }),
    });
    onNotice?.(response.ok ? 'Produit créé.' : `Création produit impossible (${response.status}).`);
    if (response.ok) {
      setProductForm({ name: '', sku: '', description: '', priceDt: 0, stockQuantity: 0, lowStockThreshold: 5, categoryId: '', active: true });
      setFilterValues({});
      await loadCatalog();
    }
  }

  async function deleteProduct(id: string) {
    const token = getAccessToken?.();
    if (!token || !boutique) return;
    const response = await fetch(`/api/boutiques/${boutique.id}/products/${id}`, { method: 'DELETE', headers: { Authorization: `Bearer ${token}` } });
    onNotice?.(response.ok ? 'Produit supprimé.' : `Suppression impossible (${response.status}).`);
    if (response.ok) await loadCatalog();
  }

  if (!boutique) {
    return <Card><h2 className="text-xl font-bold">Produits indisponibles</h2><p className="mt-2 text-sm text-[color:var(--ds-on-surface-variant)]">Aucune boutique associée.</p></Card>;
  }

  return (
    <section className="space-y-6">
      <Card className="ds-hero">
        <div className="flex flex-wrap items-center justify-between gap-4"><div><p className="ds-hero__eyebrow">Catalogue</p><h1 className="ds-hero__title">Produits de {boutique.name}</h1><p className="ds-hero__subtitle">Ajouter les produits, catégories et filtres taille/couleur pour cette boutique.</p></div><div className="flex gap-2"><Badge tone="success">{products.length} produits</Badge><Badge tone="neutral">{categories.length} catégories</Badge><Badge tone="warning">{filters.length} filtres</Badge></div></div>
      </Card>

      <div className="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
        <Card>
          <h2 className="text-xl font-bold">Ajouter un produit</h2>
          <form className="mt-4 grid gap-4" onSubmit={createProduct}>
            <div className="grid gap-4 md:grid-cols-2"><Input required placeholder="Nom du produit" value={productForm.name} onChange={(e) => setProductForm((c) => ({ ...c, name: e.target.value }))} /><Input placeholder="SKU" value={productForm.sku} onChange={(e) => setProductForm((c) => ({ ...c, sku: e.target.value }))} /></div>
            <Textarea placeholder="Description" value={productForm.description} onChange={(e) => setProductForm((c) => ({ ...c, description: e.target.value }))} />
            <div className="grid gap-4 md:grid-cols-3"><Input type="number" min={0} step="0.01" placeholder="Prix DT" value={productForm.priceDt} onChange={(e) => setProductForm((c) => ({ ...c, priceDt: Number(e.target.value) }))} /><Input type="number" min={0} placeholder="Stock" value={productForm.stockQuantity} onChange={(e) => setProductForm((c) => ({ ...c, stockQuantity: Number(e.target.value) }))} /><Input type="number" min={0} placeholder="Seuil stock bas" value={productForm.lowStockThreshold} onChange={(e) => setProductForm((c) => ({ ...c, lowStockThreshold: Number(e.target.value) }))} /></div>
            <Select value={productForm.categoryId} onChange={(e) => setProductForm((c) => ({ ...c, categoryId: e.target.value }))}><option value="">Sans catégorie</option>{categories.map((category) => <option key={category.id} value={category.id}>{category.name}</option>)}</Select>
            {filters.length > 0 && <div className="grid gap-4 md:grid-cols-2">{filters.map((filter) => <Input key={filter.id} placeholder={`${filter.name} (ex: M, Rouge)`} value={filterValues[filter.id] ?? ''} onChange={(e) => setFilterValues((current) => ({ ...current, [filter.id]: e.target.value }))} />)}</div>}
            <label className="flex items-center gap-2 text-sm"><input type="checkbox" checked={productForm.active} onChange={(e) => setProductForm((c) => ({ ...c, active: e.target.checked }))} /> Produit actif</label>
            <Button type="submit" variant="primary"><FontAwesomeIcon icon={appIcons.plus} /> Ajouter le produit</Button>
          </form>
        </Card>

        <div className="space-y-6">
          <Card><h2 className="text-xl font-bold">Catégories</h2><form className="mt-4 flex gap-3" onSubmit={createCategory}><Input required placeholder="Nom catégorie" value={categoryForm.name} onChange={(e) => setCategoryForm({ name: e.target.value })} /><Button type="submit" variant="secondary">Ajouter</Button></form><div className="mt-4 flex flex-wrap gap-2">{categories.map((category) => <Badge key={category.id} tone="neutral">{category.name}</Badge>)}</div></Card>
          <Card><h2 className="text-xl font-bold">Filtres</h2><form className="mt-4 grid gap-3" onSubmit={createFilter}><Input required placeholder="Nom filtre (Taille, Couleur...)" value={filterForm.name} onChange={(e) => setFilterForm((c) => ({ ...c, name: e.target.value }))} /><Select value={filterForm.type} onChange={(e) => setFilterForm((c) => ({ ...c, type: e.target.value }))}><option value="select">Liste</option><option value="color">Couleur</option><option value="text">Texte</option></Select><Button type="submit" variant="secondary">Ajouter filtre</Button></form><div className="mt-4 flex flex-wrap gap-2">{filters.map((filter) => <Badge key={filter.id} tone="neutral">{filter.name}</Badge>)}</div></Card>
        </div>
      </div>

      <Card>
        <div className="flex items-center justify-between"><h2 className="text-xl font-bold">Produits existants</h2><Badge tone="neutral">{isLoading ? 'Chargement' : `${products.length} produits`}</Badge></div>
        <div className="mt-5 overflow-x-auto"><table className="ds-table"><thead><tr><th>Produit</th><th>Catégorie</th><th>Prix</th><th>Stock</th><th>Filtres</th><th>Statut</th><th>Actions</th></tr></thead><tbody>{products.map((product) => <tr key={product.id}><td><strong>{product.name}</strong><p className="text-xs text-[color:var(--ds-on-surface-variant)]">{product.sku}</p></td><td>{product.categoryName ?? '-'}</td><td>{(product.priceCents / 100).toFixed(2)} {product.currency}</td><td>{product.stockQuantity}</td><td>{product.filterValues?.map((value) => `${value.filterName}: ${value.value}`).join(', ') || '-'}</td><td><Badge tone={product.active ? 'success' : 'neutral'}>{product.active ? 'Actif' : 'Inactif'}</Badge></td><td><Button variant="ghost" onClick={() => deleteProduct(product.id)}>Supprimer</Button></td></tr>)}</tbody></table>{!isLoading && products.length === 0 && <p className="mt-4 text-sm text-[color:var(--ds-on-surface-variant)]">Aucun produit pour cette boutique.</p>}</div>
      </Card>
    </section>
  );
}
