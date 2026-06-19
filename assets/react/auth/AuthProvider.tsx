import { createContext, useCallback, useEffect, useMemo, useState, type ReactNode } from 'react';

type AuthenticatedUser = {
  accessToken: string;
  profile: {
    email: string;
    displayName?: string | null;
    sub: string;
    roles: string[];
    boutiques: Array<{ id: string; name: string; slug: string; status: string }>;
  };
};

export type RegisterPayload = {
  email: string;
  password: string;
  displayName: string;
  boutiqueName: string;
  boutiqueSlug: string;
};

type AuthContextValue = {
  user: AuthenticatedUser | null;
  isLoading: boolean;
  signIn: (email: string, password: string) => Promise<void>;
  signUp: (payload: RegisterPayload) => Promise<void>;
  signOut: () => Promise<void>;
  getAccessToken: () => string | null;
};

export const AuthContext = createContext<AuthContextValue | null>(null);

const storageKey = 'market-shop.dev-auth';

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<AuthenticatedUser | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    const storedUser = window.localStorage.getItem(storageKey);

    if (storedUser) {
      try {
        setUser(JSON.parse(storedUser) as AuthenticatedUser);
      } catch {
        window.localStorage.removeItem(storageKey);
      }
    }

    setIsLoading(false);
  }, []);

  const authenticate = useCallback(async (url: string, body?: unknown) => {
    const response = await fetch(url, {
      method: 'POST',
      headers: body ? { 'Content-Type': 'application/json' } : undefined,
      body: body ? JSON.stringify(body) : undefined,
    });

    if (!response.ok) {
      const payload = await response.json().catch(() => ({ message: 'Authentification impossible.' })) as { message?: string };
      throw new Error(payload.message ?? 'Authentification impossible.');
    }

    const payload = await response.json() as {
      accessToken: string;
      user: {
        email: string;
        displayName?: string | null;
        roles: string[];
        boutiques?: Array<{ id: string; name: string; slug: string; status: string }>;
      };
    };
    const authenticatedUser: AuthenticatedUser = {
      accessToken: payload.accessToken,
      profile: {
        email: payload.user.email,
        displayName: payload.user.displayName,
        sub: payload.user.email,
        roles: payload.user.roles,
        boutiques: payload.user.boutiques ?? [],
      },
    };

    window.localStorage.setItem(storageKey, JSON.stringify(authenticatedUser));
    setUser(authenticatedUser);
  }, []);

  const signIn = useCallback(async (email: string, password: string) => {
    await authenticate('/api/auth/login', { email, password });
  }, [authenticate]);

  const signUp = useCallback(async (payload: RegisterPayload) => {
    await authenticate('/api/auth/register', payload);
  }, [authenticate]);

  const signOut = useCallback(async () => {
    window.localStorage.removeItem(storageKey);
    setUser(null);
  }, []);

  const getAccessToken = useCallback(() => user?.accessToken ?? null, [user]);

  const value = useMemo(
    () => ({ user, isLoading, signIn, signUp, signOut, getAccessToken }),
    [user, isLoading, signIn, signUp, signOut, getAccessToken],
  );

  return <AuthContext value={value}>{children}</AuthContext>;
}
