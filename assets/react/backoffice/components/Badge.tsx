import type { ReactNode } from 'react';

type Tone = 'success' | 'warning' | 'error' | 'info' | 'neutral' | 'primary';

export function Badge({ children, tone = 'neutral' }: { children: ReactNode; tone?: Tone }) {
  return <span className={`bo-badge bo-badge-${tone}`}>{children}</span>;
}

export function statusBadge(status: string): { label: string; tone: Tone } {
  const map: Record<string, { label: string; tone: Tone }> = {
    active: { label: 'Actif', tone: 'success' },
    inactive: { label: 'Inactif', tone: 'neutral' },
    published: { label: 'Publié', tone: 'success' },
    draft: { label: 'Brouillon', tone: 'neutral' },
    pending: { label: 'En attente', tone: 'warning' },
    approved: { label: 'Approuvé', tone: 'success' },
    rejected: { label: 'Rejeté', tone: 'error' },
    suspended: { label: 'Suspendu', tone: 'error' },
    cancelled: { label: 'Annulé', tone: 'error' },
    completed: { label: 'Terminé', tone: 'success' },
    processing: { label: 'En cours', tone: 'info' },
    shipped: { label: 'Expédié', tone: 'info' },
    delivered: { label: 'Livré', tone: 'success' },
    paid: { label: 'Payé', tone: 'success' },
    refunded: { label: 'Remboursé', tone: 'warning' },
    true: { label: 'Oui', tone: 'success' },
    false: { label: 'Non', tone: 'neutral' },
  };
  return map[status.toLowerCase()] ?? { label: status, tone: 'neutral' };
}
