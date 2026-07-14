import type { StorefrontThemePreset } from '../types';

export const nordicEditorialTheme: StorefrontThemePreset = {
  code: 'nordic-editorial',
  name: 'Nordic Editorial',
  description: 'Beige chaud, noir profond et mise en page editoriale.',
  layout: 'editorial',
  fontFamily: '"DM Sans", Inter, sans-serif',
  borderRadius: '1.6rem',
  colorPalette: {
    primary: '#111111',
    primaryContainer: '#2b2b2b',
    secondary: '#6b6560',
    background: '#f6f2eb',
    surface: '#ffffff',
    surfaceContainer: '#ece5d9',
    surfaceContainerHigh: '#e7e0d6',
    text: '#171717',
    textMuted: '#6b6560',
    outline: '#d8d0c4',
    accent: '#a44100',
  },
};
