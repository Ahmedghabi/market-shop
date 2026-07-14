const CART_CONSENT_COOKIE = 'hanooti_cart_cookie_consent';

export function hasCartCookieConsent(): boolean {
  return document.cookie.split('; ').some((cookie) => cookie === `${CART_CONSENT_COOKIE}=accepted`);
}

export function acceptCartCookieConsent(): void {
  document.cookie = `${CART_CONSENT_COOKIE}=accepted; Max-Age=31536000; Path=/; SameSite=Lax`;
}
