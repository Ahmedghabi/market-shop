import { useState, useCallback } from 'react';
import { useApiClient, useApiData } from '../../hooks/useApi';
import { Card, CardHeader, CardBody } from '../../components/Card';
import { Button } from '../../components/Button';
import { Badge } from '../../components/Badge';
import { LoadingState, ErrorState } from '../../components/States';
import { FormField, Input, Textarea } from '../../components/FormField';
import { PageHeader } from '../../layout/Shell';
import { useNotification } from '../../hooks/useNotification';
import { useBoutique } from '../../hooks/useBoutique';

type ThemeOption = {
  id: string;
  name: string;
  code: string;
  description?: string | null;
  isDefault?: boolean;
  colorPalette?: Record<string, string>;
};

type BoutiqueSettings = {
  name?: string; slogan?: string; description?: string; contactEmail?: string; contactPhone?: string;
  address?: string; city?: string; postalCode?: string; country?: string;
  fontFamily?: string; fontSize?: string; borderRadius?: string;
  enableEmailVerification?: boolean; enableCustomerEmailVerification?: boolean;
  orderMode?: string; maintenance?: boolean;
  theme?: string;
};

function ThemePreviewCard({
  theme,
  selected,
  onSelect,
}: {
  theme: ThemeOption;
  selected: boolean;
  onSelect: () => void;
}) {
  const palette = theme.colorPalette ?? {};
  const swatches = [
    palette.primary ?? '#3525cd',
    palette.background ?? '#fcf8ff',
    palette.surface ?? '#ffffff',
    palette.accent ?? palette.primaryContainer ?? '#4f46e5',
  ];

  return (
    <button
      type="button"
      onClick={onSelect}
      className="bo-theme-card"
      style={{
        border: selected ? '2px solid var(--bo-primary)' : '1px solid var(--bo-border)',
        boxShadow: selected ? '0 0 0 3px color-mix(in srgb, var(--bo-primary) 18%, transparent)' : undefined,
      }}
    >
      <div className="bo-theme-card__preview">
        {swatches.map((color) => (
          <span key={color} style={{ backgroundColor: color }} />
        ))}
      </div>
      <div className="bo-theme-card__body">
        <div className="bo-theme-card__title">
          <span>{theme.name}</span>
          {theme.isDefault && <Badge tone="info">Défaut</Badge>}
          {selected && <Badge tone="success">Actif</Badge>}
        </div>
        {theme.description && <p className="bo-theme-card__desc">{theme.description}</p>}
      </div>
    </button>
  );
}

