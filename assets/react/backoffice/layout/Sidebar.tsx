import type { NavItem, BackOfficeAccess } from '../types';

const defaultNav: NavItem[] = [
  { slug: 'dashboard', title: 'Tableau de bord', path: '/admin/dashboard', section: 'Général', description: 'Vue d\'ensemble', icon: 'chart', permissions: ['ROLE_BOUTIQUE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_CAISSIER'] },
  { slug: 'boutiques', title: 'Boutiques', path: '/admin/boutiques', section: 'Administration', description: 'Gestion des boutiques', icon: 'shield', permissions: ['ROLE_SUPER_ADMIN'] },
  { slug: 'boutique-admins', title: 'Admins boutique', path: '/admin/boutique-admins', section: 'Administration', description: 'Gestion des administrateurs boutique', icon: 'users', permissions: ['ROLE_SUPER_ADMIN'] },
  { slug: 'products', title: 'Produits', path: '/admin/products', section: 'Catalogue', description: 'Gestion des produits', icon: 'box', permissions: ['ROLE_BOUTIQUE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_CAISSIER'], moduleAliases: ['products', 'produits'], requiredPermissions: ['product.read', 'view_products'] },
  { slug: 'categories', title: 'Catégories', path: '/admin/categories', section: 'Catalogue', description: 'Catégories de produits', icon: 'list', permissions: ['ROLE_BOUTIQUE_ADMIN', 'ROLE_SUPER_ADMIN'], moduleAliases: ['categories'], requiredPermissions: ['product.category.manage'] },
  { slug: 'filters', title: 'Filtres', path: '/admin/filters', section: 'Catalogue', description: 'Filtres produits', icon: 'filter', permissions: ['ROLE_BOUTIQUE_ADMIN', 'ROLE_SUPER_ADMIN'], moduleAliases: ['products', 'produits'], requiredPermissions: ['product.update', 'edit_products'] },
  { slug: 'orders', title: 'Commandes', path: '/admin/orders', section: 'Ventes', description: 'Gestion des commandes', icon: 'cart', permissions: ['ROLE_BOUTIQUE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_CAISSIER'], moduleAliases: ['orders', 'commandes'], requiredPermissions: ['order.read', 'view_orders'] },
  { slug: 'customers', title: 'Clients', path: '/admin/customers', section: 'Ventes', description: 'Gestion des clients', icon: 'users', permissions: ['ROLE_BOUTIQUE_ADMIN', 'ROLE_SUPER_ADMIN'], moduleAliases: ['customers', 'clients'], requiredPermissions: ['customer.read'] },
  { slug: 'reviews', title: 'Avis', path: '/admin/reviews', section: 'Ventes', description: 'Avis clients', icon: 'star', permissions: ['ROLE_BOUTIQUE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_CAISSIER'], moduleAliases: ['reviews'], requiredPermissions: ['review.read', 'view_reviews'] },
  { slug: 'promotions', title: 'Promotions', path: '/admin/promotions', section: 'Marketing', description: 'Codes promo et réductions', icon: 'tag', permissions: ['ROLE_BOUTIQUE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_CAISSIER'], moduleAliases: ['promotions', 'coupons'], requiredPermissions: ['marketing.promotion.manage', 'marketing.coupon.manage', 'promotions', 'coupons'] },
  { slug: 'cms', title: 'CMS', path: '/admin/cms', section: 'Contenu', description: 'Pages et contenu', icon: 'file', permissions: ['ROLE_BOUTIQUE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_CAISSIER'], moduleAliases: ['cms', 'blog'], requiredPermissions: ['cms.page.read', 'cms_access', 'cms', 'blog'] },
  { slug: 'settings', title: 'Paramètres', path: '/admin/settings', section: 'Configuration', description: 'Paramètres boutique', icon: 'gear', permissions: ['ROLE_BOUTIQUE_ADMIN', 'ROLE_SUPER_ADMIN'], requiredPermissions: ['shop.settings.manage'] },
  { slug: 'employees', title: 'Employés', path: '/admin/employees', section: 'Configuration', description: 'Gestion des employés', icon: 'users', permissions: ['ROLE_BOUTIQUE_ADMIN', 'ROLE_SUPER_ADMIN'], moduleAliases: ['employees'], requiredPermissions: ['employee.read'] },
  { slug: 'subscriptions', title: 'Abonnements', path: '/admin/subscriptions', section: 'Configuration', description: 'Gestion abonnements', icon: 'credit-card', permissions: ['ROLE_BOUTIQUE_ADMIN', 'ROLE_SUPER_ADMIN'], requiredPermissions: ['subscription.plan.read'] },
];

