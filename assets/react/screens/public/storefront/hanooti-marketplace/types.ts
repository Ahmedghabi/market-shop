import type { StoreProduct } from '../ProductCard';

export type CartItem = { product: StoreProduct; qty: number };
export type PageKind = 'home' | 'catalogue' | 'category' | 'promotions' | 'reviews' | 'about' | 'contact';
export type { StoreCategory, StoreFilter } from '../catalogueTypes';
