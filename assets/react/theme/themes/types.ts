export type StorefrontThemePreset = {
  code: string;
  name: string;
  description: string;
  layout: 'glass' | 'editorial' | 'minimal';
  fontFamily: string;
  borderRadius: string;
  colorPalette: Record<string, string>;
};
