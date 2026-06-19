import { useEffect, useState, type FormEvent } from 'react';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { appIcons } from '../../icons/fontAwesome';
import { Badge, Button, Card, Input, Select } from '../../components/ui';

type BoutiqueOption = { id: string; name: string };
type DeliveryCompany = { id: string; name: string; isActive: boolean };
type DeliveryAccount = { id: string; deliveryCompanyId: string; deliveryCompanyName: string; isVerified: boolean; isActive: boolean; lastError: string | null };

export function DeliveryAccountsScreen({ boutique, boutiques = [], getAccessToken, onNotice }: { boutique?: BoutiqueOption; boutiques?: BoutiqueOption[]; getAccessToken?: () => string | null; onNotice?: (notice: string) => void }) {
  const [selectedBoutiqueId, setSelectedBoutiqueId] = useState(boutique?.id ?? boutiques[0]?.id ?? '');
  const [companies, setCompanies] = useState<DeliveryCompany[]>([]);
  const [accounts, setAccounts] = useState<DeliveryAccount[]>([]);
  const [form, setForm] = useState({ deliveryCompanyId: '', login: '', password: '' });

  useEffect(() => { setSelectedBoutiqueId((current) => current || boutique?.id || boutiques[0]?.id || ''); }, [boutique, boutiques]);

  async function load() {
    const token = getAccessToken?.();
    if (!token) return;
    const companiesResponse = await fetch('/api/delivery/companies', { headers: { Authorization: `Bearer ${token}` } });
    const companiesPayload = companiesResponse.ok ? await companiesResponse.json() : [];
    const nextCompanies = (Array.isArray(companiesPayload) ? companiesPayload : companiesPayload.member ?? companiesPayload.items ?? []) as DeliveryCompany[];
    setCompanies(nextCompanies.filter((company) => company.isActive));
    if (selectedBoutiqueId) {
      const accountsResponse = await fetch(`/api/boutiques/${selectedBoutiqueId}/delivery-accounts`, { headers: { Authorization: `Bearer ${token}` } });
      const accountsPayload = accountsResponse.ok ? await accountsResponse.json() : [];
      setAccounts(Array.isArray(accountsPayload) ? accountsPayload : accountsPayload.member ?? accountsPayload.items ?? []);
    }
  }

  useEffect(() => { void load(); }, [getAccessToken, selectedBoutiqueId]);

  async function submit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    const token = getAccessToken?.();
    if (!token || !selectedBoutiqueId) return;
    const response = await fetch(`/api/boutiques/${selectedBoutiqueId}/delivery-accounts`, { method: 'POST', headers: { Authorization: `Bearer ${token}`, 'Content-Type': 'application/json' }, body: JSON.stringify(form) });
    onNotice?.(response.ok ? 'Compte livraison ajouté.' : `Ajout impossible (${response.status}).`);
    if (response.ok) { setForm({ deliveryCompanyId: '', login: '', password: '' }); await load(); }
  }

  async function verify(accountId: string) {
    const token = getAccessToken?.();
    if (!token || !selectedBoutiqueId) return;
    const response = await fetch(`/api/boutiques/${selectedBoutiqueId}/delivery-accounts/${accountId}/verify`, { method: 'POST', headers: { Authorization: `Bearer ${token}` } });
    onNotice?.(response.ok ? 'Compte vérifié.' : `Vérification impossible (${response.status}).`);
    if (response.ok) await load();
  }

  return (
    <section className="space-y-6">
      <Card className="ds-hero"><p className="ds-hero__eyebrow">Livraison</p><h1 className="ds-hero__title">Comptes livraison boutique</h1><p className="ds-hero__subtitle">L’admin boutique choisit parmi les transporteurs activés par le super admin.</p></Card>
      <Card><h2 className="text-xl font-bold">Boutique</h2><Select className="mt-4" value={selectedBoutiqueId} onChange={(e) => setSelectedBoutiqueId(e.target.value)}>{boutiques.map((item) => <option key={item.id} value={item.id}>{item.name}</option>)}</Select></Card>
      <Card><h2 className="text-xl font-bold">Comptes configurés</h2><div className="mt-4 space-y-3">{accounts.map((account) => <div key={account.id} className="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-[color:var(--ds-outline-variant)] bg-white p-4"><div><strong>{account.deliveryCompanyName}</strong><p className="text-sm text-[color:var(--ds-on-surface-variant)]">{account.lastError ?? 'Aucune erreur'}</p></div><div className="flex gap-2"><Badge tone={account.isVerified ? 'success' : 'warning'}>{account.isVerified ? 'Vérifié' : 'Non vérifié'}</Badge><Badge tone={account.isActive ? 'success' : 'neutral'}>{account.isActive ? 'Actif' : 'Inactif'}</Badge><Button variant="secondary" onClick={() => verify(account.id)}>Vérifier</Button></div></div>)}{accounts.length === 0 && <p className="text-sm text-[color:var(--ds-on-surface-variant)]">Aucun compte configuré pour cette boutique.</p>}</div></Card>
      <Card><h2 className="text-xl font-bold">Ajouter un compte</h2><form className="mt-4 grid gap-4" onSubmit={submit}><Select required value={form.deliveryCompanyId} onChange={(e) => setForm((c) => ({ ...c, deliveryCompanyId: e.target.value }))}><option value="">Sélectionner un transporteur</option>{companies.map((company) => <option key={company.id} value={company.id}>{company.name}</option>)}</Select><Input required placeholder="Login / email" value={form.login} onChange={(e) => setForm((c) => ({ ...c, login: e.target.value }))} /><Input required type="password" placeholder="Mot de passe" value={form.password} onChange={(e) => setForm((c) => ({ ...c, password: e.target.value }))} /><Button type="submit" variant="primary"><FontAwesomeIcon icon={appIcons.plus} /> Ajouter</Button></form></Card>
    </section>
  );
}