export function SettingsPage({ getAccessToken }: { getAccessToken: () => string | null }) {
  const api = useApiClient(getAccessToken);
  const { showNotice } = useNotification();
  const { boutique } = useBoutique();
  const [saving, setSaving] = useState(false);
  const [savingTheme, setSavingTheme] = useState(false);
  const [form, setForm] = useState<BoutiqueSettings>({});
  const [themes, setThemes] = useState<ThemeOption[]>([]);

  const fetchSettings = useCallback(async () => {
    if (!boutique?.id) return null;
    const [data, themesData] = await Promise.all([
      api.get<any>('/settings'),
      api.get<ThemeOption[] | { member?: ThemeOption[] }>('/themes').catch(() => []),
    ]);
    const themeList = Array.isArray(themesData)
      ? themesData
      : (themesData as { member?: ThemeOption[] }).member ?? [];
    setThemes(themeList);
    setForm({
      name: data.shopName ?? '', slogan: data.slogan ?? '', description: data.description ?? '',
      contactEmail: data.contactEmail ?? '', contactPhone: data.contactPhone ?? '',
      address: data.address ?? '', city: data.city ?? '', postalCode: data.postalCode ?? '', country: data.country ?? '',
      fontFamily: data.fontFamily ?? '', fontSize: data.fontSize ?? '', borderRadius: data.borderRadius ?? '',
      enableEmailVerification: !!data.enableEmailVerification,
      enableCustomerEmailVerification: !!data.enableCustomerEmailVerification,
      orderMode: data.orderMode ?? 'standard', maintenance: !!data.maintenance,
      theme: data.theme ?? themeList.find((t) => t.isDefault)?.code ?? themeList[0]?.code ?? '',
    });
    return data;
  }, [api, boutique?.id]);

  const { isLoading, error, refresh } = useApiData(fetchSettings, [boutique?.id]);

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setSaving(true);
    try {
      await api.patch('/settings', { ...form, shopName: form.name });
      showNotice('Paramètres mis à jour.', 'success');
    } catch (err) {
      showNotice(err instanceof Error ? err.message : 'Erreur.', 'error');
    } finally {
      setSaving(false);
    }
  }

  async function handleThemeSelect(code: string) {
    if (form.theme === code) return;
    setSavingTheme(true);
    try {
      await api.patch('/settings', { theme: code });
      setForm((f) => ({ ...f, theme: code }));
      showNotice('Thème appliqué à la boutique.', 'success');
    } catch (err) {
      showNotice(err instanceof Error ? err.message : 'Erreur lors du changement de thème.', 'error');
    } finally {
      setSavingTheme(false);
    }
  }

  if (error) return <ErrorState message={error} onRetry={refresh} />;

  return (
    <div>
      <PageHeader title="Paramètres" description="Configuration de la boutique" />

      <Card>
        <CardHeader><h3>Thème de la boutique</h3></CardHeader>
        <CardBody>
          {isLoading ? <LoadingState /> : (
            <>
              <p style={{ marginBottom: 16, color: 'var(--bo-text-muted)' }}>
                Choisissez l&apos;apparence de votre boutique en ligne. Le changement s&apos;applique immédiatement au storefront.
              </p>
              <div className="bo-theme-grid">
                {themes.map((theme) => (
                  <ThemePreviewCard
                    key={theme.id}
                    theme={theme}
                    selected={form.theme === theme.code}
                    onSelect={() => handleThemeSelect(theme.code)}
                  />
                ))}
              </div>
              {savingTheme && <p style={{ marginTop: 12, fontSize: 13, color: 'var(--bo-text-muted)' }}>Application du thème...</p>}
              {themes.length === 0 && (
                <p style={{ color: 'var(--bo-text-muted)' }}>Aucun thème disponible. Contactez l&apos;administrateur.</p>
              )}
            </>
          )}
        </CardBody>
      </Card>

      <Card>
        <CardHeader><h3>Informations générales</h3></CardHeader>
        <CardBody>
          {isLoading ? <LoadingState /> : (
            <form className="bo-form" onSubmit={handleSubmit}>
              <div className="bo-form-row">
                <FormField label="Nom"><Input value={form.name ?? ''} onChange={(e) => setForm((f) => ({ ...f, name: e.target.value }))} /></FormField>
                <FormField label="Slogan"><Input value={form.slogan ?? ''} onChange={(e) => setForm((f) => ({ ...f, slogan: e.target.value }))} /></FormField>
              </div>
              <FormField label="Description"><Textarea rows={3} value={form.description ?? ''} onChange={(e) => setForm((f) => ({ ...f, description: e.target.value }))} /></FormField>
              <div className="bo-form-row">
                <FormField label="Email contact"><Input type="email" value={form.contactEmail ?? ''} onChange={(e) => setForm((f) => ({ ...f, contactEmail: e.target.value }))} /></FormField>
                <FormField label="Téléphone"><Input value={form.contactPhone ?? ''} onChange={(e) => setForm((f) => ({ ...f, contactPhone: e.target.value }))} /></FormField>
              </div>
              <div className="bo-form-row">
                <FormField label="Adresse"><Input value={form.address ?? ''} onChange={(e) => setForm((f) => ({ ...f, address: e.target.value }))} /></FormField>
                <FormField label="Ville"><Input value={form.city ?? ''} onChange={(e) => setForm((f) => ({ ...f, city: e.target.value }))} /></FormField>
              </div>
              <div className="bo-form-row">
                <FormField label="Code postal"><Input value={form.postalCode ?? ''} onChange={(e) => setForm((f) => ({ ...f, postalCode: e.target.value }))} /></FormField>
                <FormField label="Pays"><Input value={form.country ?? ''} onChange={(e) => setForm((f) => ({ ...f, country: e.target.value }))} /></FormField>
              </div>
              <h3 style={{ marginTop: 24 }}>Apparence</h3>
              <div className="bo-form-row">
                <FormField label="Police"><Input value={form.fontFamily ?? ''} placeholder="Inter, sans-serif" onChange={(e) => setForm((f) => ({ ...f, fontFamily: e.target.value }))} /></FormField>
                <FormField label="Taille police"><Input value={form.fontSize ?? ''} placeholder="16px" onChange={(e) => setForm((f) => ({ ...f, fontSize: e.target.value }))} /></FormField>
                <FormField label="Border radius"><Input value={form.borderRadius ?? ''} placeholder="8px" onChange={(e) => setForm((f) => ({ ...f, borderRadius: e.target.value }))} /></FormField>
              </div>
              <h3 style={{ marginTop: 24 }}>Fonctionnement</h3>
              <div className="bo-form-row">
                <FormField label="Mode commande">
                  <select className="bo-input" value={form.orderMode} onChange={(e) => setForm((f) => ({ ...f, orderMode: e.target.value }))}>
                    <option value="standard">Standard</option>
                    <option value="preorder">Pré-commande</option>
                    <option value="contact">Contact uniquement</option>
                  </select>
                </FormField>
                <FormField label="Vérification email">
                  <select className="bo-input" value={form.enableEmailVerification ? 'yes' : 'no'} onChange={(e) => setForm((f) => ({ ...f, enableEmailVerification: e.target.value === 'yes' }))}>
                    <option value="yes">Activée</option><option value="no">Désactivée</option>
                  </select>
                </FormField>
              </div>
              <div className="bo-form-row">
                <label className="bo-checkbox"><input type="checkbox" checked={!!form.maintenance} onChange={(e) => setForm((f) => ({ ...f, maintenance: e.target.checked }))} /> Mode maintenance</label>
              </div>
              <div style={{ marginTop: 24 }}>
                <Button onClick={handleSubmit} disabled={saving}>{saving ? 'Enregistrement...' : 'Enregistrer'}</Button>
              </div>
            </form>
          )}
        </CardBody>
      </Card>
    </div>
  );
}
