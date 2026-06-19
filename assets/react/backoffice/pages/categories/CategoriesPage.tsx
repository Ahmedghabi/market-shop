import { useState, useCallback } from 'react';
import type { Category } from '../../types';
import { useApiClient, useApiData } from '../../hooks/useApi';
import { Card, CardHeader, CardBody } from '../../components/Card';
import { Button } from '../../components/Button';
import { Table } from '../../components/Table';
import { Badge } from '../../components/Badge';
import { Pagination } from '../../components/Pagination';
import { Modal } from '../../components/Modal';
import { ConfirmDialog } from '../../components/ConfirmDialog';
import { FormField, Input, Select } from '../../components/FormField';
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

export function CategoriesPage({ getAccessToken }: { getAccessToken: () => string | null }) {
  const api = useApiClient(getAccessToken);
  const { showNotice } = useNotification();
  const { boutique } = useBoutique();
  const [page, setPage] = useState(1);
  const [search, setSearch] = useState('');
  const [status, setStatus] = useState('');
  const [modalOpen, setModalOpen] = useState(false);
  const [deleteTarget, setDeleteTarget] = useState<Category | null>(null);
  const [editing, setEditing] = useState<Category | null>(null);
  const [submitting, setSubmitting] = useState(false);

  const [form, setForm] = useState({ boutiqueId: '', name: '', parentId: '', isActive: true });

  const fetchData = useCallback(async () => {
    const params = new URLSearchParams();
    params.set('page', String(page));
    params.set('itemsPerPage', String(PAGE_SIZE));
    if (search) params.set('name', search);
    if (status) params.set('isActive', status === 'active' ? 'true' : 'false');
    return api.getCollection<Category>('/categories?' + params.toString());
  }, [api, page, search, status]);

  const { data, isLoading, error, refresh } = useApiData(fetchData, [page, search, status]);

  const fetchAllCats = useCallback(() => {
    const params = new URLSearchParams();
    if (!boutique?.id && form.boutiqueId) params.set('boutiqueId', form.boutiqueId);

    return api.getCollection<Category>('/categories' + (params.size ? '?' + params.toString() : ''));
  }, [api, boutique?.id, form.boutiqueId]);
  const { data: allCats } = useApiData(fetchAllCats, [boutique?.id, form.boutiqueId]);

  const categories = data?.member ?? [];
  const allCategories = allCats?.member ?? [];
  const totalItems = data?.totalItems ?? 0;
  const totalPages = Math.max(1, Math.ceil(totalItems / PAGE_SIZE));

  function openCreate() {
    setEditing(null);
    setForm({ boutiqueId: '', name: '', parentId: '', isActive: true });
    setModalOpen(true);
  }

  function openEdit(cat: Category) {
    setEditing(cat);
    setForm({ boutiqueId: cat.boutiqueId ?? '', name: cat.name, parentId: '', isActive: cat.isActive });
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
      const body = { boutiqueId, name: form.name, slug: slugify(form.name), parentId: form.parentId || null, isActive: form.isActive };
      if (editing) {
        await api.patch('/categories/' + editing.id, body);
        showNotice('Catégorie mise à jour.', 'success');
      } else {
        await api.post('/categories', body);
        showNotice('Catégorie créée.', 'success');
      }
      setModalOpen(false);
      refresh();
    } catch (err) {
      showNotice(err instanceof Error ? err.message : 'Erreur.', 'error');
    } finally {
      setSubmitting(false);
    }
  }

  async function handleDelete() {
    if (!deleteTarget) return;
    setSubmitting(true);
    try {
      await api.delete('/categories/' + deleteTarget.id);
      showNotice('Catégorie supprimée.', 'success');
      setDeleteTarget(null);
      refresh();
    } catch (err) {
      showNotice(err instanceof Error ? err.message : 'Erreur.', 'error');
    } finally {
      setSubmitting(false);
    }
  }

  const columns = [
    { key: 'name', label: 'Nom', sortable: true, render: (c: Category) => <strong>{c.name}</strong> },
    { key: 'slug', label: 'Slug', render: (c: Category) => <code style={{ fontSize: 12 }}>{c.slug}</code> },
    { key: 'productsCount', label: 'Produits', render: (c: Category) => <Badge tone="neutral">{c.productsCount ?? 0}</Badge> },
    { key: 'isActive', label: 'Statut', render: (c: Category) => <Badge tone={c.isActive ? 'success' : 'neutral'}>{c.isActive ? 'Actif' : 'Inactif'}</Badge> },
  ];

  if (error) return <ErrorState message={error} onRetry={refresh} />;

  return (
    <div>
      <PageHeader title="Catégories" description="Organisez vos produits par catégories" actions={<Button onClick={openCreate}>+ Nouvelle catégorie</Button>} />
      <Card>
        <CardHeader>
          <FiltersBar search={search} onSearchChange={(v) => { setSearch(v); setPage(1); }} status={status} onStatusChange={(v) => { setStatus(v); setPage(1); }} statusOptions={[{ value: 'active', label: 'Actives' }, { value: 'inactive', label: 'Inactives' }]} />
          <span style={{ fontSize: 13, color: 'var(--bo-text-muted)' }}>{totalItems} catégorie{totalItems > 1 ? 's' : ''}</span>
        </CardHeader>
        <CardBody>
          {isLoading ? <LoadingState /> : categories.length === 0 ? (
            <EmptyState title="Aucune catégorie" message="Créez votre première catégorie." action={{ label: '+ Nouvelle catégorie', onClick: openCreate }} />
          ) : (
            <>
              <Table columns={columns} data={categories} onRowClick={openEdit} />
              <Pagination page={page} totalPages={totalPages} onPageChange={setPage} />
            </>
          )}
        </CardBody>
      </Card>

      <Modal isOpen={modalOpen} onClose={() => setModalOpen(false)} title={editing ? 'Modifier' : 'Nouvelle catégorie'} footer={
        <><Button variant="secondary" onClick={() => setModalOpen(false)}>Annuler</Button><Button onClick={handleSubmit} disabled={submitting}>{submitting ? '...' : editing ? 'Mettre à jour' : 'Créer'}</Button></>
      }>
        <form className="bo-form" onSubmit={handleSubmit}>
          <BoutiqueFormSelect value={form.boutiqueId} onChange={(boutiqueId) => setForm((f) => ({ ...f, boutiqueId, parentId: '' }))} />
          <FormField label="Nom" required><Input required value={form.name} onChange={(e) => setForm((f) => ({ ...f, name: e.target.value }))} /></FormField>
          <FormField label="Catégorie parente">
            <Select value={form.parentId} onChange={(e) => setForm((f) => ({ ...f, parentId: e.target.value }))}>
              <option value="">Aucune (racine)</option>
              {allCategories.filter((c) => c.id !== editing?.id).map((c) => <option key={c.id} value={c.id}>{c.name}</option>)}
            </Select>
          </FormField>
          <label className="bo-checkbox"><input type="checkbox" checked={form.isActive} onChange={(e) => setForm((f) => ({ ...f, isActive: e.target.checked }))} /> Active</label>
        </form>
      </Modal>

      <ConfirmDialog isOpen={!!deleteTarget} onClose={() => setDeleteTarget(null)} onConfirm={handleDelete} title="Supprimer" message={`Supprimer "${deleteTarget?.name}" ?`} confirmLabel="Supprimer" danger isLoading={submitting} />
    </div>
  );
}