function NavIcon({ icon }: { icon: string }) {
  const icons: Record<string, React.ReactNode> = {
    chart: <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}><path strokeLinecap="round" strokeLinejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg>,
    box: <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}><path strokeLinecap="round" strokeLinejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" /></svg>,
    cart: <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}><path strokeLinecap="round" strokeLinejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" /></svg>,
    users: <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}><path strokeLinecap="round" strokeLinejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>,
    tag: <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}><path strokeLinecap="round" strokeLinejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" /><path strokeLinecap="round" strokeLinejoin="round" d="M6 6h.008v.008H6V6z" /></svg>,
    gear: <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}><path strokeLinecap="round" strokeLinejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" /><path strokeLinecap="round" strokeLinejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>,
    file: <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}><path strokeLinecap="round" strokeLinejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>,
    list: <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}><path strokeLinecap="round" strokeLinejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>,
    filter: <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}><path strokeLinecap="round" strokeLinejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" /></svg>,
    'credit-card': <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}><path strokeLinecap="round" strokeLinejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" /></svg>,
    shield: <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}><path strokeLinecap="round" strokeLinejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>,
    star: <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}><path strokeLinecap="round" strokeLinejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" /></svg>,
  };
  return <>{icons[icon] ?? icons.box}</>;
}

export function Sidebar({
  currentPath,
  userRoles,
  boutiqueName,
  access,
}: {
  currentPath: string;
  userRoles: string[];
  boutiqueName?: string;
  access?: BackOfficeAccess | null;
}) {
  const isPlatformAdmin = userRoles.includes('ROLE_SUPER_ADMIN');
  const isBoutiqueAdmin = userRoles.includes('ROLE_BOUTIQUE_ADMIN');
  const permissions = new Set(access?.permissions ?? []);
  const canAccess = (item: NavItem) => {
    if (!item.permissions.some((p) => userRoles.includes(p))) return false;
    if (isPlatformAdmin) return true;
    if (item.slug === 'dashboard') return true;
    if (item.slug === 'super-admin') return false;

    const modulesOk = !item.moduleAliases || item.moduleAliases.some((module) =>
      access?.globalModules[module] === true && (!access.boutiqueModules[module] || access.boutiqueModules[module].accessible),
    );
    if (!modulesOk) return false;

    if (isBoutiqueAdmin) return true;

    return (item.requiredPermissions ?? []).some((permission) => permissions.has(permission));
  };

  const visibleNav = defaultNav.filter(canAccess);

  const sections = visibleNav.reduce(
    (acc, item) => {
      if (!acc[item.section]) acc[item.section] = [];
      acc[item.section].push(item);
      return acc;
    },
    {} as Record<string, NavItem[]>,
  );

  return (
    <aside className="bo-sidebar">
      <div className="bo-sidebar-brand">
        <div className="bo-sidebar-brand-logo">H</div>
        <div className="bo-sidebar-brand-text">
          <h2>Hanooti</h2>
          <span>{boutiqueName ?? 'Back Office'}</span>
        </div>
      </div>
      <nav className="bo-sidebar-nav">
        {Object.entries(sections).map(([section, items]) => (
          <div key={section}>
            <div className="bo-sidebar-section">{section}</div>
            {items.map((item) => {
              const isActive = currentPath === item.path || currentPath.startsWith(item.path + '/');
              return (
                <a
                  key={item.slug}
                  href={item.path}
                  className={`bo-sidebar-item ${isActive ? 'active' : ''}`}
                >
                  <NavIcon icon={item.icon} />
                  <span>{item.title}</span>
                </a>
              );
            })}
          </div>
        ))}
      </nav>
    </aside>
  );
}
