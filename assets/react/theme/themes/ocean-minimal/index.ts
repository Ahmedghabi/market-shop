import type { StorefrontThemePreset } from '../types';

export const oceanMinimalTheme: StorefrontThemePreset = {
  code: 'ocean-minimal',
  name: 'Ocean Minimal',
  description: 'Bleu ocean, fond clair et interface epuree.',
  layout: 'minimal',
  fontFamily: 'Inter, system-ui, sans-serif',
  borderRadius: '8px',
  colorPalette: {
    primary: '#0e7490',
    primaryContainer: '#0891b2',
    secondary: '#475569',
    background: '#f0f9ff',
    surface: '#ffffff',
    surfaceContainer: '#e0f2fe',
    surfaceContainerHigh: '#bae6fd',
    text: '#0f172a',
    textMuted: '#64748b',
    outline: '#cbd5e1',
    accent: '#0369a1',
  },
};
