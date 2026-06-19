import { useEffect, useState } from 'react';
import { applyBoutiqueTheme, defaultBoutiqueTheme, type BoutiqueTheme } from './boutiqueTheme';

export function useBoutiqueTheme() {
  const [theme, setTheme] = useState<BoutiqueTheme>(defaultBoutiqueTheme);

  useEffect(() => {
    applyBoutiqueTheme(theme);
  }, [theme]);

  return { theme, setTheme };
}
