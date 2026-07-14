import { useState, useCallback } from 'react';
import type { Product, Category, ProductFilter, NoticeType, SubscriptionSummary } from '../../types';
import { useApiClient, useApiData } from '../../hooks/useApi';
import { Card, CardHeader, CardBody } from '../../components/Card';
import { Button } from '../../components/Button';
import { Table } from '../../components/Table';
import { Badge, statusBadge } from '../../components/Badge';
import { Pagination } from '../../components/Pagination';
import { Modal } from '../../components/Modal';
import { ConfirmDialog } from '../../components/ConfirmDialog';
import { FormField, Input, Select, Textarea } from '../../components/FormField';
import { FiltersBar } from '../../components/FiltersBar';
import { LoadingState, EmptyState, ErrorState } from '../../components/States';
import { PageHeader } from '../../layout/Shell';
import { useNotification } from '../../hooks/useNotification';
import { useBoutique } from '../../hooks/useBoutique';
import { BoutiqueFormSelect, resolveFormBoutiqueId } from '../../components/BoutiqueFormSelect';

const PAGE_SIZE = 20;

function slugify(value: string) {
  return value.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
}

export function ProductsPage({ getAccessToken }: { getAccessToken: () => string | null }) {
  const api = useApiClient(getAccessToken);
  const { showNotice } = useNotification();
  const { boutique } = useBoutique();
  const [page, setPage] = useState(1);
  const [search, setSearch] = useState('');
  const [status, setStatus] = useState('');
  const [sortField, setSortField] = useState('createdAt');
  const [sortDir, setSortDir] = useState<'asc' | 'desc'>('desc');
  const [modalOpen, setModalOpen] = useState(false);
  const [deleteTarget, setDeleteTarget] = useState<Product | null>(null);
  const [editingProduct, setEditingProduct] = useState<Product | null>(null);
  const [submitting, setSubmitting] = useState(false);

  const [form, setForm] = useState({
    boutiqueId: '',
    name: '', sku: '', description: '', priceCents: 0, comparePriceCents: 0,
    stockQuantity: 0, lowStockThreshold: 5, categoryId: '', isActive: true, isFeatured: false,
  });

  const fetchProducts = useCallback(async () => {
    const params = new URLSearchParams();
    params.set('page', String(page));
    params.set('itemsPerPage', String(PAGE_SIZE));
    params.set('order[' + sortField + ']', sortDir);
    if (search) params.set('name', search);
    if (status) params.set('isActive', status === 'active' ? 'true' : 'false');
    return api.getCollection<Product>('/products?' + params.toString());
  }, [api, page, search, status, sortField, sortDir]);

  const { data, isLoading, error, refresh } = useApiData(fetchProducts, [page, search, status, sortField, sortDir]);

  const fetchSummary = useCallback(async () => {
    return api.get<SubscriptionSummary>('/subscription/summary');
  }, [api]);
  const { data: summary, refresh: refreshSummary } = useApiData(fetchSummary, [boutique?.id]);

  const productQuota = summary?.quotas?.find((q) => q.code === 'max_products');
  const atQuota = !!productQuota && productQuota.remaining !== null && productQuota.remaining <= 0;

  const fetchCategories = useCallback(() => {
    const params = new URLSearchParams();
    if (!boutique?.id && form.boutiqueId) params.set('boutiqueId', form.boutiqueId);

    return api.getCollection<Category>('/categories' + (params.size ? '?' + params.toString() : ''));
  }, [api, boutique?.id, form.boutiqueId]);
  const { data: catsData } = useApiData(fetchCategories, [boutique?.id, form.boutiqueId]);

  const categories = catsData?.member ?? [];
  const products = data?.member ?? [];
  const totalItems = data?.totalItems ?? 0;
  const totalPages = Math.max(1, Math.ceil(totalItems / PAGE_SIZE));

  function openCreate() {
    setEditingProduct(null);
    setForm({ boutiqueId: '', name: '', sku: '', description: '', priceCents: 0, comparePriceCents: 0, stockQuantity: 0, lowStockThreshold: 5, categoryId: '', isActive: !atQuota, isFeatured: false });
    setModalOpen(true);
  }

  function openEdit(product: Product) {
    setEditingProduct(product);
    setForm({
      boutiqueId: product.boutiqueId ?? '',
      name: product.name,
      sku: product.sku ?? '',
      description: product.description ?? '',
      priceCents: product.sellingPrice ?? product.priceCents ?? 0,
      comparePriceCents: product.comparePrice ?? product.comparePriceCents ?? 0,
      stockQuantity: product.stockQuantity,
      lowStockThreshold: product.lowStockThreshold,
      categoryId: product.categoryId ?? '',
      isActive: product.status === 'ACTIVE',
      isFeatured: product.isFeatured,
    });
    setModalOpen(true);
  }

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    const boutiqueId = resolveFormBoutiqueId(boutique?.id, form.boutiqueId);
    if (!boutiqueId) {
      showNotice('Sélectionnez une boutique.', 'error');
      return;
    }
    setSubmitting(true);
    try {
      const body = {
        boutiqueId,
        name: form.name,
        slug: slugify(form.name),
        sku: form.sku || slugify(form.name),
        description: form.description || null,
        sellingPrice: form.priceCents,
        comparePrice: form.comparePriceCents || 0,
        currency: 'TND',
        status: form.isActive ? 'ACTIVE' : 'INACTIVE',
        stockQuantity: form.stockQuantity,
        lowStockThreshold: form.lowStockThreshold,
        categoryId: form.categoryId || null,
        isFeatured: form.isFeatured,
      };

      if (editingProduct) {
        await api.patch('/products/' + editingProduct.id, body);
        showNotice('Produit mis à jour.', 'success');
      } else {
        await api.post('/products', body);
        showNotice('Produit créé.', 'success');
      }
      setModalOpen(false);
      refresh();
      refreshSummary();
    } catch (err) {
      showNotice(err instanceof Error ? err.message : 'Erreur lors de la sauvegarde.', 'error');
    } finally {
      setSubmitting(false);
    }
  }

  async function handleDelete() {
    if (!deleteTarget) return;
    setSubmitting(true);
    try {
      await api.delete('/products/' + deleteTarget.id);
      showNotice('Produit supprimé.', 'success');
      setDeleteTarget(null);
      refresh();
      refreshSummary();
    } catch (err) {
      showNotice(err instanceof Error ? err.message : 'Erreur lors de la suppression.', 'error');
    } finally {
      setSubmitting(false);
    }
  }

  function handleSort(field: string) {
    if (sortField === field) {
      setSortDir((d) => d === 'asc' ? 'desc' : 'asc');
    } else {
      setSortField(field);
      setSortDir('asc');
    }
  }

  const columns = [
    {
      key: 'name', label: 'Produit', sortable: true,
      render: (p: Product) => (
        <div>
          <strong style={{ fontSize: 14 }}>{p.name}</strong>
          <div style={{ fontSize: 12, color: 'var(--bo-text-muted)' }}>{p.sku ?? '-'}</div>
        </div>
      ),
    },
    {
      key: 'categoryName', label: 'Catégorie',
      render: (p: Product) => <span style={{ color: 'var(--bo-text-secondary)' }}>{p.categoryName ?? '-'}</span>,
    },
    {
      key: 'priceCents', label: 'Prix', sortable: true,
      render: (p: Product) => <strong>{((p.sellingPrice ?? p.priceCents ?? 0) / 100).toFixed(2)} TND</strong>,
    },
    {
      key: 'stockQuantity', label: 'Stock', sortable: true,
      render: (p: Product) => {
        const low = p.stockQuantity <= p.lowStockThreshold;
        return <span style={{ color: low ? 'var(--bo-warning)' : 'inherit', fontWeight: low ? 600 : 400 }}>{p.stockQuantity}</span>;
      },
    },
    {
      key: 'viewsCount', label: 'Vues', sortable: true,
      render: (p: Product) => <strong>{p.viewsCount ?? 0}</strong>,
    },
    {
      key: 'isActive', label: 'Statut',
      render: (p: Product) => {
        const active = p.status === 'ACTIVE';
        return <Badge tone={active ? 'success' : 'neutral'}>{active ? 'Actif' : p.status === 'DRAFT' ? 'Brouillon' : 'Inactif'}</Badge>;
      },
    },
  ];

  if (error) return <ErrorState message={error} onRetry={refresh} />;

  return (
    <div>
      <PageHeader
        title="Produits"
        description="Gérez votre catalogue de produits"
        actions={<Button onClick={openCreate}>+ Nouveau produit</Button>}
      />

        <Card>
        {productQuota && (
          <div style={{
            padding: '10px 20px', background: 'var(--bo-surface)', borderBottom: '1px solid var(--bo-border)',
            fontSize: 13, color: 'var(--bo-text-secondary)', display: 'flex', gap: 16, alignItems: 'center',
          }}>
            <span>Produits actifs : <strong>{productQuota.usage}</strong> / {productQuota.limit ?? '∞'}</span>
            {productQuota.remaining !== null && (
              <span style={{ color: productQuota.remaining <= 0 ? 'var(--bo-warning)' : 'var(--bo-success)' }}>
                ({productQuota.remaining <= 0 ? 'quota atteint' : productQuota.remaining + ' restant' + (productQuota.remaining > 1 ? 's' : '')})
              </span>
            )}
            {productQuota.limit === null && <span style={{ color: 'var(--bo-success)' }}>Illimité</span>}
          </div>
        )}
        <CardHeader>
          <FiltersBar
            search={search}
            onSearchChange={(v) => { setSearch(v); setPage(1); }}
            status={status}
            onStatusChange={(v) => { setStatus(v); setPage(1); }}
            statusOptions={[
              { value: 'active', label: 'Actifs' },
              { value: 'inactive', label: 'Inactifs' },
            ]}
          />
          <span style={{ fontSize: 13, color: 'var(--bo-text-muted)' }}>
            {totalItems} produit{totalItems > 1 ? 's' : ''}
          </span>
        </CardHeader>
        <CardBody>
          {isLoading ? (
            <LoadingState />
          ) : products.length === 0 ? (
            <EmptyState
              title="Aucun produit"
              message="Commencez par ajouter votre premier produit."
              action={{ label: '+ Nouveau produit', onClick: openCreate }}
            />
          ) : (
            <Table
              columns={columns}
              data={products}
              sort={{ field: sortField, direction: sortDir }}
              onSort={handleSort}
              onRowClick={openEdit}
            />
          )}
        </CardBody>
        <div style={{ padding: '0 20px 16px' }}>
          <Pagination page={page} totalPages={totalPages} onPageChange={setPage} />
        </div>
      </Card>

      <Modal
        isOpen={modalOpen}
        onClose={() => setModalOpen(false)}
        title={editingProduct ? 'Modifier le produit' : 'Nouveau produit'}
        width="640px"
        footer={
          <>
            <Button variant="secondary" onClick={() => setModalOpen(false)}>Annuler</Button>
            <Button onClick={handleSubmit} disabled={submitting}>
              {submitting ? 'Enregistrement...' : editingProduct ? 'Mettre à jour' : 'Créer'}
            </Button>
          </>
        }
      >
        <form className="bo-form" onSubmit={handleSubmit}>
          <BoutiqueFormSelect value={form.boutiqueId} onChange={(boutiqueId) => setForm((f) => ({ ...f, boutiqueId, categoryId: '' }))} />
          <div className="bo-form-row">
            <FormField label="Nom" required>
              <Input required value={form.name} onChange={(e) => setForm((f) => ({ ...f, name: e.target.value }))} />
            </FormField>
            <FormField label="SKU">
              <Input value={form.sku} onChange={(e) => setForm((f) => ({ ...f, sku: e.target.value }))} />
            </FormField>
          </div>
          <FormField label="Description">
            <Textarea value={form.description} onChange={(e) => setForm((f) => ({ ...f, description: e.target.value }))} />
          </FormField>
          <div className="bo-form-row">
            <FormField label="Prix (centimes)" required hint="En centimes (ex: 2999 = 29.99 TND)">
              <Input type="number" min={0} required value={form.priceCents} onChange={(e) => setForm((f) => ({ ...f, priceCents: Number(e.target.value) }))} />
            </FormField>
            <FormField label="Prix comparatif">
              <Input type="number" min={0} value={form.comparePriceCents} onChange={(e) => setForm((f) => ({ ...f, comparePriceCents: Number(e.target.value) }))} />
            </FormField>
          </div>
          <div className="bo-form-row">
            <FormField label="Stock" required>
              <Input type="number" min={0} required value={form.stockQuantity} onChange={(e) => setForm((f) => ({ ...f, stockQuantity: Number(e.target.value) }))} />
            </FormField>
            <FormField label="Seuil stock bas">
              <Input type="number" min={0} value={form.lowStockThreshold} onChange={(e) => setForm((f) => ({ ...f, lowStockThreshold: Number(e.target.value) }))} />
            </FormField>
          </div>
          <FormField label="Catégorie">
            <Select value={form.categoryId} onChange={(e) => setForm((f) => ({ ...f, categoryId: e.target.value }))}>
              <option value="">Sans catégorie</option>
              {categories.map((cat) => (
                <option key={cat.id} value={cat.id}>{cat.name}</option>
              ))}
            </Select>
          </FormField>
          <div style={{ display: 'flex', gap: 20 }}>
            <label className="bo-checkbox" style={atQuota && !form.isActive ? { opacity: 0.5, cursor: 'not-allowed' } : {}}>
              <input type="checkbox" checked={form.isActive}
                disabled={!!(atQuota && !form.isActive)}
                onChange={(e) => setForm((f) => ({ ...f, isActive: e.target.checked }))} />
              Actif
              {atQuota && <span style={{ marginLeft: 8, color: 'var(--bo-warning)', fontSize: 12 }}>(quota atteint)</span>}
            </label>
            <label className="bo-checkbox">
              <input type="checkbox" checked={form.isFeatured} onChange={(e) => setForm((f) => ({ ...f, isFeatured: e.target.checked }))} />
              Mis en avant
            </label>
          </div>
        </form>
      </Modal>

      <ConfirmDialog
        isOpen={!!deleteTarget}
        onClose={() => setDeleteTarget(null)}
        onConfirm={handleDelete}
        title="Supprimer le produit"
        message={`Êtes-vous sûr de vouloir supprimer "${deleteTarget?.name}" ? Cette action est irréversible.`}
        confirmLabel="Supprimer"
        danger
        isLoading={submitting}
      />
    </div>
  );
}
