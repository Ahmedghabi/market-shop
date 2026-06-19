type ApiResponse<T> = { member?: T[]; items?: T[] } & T;

const storageKey = 'market-shop.auth';

export class ApiClient {
  private baseUrl: string;

  constructor(
    private getAccessToken: () => string | null,
    private boutiqueId?: string,
  ) {
    this.baseUrl = '/api';
  }

  private buildUrl(path: string): string {
    let url = `${this.baseUrl}${path}`;
    if (this.boutiqueId) {
      const separator = path.includes('?') ? '&' : '?';
      url += `${separator}boutiqueId=${encodeURIComponent(this.boutiqueId)}`;
    }
    return url;
  }

  private async request<T>(path: string, options: RequestInit = {}): Promise<T> {
    const token = this.getAccessToken();
    const headers: Record<string, string> = {
      'Content-Type': 'application/json',
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
      ...(options.headers as Record<string, string> ?? {}),
    };

    const response = await fetch(this.buildUrl(path), {
      ...options,
      headers,
    });

    if (response.status === 401) {
      window.localStorage.removeItem(storageKey);
      window.location.assign('/auth/login');
      throw new Error('Session expirée. Veuillez vous reconnecter.');
    }

    if (!response.ok) {
      const error = await response.json().catch(() => ({ detail: response.statusText }));
      throw new Error(error.detail ?? error.message ?? `Erreur ${response.status}`);
    }

    if (response.status === 204) return undefined as T;

    return response.json() as Promise<T>;
  }

  get<T>(path: string): Promise<T> {
    return this.request<T>(path);
  }

  post<T>(path: string, body?: unknown): Promise<T> {
    return this.request<T>(path, {
      method: 'POST',
      body: body ? JSON.stringify(body) : undefined,
    });
  }

  patch<T>(path: string, body: unknown): Promise<T> {
    return this.request<T>(path, {
      method: 'PATCH',
      body: JSON.stringify(body),
    });
  }

  put<T>(path: string, body: unknown): Promise<T> {
    return this.request<T>(path, {
      method: 'PUT',
      body: JSON.stringify(body),
    });
  }

  delete(path: string): Promise<void> {
    return this.request<void>(path, { method: 'DELETE' });
  }

  getCollection<T>(path: string): Promise<{ member: T[]; totalItems: number }> {
    return this.get<{ member?: T[]; totalItems?: number }>(path).then((res) => ({
      member: res.member ?? [],
      totalItems: res.totalItems ?? 0,
    }));
  }
}
