import { useState, useCallback } from 'react';
import type { Customer } from '../../types';
import { useApiClient, useApiData } from '../../hooks/useApi';
import { Card, CardHeader, CardBody } from '../../components/Card';
import { Badge } from '../../components/Badge';
import { Table } from '../../components/Table';
import { Pagination } from '../../components/Pagination';
import { Modal } from '../../components/Modal';
import { FormField } from '../../components/FormField';
import { FiltersBar } from '../../components/FiltersBar';
import { LoadingState, EmptyState, ErrorState } from '../../components/States';
import { PageHeader } from '../../layout/Shell';

const PAGE_SIZE = 20;

export function CustomersPage({ getAccessToken }: { getAccessToken: () => string | null }) {
  const api = useApiClient(getAccessToken);
  const [page, setPage] = useState(1);
  const [search, setSearch] = useState('');
  const [detailCustomer, setDetailCustomer] = useState<Customer | null>(null);

  const fetchData = useCallback(async () => {
    const params = new URLSearchParams();
    params.set('page', String(page)); params.set('itemsPerPage', String(PAGE_SIZE));
    if (search) params.set('email', search);
    return api.getCollection<Customer>('/customers?' + params.toString());
  }, [api, page, search]);

  const { data, isLoading, error, refresh } = useApiData(fetchData, [page, search]);
  const customers = data?.member ?? [];
  const totalItems = data?.totalItems ?? 0;
  const totalPages = Math.max(1, Math.ceil(totalItems / PAGE_SIZE));

  const columns = [
    {
      key: 'email', label: 'Client',
      render: (c: Customer) => (
        <div>
          <strong style={{ fontSize: 14 }}>{c.firstName || c.lastName ? `${c.firstName ?? ''} ${c.lastName ?? ''}` : c.email}</strong>
          <div style={{ fontSize: 12, color: 'var(--bo-text-muted)' }}>{c.email}</div>
        </div>
      ),
    },
    { key: 'phone', label: 'Téléphone', render: (c: Customer) => <span style={{ color: 'var(--bo-text-secondary)' }}>{c.phone ?? '—'}</span> },
    { key: 'ordersCount', label: 'Commandes', render: (c: Customer) => <Badge tone="neutral">{c.ordersCount ?? 0}</Badge> },
    {
      key: 'totalSpentCents', label: 'Total dépensé',
      render: (c: Customer) => <strong>{((c.totalSpentCents ?? 0) / 100).toFixed(2)} TND</strong>,
    },
    { key: 'createdAt', label: 'Inscrit le', render: (c: Customer) => <span style={{ fontSize: 13, color: 'var(--bo-text-secondary)' }}>{new Date(c.createdAt).toLocaleDateString('fr-FR')}</span> },
  ];

  if (error) return <ErrorState message={error} onRetry={refresh} />;

  return (
    <div>
      <PageHeader title="Clients" description="Gérez votre base clients" />
      <Card>
        <CardHeader>
          <input className="bo-input" style={{ maxWidth: 320 }} placeholder="Rechercher par email..." value={search} onChange={(e) => { setSearch(e.target.value); setPage(1); }} />
          <span style={{ fontSize: 13, color: 'var(--bo-text-muted)' }}>{totalItems} client{totalItems > 1 ? 's' : ''}</span>
        </CardHeader>
        <CardBody>
          {isLoading ? <LoadingState /> : customers.length === 0 ? (
            <EmptyState title="Aucun client" message="Les clients apparaîtront ici." />
          ) : (
            <><Table columns={columns} data={customers} onRowClick={setDetailCustomer} /><Pagination page={page} totalPages={totalPages} onPageChange={setPage} /></>
          )}
        </CardBody>
      </Card>

      <Modal isOpen={!!detailCustomer} onClose={() => setDetailCustomer(null)} title="Détails client" width="500px">
        {detailCustomer && (
          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
            <FormField label="Email"><div>{detailCustomer.email}</div></FormField>
            <FormField label="Téléphone"><div>{detailCustomer.phone ?? '—'}</div></FormField>
            <FormField label="Prénom"><div>{detailCustomer.firstName ?? '—'}</div></FormField>
            <FormField label="Nom"><div>{detailCustomer.lastName ?? '—'}</div></FormField>
            <FormField label="Commandes"><div>{detailCustomer.ordersCount ?? 0}</div></FormField>
            <FormField label="Total dépensé"><div>{((detailCustomer.totalSpentCents ?? 0) / 100).toFixed(2)} TND</div></FormField>
          </div>
        )}
      </Modal>
    </div>
  );
}
