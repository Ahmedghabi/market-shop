import { useState, useRef, useEffect } from 'react';
import type { BackOfficeAccess, Boutique, BellNotification } from '../types';
import { frontOfficeUrl } from '../utils/frontOfficeUrl';

function UserAvatar({ name }: { name: string }) {
  const initials = name.split(' ').map((p) => p[0]).join('').slice(0, 2).toUpperCase();
  return <div className="bo-user-avatar">{initials || 'U'}</div>;
}

export function Header({
  userEmail,
  userRoles,
  boutique,
  boutiques,
  onBoutiqueChange,
  onSignOut,
  access,
}: {
  userEmail: string;
  userRoles: string[];
  boutique: Boutique | null;
  boutiques: Boutique[];
  onBoutiqueChange: (b: Boutique | null) => void;
  onSignOut: () => void;
  access?: BackOfficeAccess | null;
}) {
  const [boutiqueOpen, setBoutiqueOpen] = useState(false);
  const [userOpen, setUserOpen] = useState(false);
  const [notifOpen, setNotifOpen] = useState(false);
  const [notifications, setNotifications] = useState<BellNotification[]>([
    { id: '1', title: 'Nouvelle commande', message: 'Commande #1024 de 89,90 DT', type: 'info', read: false, createdAt: 'Il y a 5 min', link: '/backoffice/orders/1024' },
    { id: '2', title: 'Stock faible', message: 'Produit "Chemise blanche" n\'a plus que 2 unités', type: 'warning', read: false, createdAt: 'Il y a 12 min' },
    { id: '3', title: 'Paiement reçu', message: 'Paiement de 156,00 DT confirmé', type: 'success', read: true, createdAt: 'Il y a 1h' },
  ]);
  const [search, setSearch] = useState('');
  const boutiqueRef = useRef<HTMLDivElement>(null);
  const userRef = useRef<HTMLDivElement>(null);
  const notifRef = useRef<HTMLDivElement>(null);
  const isAdmin = userRoles.includes('ROLE_SUPER_ADMIN') || userRoles.includes('ROLE_BOUTIQUE_ADMIN');
  const isSuperAdmin = userRoles.includes('ROLE_SUPER_ADMIN');
  const permissionSet = new Set(access?.permissions ?? []);
  const canSeeNotification = (n: BellNotification) => {
    if (isAdmin) return true;
    if (n.title.toLowerCase().includes('commande')) return permissionSet.has('order.read') || permissionSet.has('view_orders');
    if (n.title.toLowerCase().includes('stock')) return permissionSet.has('product.inventory.manage') || permissionSet.has('view_inventory');
    if (n.title.toLowerCase().includes('paiement')) return permissionSet.has('invoice.payment.receive');

    return true;
  };
  const visibleNotifications = notifications.filter(canSeeNotification);

  useEffect(() => {
    function handleClick(e: MouseEvent) {
      if (boutiqueRef.current && !boutiqueRef.current.contains(e.target as Node)) setBoutiqueOpen(false);
      if (userRef.current && !userRef.current.contains(e.target as Node)) setUserOpen(false);
      if (notifRef.current && !notifRef.current.contains(e.target as Node)) setNotifOpen(false);
    }
    document.addEventListener('mousedown', handleClick);
    return () => document.removeEventListener('mousedown', handleClick);
  }, []);

  return (
    <header className="bo-header">
      <div className="bo-header-left">
        <div className="bo-search">
          <svg className="bo-search-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
            <path strokeLinecap="round" strokeLinejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
          </svg>
          <input
            type="search"
            placeholder="Rechercher produits, commandes, clients..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
          />
        </div>
      </div>

      <div className="bo-header-right">
        <div ref={boutiqueRef} className="bo-boutique-selector">
          <button className="bo-boutique-selector-trigger" onClick={() => setBoutiqueOpen(!boutiqueOpen)}>
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
              <path strokeLinecap="round" strokeLinejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .415.336.75.75.75z" />
            </svg>
            <span>{boutique?.name ?? 'Toutes les boutiques'}</span>
          </button>
          {boutiqueOpen && (
            <div className="bo-boutique-dropdown">
              {isSuperAdmin && (
                <div className={`bo-boutique-option ${boutique === null ? 'active' : ''}`} style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                  <button
                    type="button"
                    onClick={() => { onBoutiqueChange(null); setBoutiqueOpen(false); }}
                    style={{ display: 'flex', alignItems: 'center', gap: 8, flex: 1, minWidth: 0, border: 0, background: 'transparent', padding: 0, textAlign: 'left', cursor: 'pointer', color: 'inherit' }}
                  >
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                      <path strokeLinecap="round" strokeLinejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .415.336.75.75.75z" />
                    </svg>
                    <div style={{ minWidth: 0 }}>
                      <strong>Toutes les boutiques</strong>
                      <small style={{ display: 'block', fontSize: 11, color: 'var(--bo-text-muted)' }}>Vue générale plateforme</small>
                    </div>
                    {boutique === null && <span style={{ marginLeft: 'auto', color: 'var(--bo-primary)' }}>✓</span>}
                  </button>
                </div>
              )}
              {boutiques.map((b) => (
                <div key={b.id} className={`bo-boutique-option ${b.id === boutique?.id ? 'active' : ''}`} style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                  <button
                    type="button"
                    onClick={() => { onBoutiqueChange(b); setBoutiqueOpen(false); }}
                    style={{ display: 'flex', alignItems: 'center', gap: 8, flex: 1, minWidth: 0, border: 0, background: 'transparent', padding: 0, textAlign: 'left', cursor: 'pointer', color: 'inherit' }}
                  >
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                      <path strokeLinecap="round" strokeLinejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .415.336.75.75.75z" />
                    </svg>
                    <div style={{ minWidth: 0 }}>
                      <strong>{b.name}</strong>
                      <small style={{ display: 'block', fontSize: 11, color: 'var(--bo-text-muted)' }}>{b.slug}</small>
                    </div>
                    {b.id === boutique?.id && <span style={{ marginLeft: 'auto', color: 'var(--bo-primary)' }}>✓</span>}
                  </button>
                  <a href={frontOfficeUrl(b)} target="_blank" rel="noreferrer" className="bo-btn bo-btn-secondary bo-btn-sm" style={{ textDecoration: 'none' }}>
                    Front
                  </a>
                </div>
              ))}
              {boutiques.length === 0 && (
                <div style={{ padding: '12px 14px', fontSize: 12, color: 'var(--bo-text-muted)' }}>
                  Aucune boutique disponible
                </div>
              )}
            </div>
          )}
        </div>

        <div ref={notifRef} className="bo-notif-dropdown">
          <button className="bo-notif-trigger" onClick={() => setNotifOpen(!notifOpen)} aria-label="Notifications">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
              <path strokeLinecap="round" strokeLinejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
            </svg>
            {visibleNotifications.some(n => !n.read) && (
              <span className="bo-notif-badge">{visibleNotifications.filter(n => !n.read).length}</span>
            )}
          </button>
          {notifOpen && (
            <div className="bo-notif-panel">
              <div className="bo-notif-header">
                <strong>Notifications</strong>
                {visibleNotifications.some(n => !n.read) && (
                  <button className="bo-notif-mark-read" onClick={() => setNotifications(notifications.map(n => ({ ...n, read: true })))}>
                    Tout marquer lu
                  </button>
                )}
              </div>
              <div className="bo-notif-list">
                {visibleNotifications.length === 0 ? (
                  <div className="bo-notif-empty">Aucune notification</div>
                ) : (
                  visibleNotifications.map(n => (
                    <div key={n.id} className={`bo-notif-item ${n.read ? '' : 'bo-notif-unread'}`}>
                      <div className="bo-notif-dot" data-type={n.type} />
                      <div className="bo-notif-content">
                        <div className="bo-notif-title">{n.title}</div>
                        <div className="bo-notif-msg">{n.message}</div>
                        <div className="bo-notif-time">{n.createdAt}</div>
                      </div>
                    </div>
                  ))
                )}
              </div>
              <a href="/backoffice/notifications" className="bo-notif-footer">
                Voir toutes les notifications
              </a>
            </div>
          )}
        </div>

        <div ref={userRef} className="bo-user-menu">
          <button className="bo-user-trigger" onClick={() => setUserOpen(!userOpen)}>
            <UserAvatar name={boutique?.name ?? userEmail} />
            <div className="bo-user-info">
              <strong>{boutique?.name ?? 'Compte'}</strong>
              <span>{userEmail}</span>
            </div>
          </button>
          {userOpen && (
            <div className="bo-user-dropdown">
              <div style={{ padding: '12px 14px', borderBottom: '1px solid var(--bo-border-light)' }}>
                <div style={{ fontSize: 13, fontWeight: 600 }}>{userEmail}</div>
                <div style={{ fontSize: 11, color: 'var(--bo-text-muted)', marginTop: 4 }}>
                  {userRoles.join(', ')}
                </div>
              </div>
              <a href={boutique ? frontOfficeUrl(boutique) : '/'} target="_blank" rel="noreferrer" className="bo-user-dropdown-item">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                  <path strokeLinecap="round" strokeLinejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                </svg>
                Front-office
              </a>
              <div className="bo-user-dropdown-divider" />
              <button className="bo-user-dropdown-item danger" onClick={onSignOut}>
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                  <path strokeLinecap="round" strokeLinejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                </svg>
                Déconnexion
              </button>
            </div>
          )}
        </div>
      </div>
    </header>
  );
}
