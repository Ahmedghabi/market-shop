import { createContext, useContext } from 'react';
import type { Boutique } from '../types';

export type BoutiqueContextType = {
  boutique: Boutique | null;
  boutiques: Boutique[];
  setBoutique: (b: Boutique | null) => void;
};

export const BoutiqueCtx = createContext<BoutiqueContextType>({
  boutique: null,
  boutiques: [],
  setBoutique: () => {},
});

export function useBoutique() {
  return useContext(BoutiqueCtx);
}
