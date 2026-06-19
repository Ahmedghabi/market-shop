import type { IconProp } from '@fortawesome/fontawesome-svg-core';
import { appIcons } from '../icons/fontAwesome';

export type BoutiqueTheme = {
  boutiqueId: string;
  name: string;
  logoUrl?: string;
  colorPalette: Record<string, string>;
  iconSet: Record<string, IconProp>;
  featuredCategories: Array<{ label: string; icon?: IconProp; color?: string; position?: number }>;
  frontOfficePages: Array<{ slug: string; label: string; enabled: boolean; position?: number }>;
  navigationItems: Array<{ label: string; href: string; icon?: IconProp; position?: number }>;
};

export const defaultBoutiqueTheme: BoutiqueTheme = {
  boutiqueId: 'luxe-paris',
  name: 'Luxe Paris',
  colorPalette: {
    primary: '#3525cd',
    primaryContainer: '#4f46e5',
    secondary: '#505f76',
    background: '#fcf8ff',
    surface: '#ffffff',
    surfaceContainer: '#f0ecf9',
    surfaceContainerHigh: '#eae6f4',
    text: '#1b1b24',
    textMuted: '#464555',
    outline: '#c7c4d8',
  },
  iconSet: {
    dashboard: appIcons.dashboard,
    products: appIcons.products,
    categories: appIcons.store,
    orders: appIcons.pos,
    customers: appIcons.users,
    promotions: appIcons.promotions,
    loyalty: appIcons.loyalty,
    sponsors: appIcons.sponsors,
    settings: appIcons.theme,
  },
  featuredCategories: [
    { label: 'Robes de soirée', icon: appIcons.products, color: '#3525cd', position: 1 },
    { label: 'Accessoires luxe', icon: appIcons.sponsors, color: '#a44100', position: 2 },
    { label: 'Offres privées', icon: appIcons.promotions, color: '#047857', position: 3 },
  ],
  frontOfficePages: [
    { slug: 'home', label: 'Accueil', enabled: true, position: 1 },
    { slug: 'products', label: 'Produits', enabled: true, position: 2 },
    { slug: 'offers', label: 'Offres', enabled: true, position: 3 },
    { slug: 'loyalty', label: 'Fidélité', enabled: true, position: 4 },
  ],
  navigationItems: [
    { label: 'Dashboard', href: '#dashboard', icon: appIcons.dashboard, position: 1 },
    { label: 'Produits', href: '#products', icon: appIcons.products, position: 2 },
    { label: 'Commandes', href: '#orders', icon: appIcons.pos, position: 3 },
    { label: 'Promotions', href: '#promotions', icon: appIcons.promotions, position: 4 },
    { label: 'Paramètres', href: '#settings', icon: appIcons.theme, position: 5 },
  ],
};

export function applyBoutiqueTheme(theme: BoutiqueTheme) {
  const root = document.documentElement;
  const colors = theme.colorPalette;

  root.style.setProperty('--ds-primary', colors.primary ?? '#3525cd');
  root.style.setProperty('--ds-primary-container', colors.primaryContainer ?? '#4f46e5');
  root.style.setProperty('--ds-secondary', colors.secondary ?? '#505f76');
  root.style.setProperty('--ds-surface', colors.background ?? '#fcf8ff');
  root.style.setProperty('--ds-surface-container-lowest', colors.surface ?? '#ffffff');
  root.style.setProperty('--ds-surface-container', colors.surfaceContainer ?? '#f0ecf9');
  root.style.setProperty('--ds-surface-container-high', colors.surfaceContainerHigh ?? '#eae6f4');
  root.style.setProperty('--ds-on-surface', colors.text ?? '#1b1b24');
  root.style.setProperty('--ds-on-surface-variant', colors.textMuted ?? '#464555');
  root.style.setProperty('--ds-outline-variant', colors.outline ?? '#c7c4d8');

  root.style.setProperty('--primary', colors.primary ?? '#3525cd');
  root.style.setProperty('--primary-container', colors.primaryContainer ?? '#4f46e5');
  root.style.setProperty('--secondary', colors.secondary ?? '#505f76');
  root.style.setProperty('--surface', colors.background ?? '#fcf8ff');
  root.style.setProperty('--surface-container-lowest', colors.surface ?? '#ffffff');
  root.style.setProperty('--surface-container', colors.surfaceContainer ?? '#f0ecf9');
  root.style.setProperty('--surface-container-high', colors.surfaceContainerHigh ?? '#eae6f4');
  root.style.setProperty('--on-surface', colors.text ?? '#1b1b24');
  root.style.setProperty('--on-surface-variant', colors.textMuted ?? '#464555');
  root.style.setProperty('--outline-variant', colors.outline ?? '#c7c4d8');
}
