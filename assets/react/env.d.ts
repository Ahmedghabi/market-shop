declare namespace NodeJS {
  interface ProcessEnv {
    OAUTH2_AUTHORITY?: string;
    OAUTH2_CLIENT_ID?: string;
    OAUTH2_REDIRECT_URI?: string;
    OAUTH2_POST_LOGOUT_REDIRECT_URI?: string;
    OAUTH2_SCOPE?: string;
  }
}

interface ImportMetaEnv {
  readonly VITE_MERCURE_PUBLIC_URL?: string;
}

interface ImportMeta {
  readonly env?: ImportMetaEnv;
}

interface Window {
  __boutiqueSlug__?: string;
}
