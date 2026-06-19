import { getStoredAccessToken } from '../../auth/getStoredAccessToken';

const EXCLUDED_SUBDOMAINS = ['www', 'admin', 'backoffice', 'app', 'mail', 'staging', 'dev'];

export function resolveBoutiqueSlug(pathPattern: RegExp): string {
  const pathSlug = window.location.pathname.match(pathPattern)?.[1];
  if (pathSlug) return pathSlug;

  const hostname = window.location.hostname;
  if (hostname === 'localhost' || /^\d+\.\d+\.\d+\.\d+$/.test(hostname)) return '';

  const labels = hostname.split('.');

  if (labels.length >= 3) {
    const [firstLabel] = labels;
    if (!firstLabel || EXCLUDED_SUBDOMAINS.includes(firstLabel)) return '';
    return firstLabel;
  }

  if (labels.length === 2 && hostname.endsWith('.localhost')) {
    const [firstLabel] = labels;
    if (!firstLabel || EXCLUDED_SUBDOMAINS.includes(firstLabel)) return '';
    return firstLabel;
  }

  return '';
}

export function isBoutiqueSubdomain(): boolean {
  const slug = resolveBoutiqueSlug(/^\/$/);
  return slug.length > 0;
}

export function boutiqueLink(path: string): string {
  const slug = isBoutiqueSubdomain() ? null
    : (resolveBoutiqueSlug(/^\/boutiques\/([^/]+)/) || (window as any).__boutiqueSlug__);
  return slug ? `/boutiques/${slug}${path}` : path;
}

export function authHeaders(): HeadersInit | undefined {
  const token = getStoredAccessToken();

  return token ? { Authorization: `Bearer ${token}` } : undefined;
}
