import { PageHeader } from '../../layout/Shell';
import { DeliveryAdminPanel } from './DeliveryAdminPanel';
import { DeliveryBoutiquePanel } from './DeliveryBoutiquePanel';

export function DeliveryPage({ getAccessToken, userRoles = [] }: { getAccessToken: () => string | null; userRoles?: string[] }) {
  const isSuperAdmin = userRoles.includes('ROLE_SUPER_ADMIN');

  return (
    <div>
      <PageHeader
        title="Livraison"
        description={isSuperAdmin
          ? 'Gestion des sociétés de livraison, connectors, mapping et logs API.'
          : 'Vos transporteurs, credentials et expéditions.'}
      />
      {isSuperAdmin ? (
        <DeliveryAdminPanel getAccessToken={getAccessToken} />
      ) : (
        <DeliveryBoutiquePanel getAccessToken={getAccessToken} />
      )}
    </div>
  );
}
