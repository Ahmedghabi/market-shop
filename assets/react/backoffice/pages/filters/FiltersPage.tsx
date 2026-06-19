import { useState, useCallback } from 'react';
import type { ProductFilter } from '../../types';
import { useApiClient, useApiData } from '../../hooks/useApi';
import { Card, CardHeader, CardBody } from '../../components/Card';
import { Button } from '../../components/Button';
import { Table } from '../../components/Table';
import { Badge } from '../../components/Badge';
import { Pagination } from '../../components/Pagination';
import { Modal } from '../../components/Modal';
import { ConfirmDialog } from '../../components/ConfirmDialog';
import { FormField, Input, Select, Textarea } from '../../components/FormField';
import { LoadingState, EmptyState, ErrorState } from '../../components/States';
import { PageHeader } from '../../layout/Shell';
import { useNotification } from '../../hooks/useNotification';
import { useBoutique } from '../../hooks/useBoutique';
import { BoutiqueFormSelect, resolveFormBoutiqueId } from '../../components/BoutiqueFormSelect';

const PAGE_SIZE = 20;

export function FiltersPage({ getAccessToken }: { getAccessToken: () => string | null }) {
  const api = useApiClient(getAccessToken);
  const { showNotice } = useNotification();
  const { boutique } = useBoutique();
  const [page, setPage] = useState(1);
  const [search, setSearch] = useState('');
  const [modalOpen, setModalOpen] = useState(false);
  const [deleteTarget, setDeleteTarget] = useState<ProductFilter | null>(null);
  const [editing, setEditing] = useState<ProductFilter | null>(null);
  const [submitting, setSubmitting] = useState(false);

  const [form, setForm] = useState({ boutiqueId: '', name: '', type: 'text', values: '' });

  const fetchData = useCallback(async () => {
    const params = new URLSearchParams();
    params.set('page', String(page));
    params.set('itemsPerPage', String(PAGE_SIZE));
    if (search) params.set('name', search);
    return api.getCollection<ProductFilter>('/filters?' + params.toString());
  }, [api, page, search]);

  const { data, isLoading, error, refresh } = useApiData(fetchData, [page, search]);

  const filters = data?.member ?? [];
  const totalItems = data?.totalItems ?? 0;
  const totalPages = Math.max(1, Math.ceil(totalItems / PAGE_SIZE));

  function openCreate() {
    setEditing(null);
    setForm({ boutiqueId: '', name: '', type: 'text', values: '' });
    setModalOpen(true);
  }

  function openEdit(f: ProductFilter) {
    setEditing(f);
    setForm({
      boutiqueId: f.boutiqueId ?? '',
      name: f.name,
      type: f.type,
      values: (f.values ?? []).map((v) => v.value).join(', '),
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
      const values = form.values.split(',').map((v) => v.trim()).filter(Boolean).map((v) => ({ value: v }));
      const body = { boutiqueId, name: form.name, type: form.type, active: true, values };
      if (editing) {
        await api.patch('/filters/' + editing.id, body);
        showNotice('Filtre mis à jour.', 'success');
      } else {
        await api.post('/filters', body);
        showNotice('Filtre créé.', 'success');
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
    try {
      await api.delete('/filters/' + deleteTarget.id);
      showNotice('Filtre supprimé.', 'success');
      setDeleteTarget(null);
      refresh();
    } catch (err) {
      showNotice(err instanceof Error ? err.message : 'Erreur.', 'error');
    }
  }

  const columns = [
    { key: 'name', label: 'Nom', render: (f: ProductFilter) => <strong>{f.name}</strong> },
    { key: 'type', label: 'Type', render: (f: ProductFilter) => <Badge tone="neutral">{f.type}</Badge> },
    { key: 'active', label: 'Statut', render: (f: ProductFilter) => <Badge tone={f.active ? 'success' : 'neutral'}>{f.active ? 'Actif' : 'Inactif'}</Badge> },
    { key: 'values', label: 'Valeurs', render: (f: ProductFilter) => <span style={{ fontSize: 13, color: 'var(--bo-text-secondary)' }}>{(f.values ?? []).length} valeur(s)</span> },
  ];

  if (error) return <ErrorState message={error} onRetry={refresh} />;

  return (
    <div>
      <PageHeader title="Filtres produits" description="Configurez les filtres de recherche" actions={<Button onClick={openCreate}>+ Nouveau filtre</Button>} />
      <Card>
        <CardHeader>
          <input className="bo-input" style={{ maxWidth: 320 }} placeholder="Rechercher un filtre..." value={search} onChange={(e) => { setSearch(e.target.value); setPage(1); }} />
        </CardHeader>
        <CardBody>
          {isLoading ? <LoadingState /> : filters.length === 0 ? (
            <EmptyState title="Aucun filtre" message="Créez votre premier filtre." action={{ label: '+ Nouveau filtre', onClick: openCreate }} />
          ) : (
            <>
              <Table columns={columns} data={filters} onRowClick={openEdit} />
              <Pagination page={page} totalPages={totalPages} onPageChange={setPage} />
            </>
          )}
        </CardBody>
      </Card>

      <Modal isOpen={modalOpen} onClose={() => setModalOpen(false)} title={editing ? 'Modifier' : 'Nouveau filtre'} footer={
        <><Button variant="secondary" onClick={() => setModalOpen(false)}>Annuler</Button><Button onClick={handleSubmit} disabled={submitting}>{submitting ? '...' : editing ? 'Mettre à jour' : 'Créer'}</Button></>
      }>
        <form className="bo-form" onSubmit={handleSubmit}>
          <BoutiqueFormSelect value={form.boutiqueId} onChange={(boutiqueId) => setForm((f) => ({ ...f, boutiqueId }))} />
          <FormField label="Nom" required><Input required value={form.name} onChange={(e) => setForm((f) => ({ ...f, name: e.target.value }))} /></FormField>
          <FormField label="Type">
            <Select value={form.type} onChange={(e) => setForm((f) => ({ ...f, type: e.target.value }))}>
              <option value="text">Texte</option>
              <option value="select">Sélection</option>
              <option value="checkbox">Cases à cocher</option>
              <option value="range">Plage</option>
            </Select>
          </FormField>
          <FormField label="Valeurs (séparées par des virgules)" hint="Ex: Rouge, Bleu, Vert">
            <Textarea value={form.values} onChange={(e) => setForm((f) => ({ ...f, values: e.target.value }))} />
          </FormField>
        </form>
      </Modal>

      <ConfirmDialog isOpen={!!deleteTarget} onClose={() => setDeleteTarget(null)} onConfirm={handleDelete} title="Supprimer" message={`Supprimer "${deleteTarget?.name}" ?`} confirmLabel="Supprimer" danger />
    </div>
  );
}
