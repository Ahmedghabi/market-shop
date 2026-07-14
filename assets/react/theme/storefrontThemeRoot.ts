import { applyBoutiqueTheme, applyStorefrontCssVars } from './boutiqueTheme';

export type StorefrontThemeData = {
  id?: string;
  name?: string;
  logoUrl?: string | null;
  primaryColor?: string | null;
  backgroundColor?: string | null;
  colorPalette?: Record<string, string> | null;
  iconSet?: Record<string, never>;
  featuredCategories?: [];
  frontOfficePages?: [];
  navigationItems?: [];
  theme?: string | null;
  fontFamily?: string | null;
  fontSize?: string | null;
  borderRadius?: string | null;
};

export function applyStorefrontTheme(data: StorefrontThemeData): void {
  const colorPalette = {
    ...(data.colorPalette ?? {}),
    ...(data.primaryColor ? { primary: data.primaryColor } : {}),
    ...(data.backgroundColor && !data.colorPalette?.background ? { background: data.backgroundColor } : {}),
  };

  if (Object.keys(colorPalette).length > 0) {
    applyBoutiqueTheme({
      boutiqueId: data.id ?? '',
      name: data.name ?? '',
      logoUrl: data.logoUrl ?? undefined,
      colorPalette,
      iconSet: data.iconSet ?? {},
      featuredCategories: data.featuredCategories ?? [],
      frontOfficePages: data.frontOfficePages ?? [],
      navigationItems: data.navigationItems ?? [],
    });
    applyStorefrontCssVars(colorPalette);
  }

  const root = document.documentElement;
  if (data.theme) root.dataset.storefrontTheme = data.theme;
  if (data.fontFamily) root.style.setProperty('--ds-font-family', data.fontFamily);
  if (data.fontSize) root.style.setProperty('--ds-font-size', data.fontSize);
  if (data.borderRadius) {
    root.style.setProperty('--ds-radius', data.borderRadius);
    root.style.setProperty('--ds-radius-sm', `calc(${data.borderRadius} / 2)`);
    root.style.setProperty('--ds-radius-lg', `calc(${data.borderRadius} * 1.5)`);
  }
}

export function resetStorefrontTheme(): void {
  const root = document.documentElement;
  [
    '--ds-primary', '--ds-primary-container', '--ds-secondary', '--ds-surface', '--ds-surface-container-lowest',
    '--ds-surface-container', '--ds-surface-container-high', '--ds-on-surface', '--ds-on-surface-variant',
    '--ds-outline-variant', '--primary', '--primary-container', '--secondary', '--surface',
    '--surface-container-lowest', '--surface-container', '--surface-container-high', '--on-surface',
    '--on-surface-variant', '--outline-variant', '--sf-bg', '--sf-surface', '--sf-surface-muted',
    '--sf-surface-accent', '--sf-text', '--sf-text-muted', '--sf-accent', '--sf-accent-alt', '--sf-outline',
    '--ds-font-family', '--ds-font-size', '--ds-radius', '--ds-radius-sm', '--ds-radius-lg',
  ].forEach((property) => root.style.removeProperty(property));
  delete root.dataset.storefrontTheme;
}
