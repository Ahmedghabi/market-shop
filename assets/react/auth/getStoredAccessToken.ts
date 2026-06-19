export function getStoredAccessToken(): string | null {
  const raw = window.localStorage.getItem('market-shop.auth');
  if (!raw) return null;

  try {
    const token = (JSON.parse(raw) as { accessToken?: string }).accessToken ?? null;

    if (!token || isAccessTokenExpired(token)) return null;

    return token;
  } catch {
    return null;
  }
}

export function isAccessTokenExpired(token: string): boolean {
  const [, payload] = token.split('.');
  if (!payload) return false;

  try {
    const normalized = payload.replace(/-/g, '+').replace(/_/g, '/');
    const padded = normalized.padEnd(normalized.length + ((4 - normalized.length % 4) % 4), '=');
    const decoded = JSON.parse(window.atob(padded)) as { exp?: number };

    return typeof decoded.exp === 'number' && decoded.exp * 1000 <= Date.now();
  } catch {
    return false;
  }
}
