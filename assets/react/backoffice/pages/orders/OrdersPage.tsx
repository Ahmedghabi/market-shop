import { useState, useCallback } from 'react';
import type { Order } from '../../types';
import { useApiClient, useApiData } from '../../hooks/useApi';
import { Card, CardHeader, CardBody } from '../../components/Card';
import { Button } from '../../components/Button';
import { Table } from '../../components/Table';
import { Badge } from '../../components/Badge';
import { Pagination } from '../../components/Pagination';
import { Modal } from '../../components/Modal';
import { FormField } from '../../components/FormField';
import { FiltersBar } from '../../components/FiltersBar';
import { LoadingState, EmptyState, ErrorState } from '../../components/States';
import { PageHeader } from '../../layout/Shell';
import { useNotification } from '../../hooks/useNotification';

const PAGE_SIZE = 20;

const statusStyle: Record<string, string> = {
  pending: 'warning', confirmed: 'info', processing: 'info', shipped: 'success', delivered: 'success', cancelled: 'neutral', refunded: 'neutral',
};

const statusLabels: Record<string, string> = {
  pending: 'En attente', confirmed: 'Confirmée', processing: 'En cours', shipped: 'Expédiée', delivered: 'Livrée', cancelled: 'Annulée', refunded: 'Remboursée',
};

export function OrdersPage({ getAccessToken }: { getAccessToken: () => string | null }) {
  const api = useApiClient(getAccessToken);
  const { showNotice } = useNotification();
  const [page, setPage] = useState(1);
  const [search, setSearch] = useState('');
  const [status, setStatus] = useState('');
  const [sortField, setSortField] = useState('createdAt');
  const [sortDir, setSortDir] = useState<'asc' | 'desc'>('desc');
  const [detailOrder, setDetailOrder] = useState<Order | null>(null);

  const fetchData = useCallback(async () => {
    const params = new URLSearchParams();
    params.set('page', String(page));
    params.set('itemsPerPage', String(PAGE_SIZE));
    params.set('order[' + sortField + ']', sortDir);
    if (search) params.set('customerName', search);
    if (status) params.set('status', status);
    return api.getCollection<Order>('/orders?' + params.toString());
  }, [api, page, search, status, sortField, sortDir]);

  const { data, isLoading, error, refresh } = useApiData(fetchData, [page, search, status, sortField, sortDir]);

  const orders = data?.member ?? [];
  const totalItems = data?.totalItems ?? 0;
  const totalPages = Math.max(1, Math.ceil(totalItems / PAGE_SIZE));

  function handleSort(field: string) {
    if (sortField === field) setSortDir((d) => d === 'asc' ? 'desc' : 'asc');
    else { setSortField(field); setSortDir('asc'); }
  }

  const columns = [
    {
      key: 'id', label: 'Commande',
      render: (o: Order) => <strong style={{ fontSize: 13 }}>#{o.id.slice(0, 8)}</strong>,
    },
    {
      key: 'customerName', label: 'Client', sortable: true,
      render: (o: Order) => (
        <div><div>{o.customerName ?? '—'}</div><div style={{ fontSize: 12, color: 'var(--bo-text-muted)' }}>{o.customerEmail ?? ''}</div></div>
      ),
    },
    {
      key: 'status', label: 'Statut',
      render: (o: Order) => <Badge tone={statusStyle[o.status] as any ?? 'neutral'}>{statusLabels[o.status] ?? o.status}</Badge>,
    },
    {
      key: 'totalCents', label: 'Total', sortable: true,
      render: (o: Order) => <strong>{(o.totalCents / 100).toFixed(2)} {o.currency}</strong>,
    },
    {
      key: 'createdAt', label: 'Date', sortable: true,
      render: (o: Order) => <span style={{ color: 'var(--bo-text-secondary)', fontSize: 13 }}>{new Date(o.createdAt).toLocaleDateString('fr-FR')}</span>,
    },
  ];

  if (error) return <ErrorState message={error} onRetry={refresh} />;

  return (
    <div>
      <PageHeader title="Commandes" description="Suivez et gérez les commandes" />
      <Card>
        <CardHeader>
          <FiltersBar search={search} onSearchChange={(v) => { setSearch(v); setPage(1); }} status={status} onStatusChange={(v) => { setStatus(v); setPage(1); }} statusOptions={[
            { value: 'pending', label: 'En attente' }, { value: 'confirmed', label: 'Confirmée' },
            { value: 'processing', label: 'En cours' }, { value: 'shipped', label: 'Expédiée' },
            { value: 'delivered', label: 'Livrée' }, { value: 'cancelled', label: 'Annulée' },
          ]} />
          <span style={{ fontSize: 13, color: 'var(--bo-text-muted)' }}>{totalItems} commande{totalItems > 1 ? 's' : ''}</span>
        </CardHeader>
        <CardBody>
          {isLoading ? <LoadingState /> : orders.length === 0 ? (
            <EmptyState title="Aucune commande" message="Les commandes apparaîtront ici." />
          ) : (
            <>
              <Table columns={columns} data={orders} sort={{ field: sortField, direction: sortDir }} onSort={handleSort} onRowClick={setDetailOrder} />
              <Pagination page={page} totalPages={totalPages} onPageChange={setPage} />
            </>
          )}
        </CardBody>
      </Card>

      <Modal isOpen={!!detailOrder} onClose={() => setDetailOrder(null)} title={`Commande #${detailOrder?.id?.slice(0, 8) ?? ''}`} width="560px">
        {detailOrder && (
          <div style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
              <FormField label="Client"><div>{detailOrder.customerName ?? '—'}</div></FormField>
              <FormField label="Email"><div>{detailOrder.customerEmail ?? '—'}</div></FormField>
              <FormField label="Statut"><Badge tone={statusStyle[detailOrder.status] as any ?? 'neutral'}>{statusLabels[detailOrder.status] ?? detailOrder.status}</Badge></FormField>
              <FormField label="Total"><strong>{(detailOrder.totalCents / 100).toFixed(2)} {detailOrder.currency}</strong></FormField>
              <FormField label="Date"><span>{new Date(detailOrder.createdAt).toLocaleString('fr-FR')}</span></FormField>
            </div>
          </div>
        )}
      </Modal>
    </div>
  );
}
