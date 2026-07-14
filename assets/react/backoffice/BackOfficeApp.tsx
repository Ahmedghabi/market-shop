import { useState, useEffect, type JSX } from 'react';
import { useLocation } from 'react-router-dom';
import { Shell } from './layout/Shell';
import { BoutiqueCtx } from './hooks/useBoutique';
import { NotificationProvider } from './hooks/useNotification';
import { ToastContainer } from './components/Toast';
import { DashboardPage } from './pages/dashboard/DashboardPage';
import { ProductsPage } from './pages/products/ProductsPage';
import { CategoriesPage } from './pages/categories/CategoriesPage';
import { FiltersPage } from './pages/filters/FiltersPage';
import { OrdersPage } from './pages/orders/OrdersPage';
import { CustomersPage } from './pages/customers/CustomersPage';
import { PromotionsPage } from './pages/promotions/PromotionsPage';
import { CmsManagementPage } from './pages/cms/CmsPage';
import { SettingsPage } from './pages/settings/SettingsPage';
import { FrontOfficePage } from './pages/front-office/FrontOfficePage';
import { ReviewsPage } from './pages/reviews/ReviewsPage';
import { EmployeesPage } from './pages/employees/EmployeesPage';
import { SubscriptionsPage } from './pages/subscriptions/SubscriptionsPage';
import { DeliveryPage } from './pages/delivery/DeliveryPage';
import { SuperAdminPage } from './pages/super-admin/SuperAdminPage';
import { BoutiqueAdminsPage } from './pages/boutique-admins/BoutiqueAdminsPage';
import { Card, CardBody } from './components/Card';
import { LoadingState } from './components/States';
import type { BackOfficeAccess, Boutique } from './types';

type PageProps = { getAccessToken: () => string | null; userRoles?: string[] };
type RouteGate = { moduleAliases?: string[]; permissions?: string[]; roles?: string[]; sensitive?: boolean };
const authStorageKey = 'market-shop.auth';

function handleUnauthorized(response: Response): Response {
  if (response.status === 401) {
    window.localStorage.removeItem(authStorageKey);
    window.location.assign('/auth/login');
  }

  return response;
}

const routeGates: Record<string, RouteGate> = {
  dashboard: { roles: ['ROLE_SUPER_ADMIN', 'ROLE_BOUTIQUE_ADMIN', 'ROLE_CAISSIER'] },
  products: { moduleAliases: ['products', 'produits'], permissions: ['product.read', 'view_products'] },
  categories: { moduleAliases: ['categories'], permissions: ['product.category.manage'] },
  filters: { moduleAliases: ['products', 'produits'], permissions: ['product.update', 'edit_products'] },
  orders: { moduleAliases: ['orders', 'commandes'], permissions: ['order.read', 'view_orders'] },
  customers: { moduleAliases: ['customers', 'clients'], permissions: ['customer.read'] },
  promotions: { moduleAliases: ['promotions', 'coupons'], permissions: ['marketing.promotion.manage', 'marketing.coupon.manage', 'promotions', 'coupons'] },
  reviews: { moduleAliases: ['reviews'], permissions: ['review.read', 'view_reviews'] },
  cms: { moduleAliases: ['cms', 'blog'], permissions: ['cms.page.read', 'cms_access', 'cms', 'blog'] },
  appearance: { permissions: ['shop.appearance.manage', 'shop.settings.manage'], sensitive: true },
  theme: { permissions: ['shop.appearance.manage', 'shop.settings.manage'], sensitive: true },
  settings: { permissions: ['shop.settings.manage'], sensitive: true },
  employees: { moduleAliases: ['employees'], permissions: ['employee.read'], sensitive: true },
  subscriptions: { permissions: ['subscription.plan.read'], sensitive: true },
  delivery: { permissions: ['shop.delivery_account.manage', 'order.delivery.manage'], sensitive: true },
  boutiques: { roles: ['ROLE_SUPER_ADMIN'] },
  'boutique-admins': { roles: ['ROLE_SUPER_ADMIN'] },
  'super-admin': { roles: ['ROLE_SUPER_ADMIN'] },
};

