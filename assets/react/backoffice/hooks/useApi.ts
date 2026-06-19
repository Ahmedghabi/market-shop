import { useState, useEffect, useCallback } from 'react';
import { ApiClient } from '../api/client';
import { useBoutique } from './useBoutique';

export function useApiClient(getAccessToken: () => string | null): ApiClient {
  const { boutique } = useBoutique();
  return new ApiClient(getAccessToken, boutique?.id);
}

export function useApiData<T>(
  fetchFn: () => Promise<T>,
  deps: unknown[] = [],
): {
  data: T | null;
  isLoading: boolean;
  error: string | null;
  refresh: () => void;
} {
  const [data, setData] = useState<T | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [trigger, setTrigger] = useState(0);

  const refresh = useCallback(() => setTrigger((n) => n + 1), []);

  useEffect(() => {
    let cancelled = false;
    setIsLoading(true);
    setError(null);

    fetchFn()
      .then((result) => {
        if (!cancelled) setData(result);
      })
      .catch((err) => {
        if (!cancelled) setError(err instanceof Error ? err.message : 'Erreur inconnue');
      })
      .finally(() => {
        if (!cancelled) setIsLoading(false);
      });

    return () => { cancelled = true; };
  }, [trigger, ...deps]);

  return { data, isLoading, error, refresh };
}

export function useDebounce<T>(value: T, delay = 300): T {
  const [debounced, setDebounced] = useState(value);
  useEffect(() => {
    const timer = setTimeout(() => setDebounced(value), delay);
    return () => clearTimeout(timer);
  }, [value, delay]);
  return debounced;
}
