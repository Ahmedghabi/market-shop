import { useEffect, useState } from 'react';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { appIcons } from '../../icons/fontAwesome';
import { Badge, Button, Card, Input } from '../../components/ui';

type NotificationRecord = {
  id: string;
  type: string;
  title: string;
  message: string;
  boutiqueId?: string | null;
  read: boolean;
  createdAt: string;
};

export function SuperAdminDashboardScreen({
  boutiquesCount = 0,
  getAccessToken,
  notifications = [],
}: {
  boutiquesCount?: number;
  getAccessToken: () => string | null;
  notifications?: NotificationRecord[];
}) {
  const [appPixelId, setAppPixelId] = useState('');
  const [pixelSaving, setPixelSaving] = useState(false);
  const [pixelSaved, setPixelSaved] = useState(false);
  const [alertsVisible, setAlertsVisible] = useState(false);
  const unreadAlerts = notifications.filter((notification) => !notification.read);

  async function saveAppPixel() {
    setPixelSaving(true);
    setPixelSaved(false);
    const token = getAccessToken();
    if (!token) {
      setPixelSaving(false);
      return;
    }

    try {
      const response = await fetch('/api/admin/app-config', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Authorization: `Bearer ${token}` },
        body: JSON.stringify({ app_meta_pixel_id: appPixelId }),
      });
      if (response.ok) {
        setPixelSaved(true);
        setTimeout(() => setPixelSaved(false), 3000);
      }
    } catch {
      // silent
    }
    setPixelSaving(false);
  }

  useEffect(() => {
    const token = getAccessToken();
    if (!token) {
      return;
    }

    fetch('/api/admin/app-config', { headers: { Authorization: `Bearer ${token}` } })
      .then((res) => res.ok ? res.json() as Promise<{ app_meta_pixel_id?: string }> : null)
      .then((data) => {
        if (data?.app_meta_pixel_id) {
          setAppPixelId(data.app_meta_pixel_id);
        }
      })
      .catch(() => {});
  }, [getAccessToken]);
  return (
    <section className="space-y-6">
      <Card className="ds-hero">
        <div className="flex flex-wrap items-start justify-between gap-4">
          <div>
            <p className="ds-hero__eyebrow">Super Admin</p>
            <h1 className="ds-hero__title">Tableau de bord Super Admin</h1>
            <p className="ds-hero__subtitle">Pilotage plateforme, boutiques, revenus et santé du SaaS.</p>
          </div>
          <Badge tone="success"><FontAwesomeIcon icon={appIcons.security} /> Healthy</Badge>
        </div>
      </Card>
      <div className="ds-grid ds-grid--cards">
        <Card className="bg-[color:var(--ds-surface-container-lowest)]/90 p-5"><p className="text-sm text-[color:var(--ds-on-surface-variant)]">Boutiques</p><h2 className="mt-2 text-3xl font-bold">{boutiquesCount}</h2></Card>
        <Card className="bg-[color:var(--ds-surface-container-lowest)]/90 p-5"><p className="text-sm text-[color:var(--ds-on-surface-variant)]">Revenu</p><h2 className="mt-2 text-3xl font-bold">12,450 €</h2></Card>
        <Card className="bg-[color:var(--ds-surface-container-lowest)]/90 p-5"><p className="text-sm text-[color:var(--ds-on-surface-variant)]">SLA</p><h2 className="mt-2 text-3xl font-bold">99.9%</h2></Card>
        <Card className="bg-[color:var(--ds-surface-container-lowest)]/90 p-5"><p className="text-sm text-[color:var(--ds-on-surface-variant)]">Incidents</p><h2 className="mt-2 text-3xl font-bold">0</h2></Card>
      </div>
      <Card>
        <div className="flex flex-wrap items-center justify-between gap-3">
          <div>
            <h2 className="text-xl font-bold">Santé plateforme</h2>
            <p className="text-sm text-[color:var(--ds-on-surface-variant)]">Abonnements, boutiques et delivery process.</p>
          </div>
          <Button variant="secondary" onClick={() => setAlertsVisible((visible) => !visible)}>
            {alertsVisible ? 'Masquer les alertes' : 'Voir les alertes'}
          </Button>
        </div>
        {alertsVisible && (
          <div className="mt-5 grid gap-3">
            {(unreadAlerts.length > 0 ? unreadAlerts : notifications).length > 0 ? (
              (unreadAlerts.length > 0 ? unreadAlerts : notifications).map((notification) => (
                <article key={notification.id} className="rounded-2xl border border-[color:var(--ds-outline-variant)] bg-white p-4">
                  <div className="flex flex-wrap items-start justify-between gap-3">
                    <div>
                      <strong>{notification.title}</strong>
                      <p className="mt-1 text-sm text-[color:var(--ds-on-surface-variant)]">{notification.message}</p>
                    </div>
                    <Badge tone={notification.read ? 'neutral' : 'warning'}>{notification.read ? 'Lu' : 'Non lu'}</Badge>
                  </div>
                </article>
              ))
            ) : (
              <div className="rounded-2xl border border-dashed border-[color:var(--ds-outline-variant)] bg-white p-4 text-sm text-[color:var(--ds-on-surface-variant)]">
                Aucune alerte plateforme pour le moment.
              </div>
            )}
          </div>
        )}
      </Card>

      <Card>
        <h2 className="text-xl font-bold">Meta Pixel applicatif</h2>
        <p className="mt-2 text-sm text-[color:var(--ds-on-surface-variant)]">
          ID Pixel Facebook global tiré sur toutes les boutiques (super admin).
        </p>
        <div className="mt-4 grid gap-4">
          <Input
            placeholder="Ex: 123456789012345"
            value={appPixelId}
            onChange={(e) => { setAppPixelId(e.target.value); setPixelSaved(false); }}
          />
          <div className="flex items-center gap-3">
            <Button variant="primary" disabled={pixelSaving} onClick={saveAppPixel}>
              {pixelSaving ? 'Enregistrement...' : 'Enregistrer'}
            </Button>
            {pixelSaved && <span className="text-sm text-green-600">Enregistré</span>}
          </div>
        </div>
      </Card>
    </section>
  );
}
