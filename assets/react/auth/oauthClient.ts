import { UserManager, WebStorageStateStore } from 'oidc-client-ts';

const origin = window.location.origin;

export const oauthClient = new UserManager({
  authority: process.env.OAUTH2_AUTHORITY ?? 'https://issuer.example.com',
  client_id: process.env.OAUTH2_CLIENT_ID ?? 'market-shop-spa',
  redirect_uri: process.env.OAUTH2_REDIRECT_URI ?? `${origin}/oauth/callback`,
  post_logout_redirect_uri: process.env.OAUTH2_POST_LOGOUT_REDIRECT_URI ?? origin,
  response_type: 'code',
  scope: process.env.OAUTH2_SCOPE ?? 'openid profile email offline_access',
  automaticSilentRenew: true,
  userStore: new WebStorageStateStore({ store: window.localStorage }),
});
