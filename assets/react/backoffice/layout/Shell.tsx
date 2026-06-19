import type { ReactNode } from 'react';
import type { BackOfficeAccess, Boutique } from '../types';
import { Sidebar } from './Sidebar';
import { Header } from './Header';

export function Shell({
  children,
  currentPath,
  userEmail,
  userRoles,
  boutique,
  boutiques,
  onBoutiqueChange,
  onSignOut,
  access,
}: {
  children: ReactNode;
  currentPath: string;
  userEmail: string;
  userRoles: string[];
  boutique: Boutique | null;
  boutiques: Boutique[];
  onBoutiqueChange: (b: Boutique | null) => void;
  onSignOut: () => void;
  access?: BackOfficeAccess | null;
}) {
  return (
    <div className="bo-shell">
      <Sidebar
        currentPath={currentPath}
        userRoles={userRoles}
        boutiqueName={boutique?.name}
        access={access}
      />
      <Header
        userEmail={userEmail}
        userRoles={userRoles}
        boutique={boutique}
        boutiques={boutiques}
        onBoutiqueChange={onBoutiqueChange}
        onSignOut={onSignOut}
        access={access}
      />
      <main className="bo-content">
        {children}
      </main>
    </div>
  );
}

export function PageHeader({
  title,
  description,
  actions,
}: {
  title: string;
  description: string;
  actions?: ReactNode;
}) {
  return (
    <div className="bo-page-header">
      <div className="bo-page-header-info">
        <h1>{title}</h1>
        <p>{description}</p>
      </div>
      {actions && <div className="bo-page-header-actions">{actions}</div>}
    </div>
  );
}
