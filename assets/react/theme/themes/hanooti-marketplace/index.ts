import type { StorefrontThemePreset } from '../types';

export const hanootiMarketplaceTheme: StorefrontThemePreset = {
  code: 'hanooti-marketplace',
  name: 'Hanooti Marketplace',
  description: 'Marketplace premium avec surfaces glass, CTA achat vert et navigation catalogue par slug.',
  layout: 'glass',
  fontFamily: '"Nunito Sans", Rubik, system-ui, sans-serif',
  borderRadius: '18px',
  colorPalette: {
    primary: '#7C3AED',
    primaryContainer: '#A78BFA',
    secondary: '#475569',
    background: '#FAF5FF',
    surface: '#FFFFFF',
    surfaceContainer: '#F3E8FF',
    surfaceContainerHigh: '#E9D5FF',
    text: '#0F172A',
    textMuted: '#475569',
    outline: '#DDD6FE',
    accent: '#22C55E',
  },
};