function resolvePage(slug: string, props: PageProps) {
  const pages: Record<string, (p: PageProps) => JSX.Element> = {
    dashboard: (p) => <DashboardPage {...p} />,
    products: (p) => <ProductsPage {...p} />,
    categories: (p) => <CategoriesPage {...p} />,
    filters: (p) => <FiltersPage {...p} />,
    orders: (p) => <OrdersPage {...p} />,
    customers: (p) => <CustomersPage {...p} />,
    promotions: (p) => <PromotionsPage {...p} />,
    reviews: (p) => <ReviewsPage {...p} />,
    cms: (p) => <CmsManagementPage {...p} />,
    appearance: (p) => <FrontOfficePage {...p} />,
    theme: (p) => <FrontOfficePage {...p} />,
    settings: (p) => <SettingsPage {...p} />,
    employees: (p) => <EmployeesPage {...p} />,
    subscriptions: (p) => <SubscriptionsPage {...p} />,
    delivery: (p) => <DeliveryPage {...p} />,
    boutiques: (p) => <SuperAdminPage {...p} />,
    'boutique-admins': (p) => <BoutiqueAdminsPage {...p} />,
    'super-admin': (p) => <SuperAdminPage {...p} />,
  };
  return pages[slug]?.(props) ?? <DashboardPage {...props} />;
}

function canOpenRoute(slug: string, userRoles: string[], access: BackOfficeAccess | null) {
  const gate = routeGates[slug];
  if (!gate) return false;
  if (userRoles.includes('ROLE_SUPER_ADMIN')) return true;
  if (gate.roles?.some((role) => userRoles.includes(role))) return true;
  if (gate.sensitive && !userRoles.includes('ROLE_BOUTIQUE_ADMIN')) return false;
  if (!access) return slug === 'dashboard';

  const moduleOk = !gate.moduleAliases || gate.moduleAliases.some((module) =>
    access.globalModules[module] === true && (!access.boutiqueModules[module] || access.boutiqueModules[module].accessible),
  );
  if (!moduleOk) return false;

  return (gate.permissions ?? []).some((permission) => access.permissions.includes(permission));
}

function AccessDeniedPage() {
  return (
    <Card>
      <CardBody>
        <div style={{ textAlign: 'center', padding: 32 }}>
          <h2 style={{ margin: 0 }}>Accès refusé</h2>
          <p style={{ color: 'var(--bo-text-muted)', marginTop: 8 }}>Cette page est masquée par les permissions, les modules ou l'abonnement de la boutique.</p>
        </div>
      </CardBody>
    </Card>
  );
}

function slugFromPath(path: string): string {
  const segments = path.replace(/^\/admin\/?/, '').split('/');
  return segments[0] || 'dashboard';
}

