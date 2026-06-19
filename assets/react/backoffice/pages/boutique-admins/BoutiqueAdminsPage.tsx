import { useCallback, useMemo, useState } from 'react';
import { useApiClient, useApiData } from '../../hooks/useApi';
import { Badge } from '../../components/Badge';
import { Card, CardBody, CardHeader } from '../../components/Card';
import { EmptyState, ErrorState, LoadingState } from '../../components/States';
import { FiltersBar } from '../../components/FiltersBar';
import { Table } from '../../components/Table';
import { PageHeader } from '../../layout/Shell';

type BoutiqueAdmin = {
  id: string;
  userId: string;
  email: string;
  displayName?: string | null;
  boutiqueId: string;
  boutiqueName: string;
  role: string;
  status: string;
  createdAt: string;
};

const statusLabels: Record<string, string> = {
  PENDING: 'En attente',
  ACTIVE: 'Actif',
  SUSPENDED: 'Suspendu',
  REJECTED: 'Rejeté',
};

function statusTone(status: string): 'success' | 'warning' | 'error' | 'neutral' {
  if (status === 'ACTIVE') return 'success';
  if (status === 'PENDING') return 'warning';
  if (status === 'SUSPENDED' || status === 'REJECTED') return 'error';

  return 'neutral';
}

export function BoutiqueAdminsPage({ getAccessToken }: { getAccessToken: () => string | null }) {
  const api = useApiClient(getAccessToken);
  const [search, setSearch] = useState('');
  const [status, setStatus] = useState('');

  const fetchAdmins = useCallback(() => api.getCollection<BoutiqueAdmin>('/admin/boutique-admins'), [api]);
  const { data, isLoading, error, refresh } = useApiData(fetchAdmins);
  const admins = data?.member ?? [];

  const filteredAdmins = useMemo(() => {
    const normalizedSearch = search.trim().toLowerCase();

    return admins.filter((admin) => {
      const matchesStatus = !status || admin.status === status;
      const matchesSearch = !normalizedSearch
        || admin.email.toLowerCase().includes(normalizedSearch)
        || (admin.displayName ?? '').toLowerCase().includes(normalizedSearch)
        || admin.boutiqueName.toLowerCase().includes(normalizedSearch);

      return matchesStatus && matchesSearch;
    });
  }, [admins, search, status]);

  const columns = [
    {
      key: 'email',
      label: 'Administrateur',
      render: (admin: BoutiqueAdmin) => (
        <div>
          <strong>{admin.displayName || admin.email}</strong>
          {admin.displayName && <div style={{ color: 'var(--bo-text-muted)', fontSize: 13 }}>{admin.email}</div>}
        </div>
      ),
    },
    { key: 'boutiqueName', label: 'Boutique', render: (admin: BoutiqueAdmin) => <span>{admin.boutiqueName}</span> },
    { key: 'role', label: 'Rôle', render: (admin: BoutiqueAdmin) => <Badge tone="primary">{admin.role}</Badge> },
    { key: 'status', label: 'Statut', render: (admin: BoutiqueAdmin) => <Badge tone={statusTone(admin.status)}>{statusLabels[admin.status] ?? admin.status}</Badge> },
    { key: 'createdAt', label: 'Créé le', render: (admin: BoutiqueAdmin) => <span>{new Date(admin.createdAt).toLocaleDateString('fr-FR')}</span> },
  ];

  if (error) return <ErrorState message={error} onRetry={refresh} />;

  return (
    <div>
      <PageHeader
        title="Admins boutique"
        description="Consultez les administrateurs liés aux boutiques de la plateforme."
      />

      <Card>
        <CardHeader>
          <FiltersBar
            search={search}
            onSearchChange={setSearch}
            status={status}
            onStatusChange={setStatus}
            statusOptions={Object.entries(statusLabels).map(([value, label]) => ({ value, label }))}
          />
        </CardHeader>
        <CardBody>
          {isLoading ? <LoadingState /> : filteredAdmins.length === 0 ? (
            <EmptyState title="Aucun admin boutique" message="Aucun administrateur ne correspond aux filtres." />
          ) : (
            <Table columns={columns} data={filteredAdmins} />
          )}
        </CardBody>
      </Card>
    </div>
  );
}
