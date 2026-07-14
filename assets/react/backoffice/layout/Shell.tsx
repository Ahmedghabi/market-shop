import { useEffect, useState, type ReactNode } from 'react';
import { AnimatePresence, motion } from 'framer-motion';
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
  const [menuOpen, setMenuOpen] = useState(false);

  useEffect(() => {
    setMenuOpen(false);
  }, [currentPath]);

  return (
    <div className="bo-shell">
      <Sidebar
        currentPath={currentPath}
        userRoles={userRoles}
        boutiqueName={boutique?.name}
        access={access}
        isOpen={menuOpen}
        onNavigate={() => setMenuOpen(false)}
      />
      <AnimatePresence>
        {menuOpen && (
          <motion.div
            className="bo-sidebar-scrim"
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            onClick={() => setMenuOpen(false)}
          />
        )}
      </AnimatePresence>
      <Header
        userEmail={userEmail}
        userRoles={userRoles}
        boutique={boutique}
        boutiques={boutiques}
        onBoutiqueChange={onBoutiqueChange}
        onSignOut={onSignOut}
        access={access}
        onMenuToggle={() => setMenuOpen((v) => !v)}
      />
      <main className="bo-content">
        <AnimatePresence mode="wait">
          <motion.div
            key={currentPath}
            initial={{ opacity: 0, y: 8 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, y: -4 }}
            transition={{ duration: 0.22, ease: [0.16, 1, 0.3, 1] }}
          >
            {children}
          </motion.div>
        </AnimatePresence>
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