export function BackOfficeApp({
  userEmail,
  userRoles,
  userBoutiques,
  getAccessToken,
  onSignOut,
}: {
  userEmail: string;
  userRoles: string[];
  userBoutiques: Array<{ id: string; name: string; slug: string; status: string; customDomain?: string | null; isVisiblePublicly?: boolean }>;
  getAccessToken: () => string | null;
  onSignOut: () => Promise<void>;
}) {
  const location = useLocation();
  const currentPath = location.pathname;
  const pageSlug = slugFromPath(currentPath);

  const isSuperAdmin = userRoles.includes('ROLE_SUPER_ADMIN');
  const defaultBoutique: Boutique | null = !isSuperAdmin && userBoutiques.length > 0
    ? { id: userBoutiques[0].id, name: userBoutiques[0].name, slug: userBoutiques[0].slug, status: userBoutiques[0].status, customDomain: userBoutiques[0].customDomain, isVisiblePublicly: userBoutiques[0].isVisiblePublicly }
    : null;

  const [boutique, setBoutique] = useState<Boutique | null>(defaultBoutique);
  const [boutiques, setBoutiques] = useState<Boutique[]>(
    userBoutiques.map((b) => ({ id: b.id, name: b.name, slug: b.slug, status: b.status, customDomain: b.customDomain, isVisiblePublicly: b.isVisiblePublicly }))
  );
  const [access, setAccess] = useState<BackOfficeAccess | null>(null);
  const [accessLoading, setAccessLoading] = useState(true);

  useEffect(() => {
    const token = getAccessToken();
    if (token) {
      fetch('/api/boutiques', { headers: { Authorization: `Bearer ${token}` } })
        .then(handleUnauthorized)
        .then((r) => r.json())
        .then((data) => {
          const list: Boutique[] = data.member ?? data.items ?? [];
          if (list.length > 0) {
            setBoutiques(list);
            setBoutique((prev) => prev ?? (isSuperAdmin ? null : list[0]));
          }
        })
        .catch(() => {});
    }
  }, [getAccessToken, isSuperAdmin]);

  useEffect(() => {
    const token = getAccessToken();
    if (!token) {
      setAccess(null);
      setAccessLoading(false);
      return;
    }

    setAccessLoading(true);

    if (!boutique) {
      fetch('/api/admin/dashboard/modules', { headers: { Authorization: `Bearer ${token}` } })
        .then(handleUnauthorized)
        .then((r) => r.ok ? r.json() : { modules: {} })
        .then((global) => {
          setAccess({
            globalModules: global.modules ?? {},
            boutiqueModules: {},
            permissions: [],
            roles: userRoles,
          });
        })
        .catch(() => setAccess(null))
        .finally(() => setAccessLoading(false));
      return;
    }

    Promise.all([
      fetch('/api/admin/dashboard/modules', { headers: { Authorization: `Bearer ${token}` } }).then(handleUnauthorized).then((r) => r.ok ? r.json() : { modules: {} }),
      fetch(`/api/admin/boutiques/${boutique.id}/dashboard/access`, { headers: { Authorization: `Bearer ${token}` } }).then(handleUnauthorized).then((r) => r.ok ? r.json() : { modules: {}, permissions: [], roles: userRoles }),
    ])
      .then(([global, boutiqueAccess]) => {
        setAccess({
          globalModules: global.modules ?? {},
          boutiqueModules: boutiqueAccess.modules ?? {},
          permissions: boutiqueAccess.permissions ?? [],
          roles: boutiqueAccess.roles ?? userRoles,
        });
      })
      .catch(() => setAccess(null))
      .finally(() => setAccessLoading(false));
  }, [boutique?.id, getAccessToken, userRoles.join('|')]);

  return (
    <BoutiqueCtx.Provider value={{ boutique, boutiques, setBoutique }}>
      <NotificationProvider>
        <InnerApp
          currentPath={currentPath}
          pageSlug={pageSlug}
          userEmail={userEmail}
          userRoles={userRoles}
          boutique={boutique}
          boutiques={boutiques}
          onBoutiqueChange={setBoutique}
          access={access}
          accessLoading={accessLoading}
          getAccessToken={getAccessToken}
          onSignOut={onSignOut}
        />
      </NotificationProvider>
    </BoutiqueCtx.Provider>
  );
}

function InnerApp({
  currentPath,
  pageSlug,
  userEmail,
  userRoles,
  boutique,
  boutiques,
  onBoutiqueChange,
  access,
  accessLoading,
  getAccessToken,
  onSignOut,
}: {
  currentPath: string;
  pageSlug: string;
  userEmail: string;
  userRoles: string[];
  boutique: Boutique | null;
  boutiques: Boutique[];
  onBoutiqueChange: (b: Boutique | null) => void;
  access: BackOfficeAccess | null;
  accessLoading: boolean;
  getAccessToken: () => string | null;
  onSignOut: () => Promise<void>;
}) {
  const waitingForAccess = accessLoading && !userRoles.includes('ROLE_SUPER_ADMIN');

  return (
    <>
      <Shell
        currentPath={currentPath}
        userEmail={userEmail}
        userRoles={userRoles}
        boutique={boutique}
        boutiques={boutiques}
        onBoutiqueChange={onBoutiqueChange}
        onSignOut={onSignOut}
        access={access}
      >
        {waitingForAccess ? (
          <Card><CardBody><LoadingState message="Vérification des accès..." /></CardBody></Card>
        ) : canOpenRoute(pageSlug, userRoles, access) ? (
          resolvePage(pageSlug, { getAccessToken, userRoles })
        ) : (
          <AccessDeniedPage />
        )}
      </Shell>
      <ToastContainer />
    </>
  );
}
