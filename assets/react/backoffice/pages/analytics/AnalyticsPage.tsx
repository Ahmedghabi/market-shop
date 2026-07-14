import { useState, useCallback } from 'react';
import { useApiClient, useApiData } from '../../hooks/useApi';
import { Card, CardHeader, CardBody } from '../../components/Card';
import { Button } from '../../components/Button';
import { Badge } from '../../components/Badge';
import { LoadingState, ErrorState } from '../../components/States';
import { PageHeader } from '../../layout/Shell';
import { Modal } from '../../components/Modal';

type PlatformData = {
  kpis: {
    totalBoutiques: number; activeBoutiques: number; pendingBoutiques: number; newBoutiques: number;
    totalCustomers: number; totalProducts: number; totalOrders: number; ordersToday: number;
    platformRevenueCents: number; monthlyGrowthPercent: number | null;
    totalBoutiqueAdmins: number; totalEmployees: number;
  };
  subscriptions: { active: number; expired: number; expiringSoon: number };
};

type AppConfig = {
  modules: Record<string, boolean>;
};

const MODULE_INFO: Record<string, { label: string; icon: string; color: string; section: string }> = {
  analytics: { label: 'Analytiques', icon: 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z', color: '#6366f1', section: 'Analytique' },
  audit: { label: 'Audit', icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', color: '#8b5cf6', section: 'Sécurité' },
  monitoring: { label: 'Monitoring', icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z', color: '#f43f5e', section: 'Infrastructure' },
  themes: { label: 'Thèmes', icon: 'M4.098 19.902a3.75 3.75 0 005.304 0l6.401-6.402M6.75 21A3.75 3.75 0 013 17.25V4.125C3 3.504 3.504 3 4.125 3h5.25c.621 0 1.125.504 1.125 1.125v4.072M6.75 21a3.75 3.75 0 003.75-3.75V8.197M6.75 21h13.125A3.75 3.75 0 0021 17.25v-9.5A3.75 3.75 0 0017.25 4H9.75', color: '#ec4899', section: 'Apparence' },
};

function SvgIcon({ path, color }: { path: string; color: string }) {
  return (
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5} style={{ width: 20, height: 20, color }}>
      <path strokeLinecap="round" strokeLinejoin="round" d={path} />
    </svg>
  );
}

function StatRow({ label, value, color }: { label: string; value: string | number; color?: string }) {
  return (
    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', padding: '4px 0', fontSize: 13 }}>
      <span style={{ color: 'var(--bo-text-muted)' }}>{label}</span>
      <strong style={{ color: color ?? 'var(--bo-text)' }}>{value}</strong>
    </div>
  );
}

function StatBox({ label, value }: { label: string; value: string | number }) {
  return (
    <div style={{ padding: '12px 16px', borderRadius: 8, background: 'var(--bo-bg-subtle, rgba(0,0,0,0.02))', border: '1px solid var(--bo-border)' }}>
      <div style={{ fontSize: 12, color: 'var(--bo-text-muted)' }}>{label}</div>
      <div style={{ fontSize: 20, fontWeight: 700, marginTop: 4 }}>{value}</div>
    </div>
  );
}

function MonitorBox({ label, value, ok }: { label: string; value: string; ok?: boolean }) {
  return (
    <div style={{ padding: '12px 16px', borderRadius: 8, background: 'var(--bo-bg-subtle, rgba(0,0,0,0.02))', border: '1px solid var(--bo-border)' }}>
      <div style={{ fontSize: 12, color: 'var(--bo-text-muted)' }}>{label}</div>
      <div style={{ fontSize: 18, fontWeight: 700, marginTop: 4, color: ok ? 'var(--bo-success)' : 'var(--bo-text-muted)' }}>{value}</div>
    </div>
  );
}

export function AnalyticsPage({ getAccessToken }: { getAccessToken: () => string | null }) {
  const api = useApiClient(getAccessToken);
  const fetchPlatform = useCallback(() => api.get<PlatformData>('/admin/dashboard/platform'), [api]);
  const fetchConfig = useCallback(() => api.get<AppConfig>('/admin/dashboard/modules'), [api]);

  const { data: plat, isLoading, error, refresh } = useApiData(fetchPlatform);
  const { data: config } = useApiData(fetchConfig);

  const modules = config?.modules ?? {};
  const kpis = plat?.kpis;

  const [showModulesModal, setShowModulesModal] = useState(false);

  const moduleAliases: Record<string, string[]> = {
    commandes: ['orders', 'commandes'], analytics: ['analytics'],
    themes: ['themes'], audit: ['audit'], monitoring: ['monitoring'],
  };

  const isOn = (k: string) => {
    const aliases = moduleAliases[k] ?? [k];
    return aliases.some((alias) => modules[alias] === true);
  };

  const centsToTnd = (c: number) => (c / 100).toLocaleString('fr-TN', { style: 'currency', currency: 'TND' });

  return (
    <div className="bo-page">
      <PageHeader
        title="Analyse & Monitoring"
        description="Analytiques, logs, audit et surveillance de la plateforme"
        actions={
          <Button variant="primary" onClick={() => setShowModulesModal(true)}>Modules</Button>
        }
      />

      {isLoading ? <LoadingState /> : error ? <ErrorState message={error} onRetry={refresh} /> : (
        <div className="bo-page-content space-y-6">
          {/* Analytics */}
          {isOn('analytics') && (
            <Card>
              <CardHeader><span>Analytiques</span></CardHeader>
              <CardBody>
                <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(200px, 1fr))', gap: 12 }}>
                  <StatBox label="Croissance boutiques" value={kpis?.newBoutiques ?? '-'} />
                  <StatBox label="Croissance revenus" value={kpis?.monthlyGrowthPercent != null ? `${kpis.monthlyGrowthPercent >= 0 ? '+' : ''}${kpis.monthlyGrowthPercent}%` : '—'} />
                  {isOn('commandes') && <StatBox label="Croissance commandes" value={'-'} />}
                  <StatBox label="Revenu total" value={centsToTnd(kpis?.platformRevenueCents ?? 0)} />
                </div>
              </CardBody>
            </Card>
          )}

          {/* Thèmes */}
          {isOn('themes') && (
            <Card>
              <CardHeader><span>Thèmes</span></CardHeader>
              <CardBody>
                <StatRow label="Thèmes actifs" value={'-'} />
                <StatRow label="Thèmes premium" value={0} />
              </CardBody>
            </Card>
          )}

          {/* Logs & Audit */}
          {isOn('audit') && (
            <Card>
              <CardHeader><span>Logs & Audit</span></CardHeader>
              <CardBody>
                <StatRow label="Connexions (mois)" value={'-'} />
                <StatRow label="Modifications" value={0} />
                <StatRow label="Erreurs" value={0} color="var(--bo-error)" />
              </CardBody>
            </Card>
          )}

          {/* Monitoring */}
          {isOn('monitoring') && (
            <Card>
              <CardHeader><span>Monitoring</span></CardHeader>
              <CardBody>
                <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(150px, 1fr))', gap: 12 }}>
                  <MonitorBox label="CPU" value="—" />
                  <MonitorBox label="RAM" value="—" />
                  <MonitorBox label="Disque" value="—" />
                  <MonitorBox label="PostgreSQL" value="✓" ok />
                  <MonitorBox label="Redis" value="✓" ok />
                  <MonitorBox label="MongoDB" value="—" />
                  <MonitorBox label="Queue Messenger" value="—" />
                </div>
              </CardBody>
            </Card>
          )}
        </div>
      )}

      <Modal isOpen={showModulesModal} onClose={() => setShowModulesModal(false)} title="Gestion des modules">
        <div style={{ display: 'flex', flexDirection: 'column', gap: 4 }}>
          {Object.entries(MODULE_INFO).map(([key, info]) => {
            const enabled = modules[key] !== false;
            return (
              <div key={key} style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '10px 12px', borderRadius: 8, border: '1px solid var(--bo-border)' }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                  <SvgIcon path={info.icon} color={info.color} />
                  <div>
                    <div style={{ fontWeight: 600, fontSize: 14 }}>{info.label}</div>
                    <div style={{ fontSize: 11, color: 'var(--bo-text-muted)' }}>{info.section}</div>
                  </div>
                </div>
                <Badge tone={enabled ? 'success' : 'error'}>{enabled ? 'Actif' : 'Inactif'}</Badge>
              </div>
            );
          })}
        </div>
      </Modal>
    </div>
  );
}
