import type { StorefrontThemePreset } from '../types';

export const hanootiGlassTheme: StorefrontThemePreset = {
  code: 'hanooti-glass',
  name: 'Hanooti Glass',
  description: 'Violet moderne, surfaces lumineuses et effet glassmorphism.',
  layout: 'glass',
  fontFamily: 'Inter, system-ui, sans-serif',
  borderRadius: '12px',
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
    accent: '#7c3aed',
  },
};
