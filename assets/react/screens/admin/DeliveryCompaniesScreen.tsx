import { useEffect, useState, type FormEvent } from 'react';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { appIcons } from '../../icons/fontAwesome';
import { Badge, Button, Card, Input } from '../../components/ui';

type DeliveryCompany = { id: string; slug: string; name: string; baseUrl: string; authEndpoint?: string | null; submitOrderEndpoint?: string | null; trackEndpoint?: string | null; description?: string | null; isActive: boolean };

export function DeliveryCompaniesScreen({ getAccessToken, isSuperAdmin = false, onNotice }: { getAccessToken?: () => string | null; isSuperAdmin?: boolean; onNotice?: (notice: string) => void }) {
  const [companies, setCompanies] = useState<DeliveryCompany[]>([]);
  const [form, setForm] = useState({ name: '', slug: '', baseUrl: '', authEndpoint: '', submitOrderEndpoint: '/orders', trackEndpoint: '', description: '', isActive: true });

  async function loadCompanies() {
    const token = getAccessToken?.();
    if (!token) return;
    const response = await fetch('/api/delivery/companies', { headers: { Authorization: `Bearer ${token}` } });
    const payload = response.ok ? await response.json() : [];
    setCompanies(Array.isArray(payload) ? payload : payload.member ?? payload.items ?? []);
  }

  useEffect(() => { void loadCompanies(); }, [getAccessToken]);

  async function submit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    const token = getAccessToken?.();
    if (!token || !isSuperAdmin) return;
    const response = await fetch('/api/delivery/companies', { method: 'POST', headers: { Authorization: `Bearer ${token}`, 'Content-Type': 'application/json' }, body: JSON.stringify({ ...form, authEndpoint: form.authEndpoint || null, trackEndpoint: form.trackEndpoint || null, description: form.description || null }) });
    onNotice?.(response.ok ? 'Transporteur créé.' : `Création transporteur impossible (${response.status}).`);
    if (response.ok) { setForm({ name: '', slug: '', baseUrl: '', authEndpoint: '', submitOrderEndpoint: '/orders', trackEndpoint: '', description: '', isActive: true }); await loadCompanies(); }
  }

  return (
    <section className="space-y-6">
      <Card className="ds-hero"><p className="ds-hero__eyebrow">Livraison</p><h1 className="ds-hero__title">Transporteurs</h1><p className="ds-hero__subtitle">Gestion super admin des sociétés de livraison disponibles pour les boutiques.</p></Card>
      <Card>
        <div className="flex items-center justify-between"><h2 className="text-xl font-bold">Sociétés de livraison</h2><Badge tone={isSuperAdmin ? 'success' : 'neutral'}>{isSuperAdmin ? 'Gestion super admin' : 'Lecture seule'}</Badge></div>
        <div className="mt-6 overflow-x-auto"><table className="ds-table"><thead><tr><th>Nom</th><th>Slug</th><th>URL</th><th>Endpoint commande</th><th>Statut</th></tr></thead><tbody>{companies.map((c) => <tr key={c.id}><td className="font-medium">{c.name}</td><td>{c.slug}</td><td>{c.baseUrl}</td><td>{c.submitOrderEndpoint ?? '-'}</td><td><Badge tone={c.isActive ? 'success' : 'neutral'}>{c.isActive ? 'Actif' : 'Inactif'}</Badge></td></tr>)}</tbody></table>{companies.length === 0 && <p className="mt-4 text-sm text-[color:var(--ds-on-surface-variant)]">Aucun transporteur configuré.</p>}</div>
      </Card>
      {isSuperAdmin && <Card><h2 className="text-xl font-bold">Créer un transporteur</h2><form className="mt-4 grid gap-4" onSubmit={submit}><Input required placeholder="Nom" value={form.name} onChange={(e) => setForm((c) => ({ ...c, name: e.target.value }))} /><Input required placeholder="slug" value={form.slug} onChange={(e) => setForm((c) => ({ ...c, slug: e.target.value }))} /><Input required placeholder="URL de base" value={form.baseUrl} onChange={(e) => setForm((c) => ({ ...c, baseUrl: e.target.value }))} /><Input placeholder="Endpoint auth" value={form.authEndpoint} onChange={(e) => setForm((c) => ({ ...c, authEndpoint: e.target.value }))} /><Input required placeholder="Endpoint commande" value={form.submitOrderEndpoint} onChange={(e) => setForm((c) => ({ ...c, submitOrderEndpoint: e.target.value }))} /><Input placeholder="Endpoint tracking" value={form.trackEndpoint} onChange={(e) => setForm((c) => ({ ...c, trackEndpoint: e.target.value }))} /><label className="flex items-center gap-2 text-sm"><input type="checkbox" checked={form.isActive} onChange={(e) => setForm((c) => ({ ...c, isActive: e.target.checked }))} /> Actif</label><Button type="submit" variant="primary"><FontAwesomeIcon icon={appIcons.plus} /> Ajouter le transporteur</Button></form></Card>}
    </section>
  );
}
