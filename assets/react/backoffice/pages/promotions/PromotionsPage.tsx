import { useState, useCallback } from 'react';
import { useApiClient, useApiData } from '../../hooks/useApi';
import { Card, CardHeader, CardBody } from '../../components/Card';
import { Button } from '../../components/Button';
import { Table } from '../../components/Table';
import { Badge } from '../../components/Badge';
import { Pagination } from '../../components/Pagination';
import { Modal } from '../../components/Modal';
import { ConfirmDialog } from '../../components/ConfirmDialog';
import { FormField, Input, Select } from '../../components/FormField';
import { LoadingState, EmptyState, ErrorState } from '../../components/States';
import { PageHeader } from '../../layout/Shell';
import { useNotification } from '../../hooks/useNotification';
import { useBoutique } from '../../hooks/useBoutique';
import { BoutiqueFormSelect, resolveFormBoutiqueId } from '../../components/BoutiqueFormSelect';

type Promotion = {
  id: string; boutiqueId?: string; name: string; type: string; value: number; active: boolean;
  startsAt?: string; endsAt?: string; createdAt: string;
};

const PAGE_SIZE = 20;

export function PromotionsPage({ getAccessToken }: { getAccessToken: () => string | null }) {
  const api = useApiClient(getAccessToken);
  const { showNotice } = useNotification();
  const { boutique } = useBoutique();
  const [page, setPage] = useState(1);
  const [search, setSearch] = useState('');
  const [status, setStatus] = useState('');
  const [modalOpen, setModalOpen] = useState(false);
  const [deleteTarget, setDeleteTarget] = useState<Promotion | null>(null);
  const [editing, setEditing] = useState<Promotion | null>(null);
  const [submitting, setSubmitting] = useState(false);
  const [form, setForm] = useState({ boutiqueId: '', name: '', type: 'percentage', value: 0, startDate: '', endDate: '', status: 'active' });

  const fetchData = useCallback(async () => {
    const params = new URLSearchParams();
    params.set('page', String(page)); params.set('itemsPerPage', String(PAGE_SIZE));
    if (search) params.set('name', search);
    if (status) params.set('status', status);
    return api.getCollection<Promotion>('/promotions?' + params.toString());
  }, [api, page, search, status]);

  const { data, isLoading, error, refresh } = useApiData(fetchData, [page, search, status]);
  const items = data?.member ?? [];
  const totalItems = data?.totalItems ?? 0;
  const totalPages = Math.max(1, Math.ceil(totalItems / PAGE_SIZE));

  function openCreate() {
    setEditing(null);
    setForm({ boutiqueId: '', name: '', type: 'percentage', value: 0, startDate: '', endDate: '', status: 'active' });
    setModalOpen(true);
  }
  function openEdit(p: Promotion) {
    setEditing(p);
    setForm({ boutiqueId: p.boutiqueId ?? '', name: p.name, type: p.type, value: p.value, startDate: p.startsAt ?? '', endDate: p.endsAt ?? '', status: p.active ? 'active' : 'inactive' });
    setModalOpen(true);
  }

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    const boutiqueId = resolveFormBoutiqueId(boutique?.id, form.boutiqueId);
    if (!boutiqueId) { showNotice('Sélectionnez une boutique.', 'error'); return; }
    setSubmitting(true);
    try {
      const body = { boutiqueId, name: form.name, type: form.type, value: form.value, startsAt: form.startDate || null, endsAt: form.endDate || null, active: form.status === 'active' };
      if (editing) { await api.patch('/promotions/' + editing.id, body); showNotice('Promotion mise à jour.', 'success'); }
      else { await api.post('/promotions', body); showNotice('Promotion créée.', 'success'); }
      setModalOpen(false); refresh();
    } catch (err) { showNotice(err instanceof Error ? err.message : 'Erreur.', 'error'); }
    finally { setSubmitting(false); }
  }

  async function handleDelete() {
    if (!deleteTarget) return;
    try { await api.delete('/promotions/' + deleteTarget.id); showNotice('Promotion supprimée.', 'success'); setDeleteTarget(null); refresh(); }
    catch (err) { showNotice(err instanceof Error ? err.message : 'Erreur.', 'error'); }
  }

  const columns = [
    { key: 'name', label: 'Nom', render: (p: Promotion) => <strong>{p.name}</strong> },
    { key: 'type', label: 'Type', render: (p: Promotion) => <Badge tone="neutral">{p.type === 'percentage' ? '%' : 'Montant fixe'}</Badge> },
    { key: 'value', label: 'Valeur', render: (p: Promotion) => <span>{p.type === 'percentage' ? `${p.value}%` : `${(p.value / 100).toFixed(2)} TND`}</span> },
    { key: 'status', label: 'Statut', render: (p: Promotion) => <Badge tone={p.active ? 'success' : 'neutral'}>{p.active ? 'Actif' : 'Inactif'}</Badge> },
  ];

  if (error) return <ErrorState message={error} onRetry={refresh} />;

  return (
    <div>
      <PageHeader title="Promotions" description="Codes promo et réductions" actions={<Button onClick={openCreate}>+ Nouvelle promotion</Button>} />
      <Card>
        <CardHeader>
          <input className="bo-input" style={{ maxWidth: 320 }} placeholder="Rechercher..." value={search} onChange={(e) => { setSearch(e.target.value); setPage(1); }} />
        </CardHeader>
        <CardBody>
          {isLoading ? <LoadingState /> : items.length === 0 ? (
            <EmptyState title="Aucune promotion" message="Créez votre première promotion." action={{ label: '+ Nouvelle promotion', onClick: openCreate }} />
          ) : (
            <><Table columns={columns} data={items} onRowClick={openEdit} /><Pagination page={page} totalPages={totalPages} onPageChange={setPage} /></>
          )}
        </CardBody>
      </Card>

      <Modal isOpen={modalOpen} onClose={() => setModalOpen(false)} title={editing ? 'Modifier' : 'Nouvelle promotion'} footer={
        <><Button variant="secondary" onClick={() => setModalOpen(false)}>Annuler</Button><Button onClick={handleSubmit} disabled={submitting}>{submitting ? '...' : editing ? 'Mettre à jour' : 'Créer'}</Button></>
      }>
        <form className="bo-form" onSubmit={handleSubmit}>
          <BoutiqueFormSelect value={form.boutiqueId} onChange={(boutiqueId) => setForm((f) => ({ ...f, boutiqueId }))} />
          <FormField label="Nom" required><Input required value={form.name} onChange={(e) => setForm((f) => ({ ...f, name: e.target.value }))} /></FormField>
          <div className="bo-form-row">
            <FormField label="Type">
              <Select value={form.type} onChange={(e) => setForm((f) => ({ ...f, type: e.target.value }))}>
                <option value="percentage">Pourcentage</option>
                <option value="fixed_amount">Montant fixe</option>
              </Select>
            </FormField>
            <FormField label="Valeur" required hint="% ou centimes">
              <Input type="number" min={0} required value={form.value} onChange={(e) => setForm((f) => ({ ...f, value: Number(e.target.value) }))} />
            </FormField>
          </div>
          <div className="bo-form-row">
            <FormField label="Début"><Input type="date" value={form.startDate} onChange={(e) => setForm((f) => ({ ...f, startDate: e.target.value }))} /></FormField>
            <FormField label="Fin"><Input type="date" value={form.endDate} onChange={(e) => setForm((f) => ({ ...f, endDate: e.target.value }))} /></FormField>
          </div>
        </form>
      </Modal>

      <ConfirmDialog isOpen={!!deleteTarget} onClose={() => setDeleteTarget(null)} onConfirm={handleDelete} title="Supprimer" message={`Supprimer "${deleteTarget?.name}" ?`} confirmLabel="Supprimer" danger />
    </div>
  );
}
