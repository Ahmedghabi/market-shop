import { useEffect, useState, type FormEvent } from 'react';
import { Badge, Button, Card, Input, Select } from '../../components/ui';

type BoutiqueOption = { id: string; name: string };
type SubscriptionPlan = { id: string; label: string; durationMonths: number; priceDt: number; isFree: boolean; isActive: boolean };
type SubscriptionRecord = { id: string; boutiqueId: string; boutiqueName?: string | null; plan: string; status: string; startDate?: string | null; endDate?: string | null; priceCents: number };

const planStorageKey = 'hanooty_subscription_plans';
const defaultPlans: SubscriptionPlan[] = [
  { id: 'free', label: 'Gratuit', durationMonths: 1, priceDt: 0, isFree: true, isActive: true },
  { id: '3months', label: '3 mois', durationMonths: 3, priceDt: 30, isFree: false, isActive: true },
  { id: '6months', label: '6 mois', durationMonths: 6, priceDt: 50, isFree: false, isActive: true },
  { id: '1year', label: '1 an', durationMonths: 12, priceDt: 90, isFree: false, isActive: true },
];

export function SubscriptionsScreen({ boutiques = [], getAccessToken, isSuperAdmin = false, onNotice }: { boutiques?: BoutiqueOption[]; getAccessToken?: () => string | null; isSuperAdmin?: boolean; onNotice?: (notice: string) => void }) {
  const [plans, setPlans] = useState<SubscriptionPlan[]>(() => {
    const stored = window.localStorage.getItem(planStorageKey);
    if (!stored) return defaultPlans;
    try { return JSON.parse(stored) as SubscriptionPlan[]; } catch { return defaultPlans; }
  });
  const [form, setForm] = useState({ label: '', durationMonths: 1, priceDt: 0, isFree: false });
  const [selectedBoutiqueId, setSelectedBoutiqueId] = useState(boutiques[0]?.id ?? '');
  const [selectedPlanId, setSelectedPlanId] = useState('free');
  const [subscriptions, setSubscriptions] = useState<SubscriptionRecord[]>([]);

  useEffect(() => { window.localStorage.setItem(planStorageKey, JSON.stringify(plans)); }, [plans]);

  useEffect(() => {
    setSelectedBoutiqueId((current) => current || boutiques[0]?.id || '');
  }, [boutiques]);

  useEffect(() => {
    const token = getAccessToken?.();
    if (!token || !selectedBoutiqueId) {
      setSubscriptions([]);
      return;
    }

    fetch(`/api/boutiques/${selectedBoutiqueId}/subscriptions`, { headers: { Authorization: `Bearer ${token}` } })
      .then((response) => response.ok ? response.json() : [])
      .then((payload) => setSubscriptions(Array.isArray(payload) ? payload : payload.member ?? payload.items ?? []))
      .catch(() => setSubscriptions([]));
  }, [getAccessToken, selectedBoutiqueId]);

  function createPlan(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    const id = form.label.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
    if (!id) return;
    setPlans((current) => [...current.filter((plan) => plan.id !== id), { id, label: form.label, durationMonths: Number(form.durationMonths), priceDt: form.isFree ? 0 : Number(form.priceDt), isFree: form.isFree, isActive: true }]);
    setForm({ label: '', durationMonths: 1, priceDt: 0, isFree: false });
    onNotice?.('Plan d’abonnement enregistré.');
  }

  async function createSubscription() {
    const token = getAccessToken?.();
    if (!token || !selectedBoutiqueId) return;
    const apiPlan = ['free', '3months', '6months', '1year'].includes(selectedPlanId) ? selectedPlanId : 'free';
    const response = await fetch(`/api/boutiques/${selectedBoutiqueId}/subscriptions`, { method: 'POST', headers: { Authorization: `Bearer ${token}`, 'Content-Type': 'application/json' }, body: JSON.stringify({ plan: apiPlan }) });
    onNotice?.(response.ok ? 'Abonnement créé.' : `Création abonnement impossible (${response.status}).`);
  }

  async function transition(sub: SubscriptionRecord, action: 'accept' | 'reject') {
    const token = getAccessToken?.();
    if (!token) return;
    const response = await fetch(`/api/boutiques/${sub.boutiqueId}/subscriptions/${sub.id}/${action}`, { method: 'PATCH', headers: { Authorization: `Bearer ${token}` } });
    onNotice?.(response.ok ? `Abonnement ${action === 'accept' ? 'activé' : 'désactivé'}.` : `Action impossible (${response.status}).`);
  }

  return (
    <section className="space-y-6">
      <Card className="ds-hero">
        <div className="flex flex-wrap items-center justify-between gap-4">
          <div>
            <p className="ds-hero__eyebrow">Abonnements</p>
            <h1 className="ds-hero__title">Gestion des abonnements</h1>
            <p className="ds-hero__subtitle">Créer les plans, fixer délai/prix en DT, gratuit, activation et désactivation.</p>
          </div>
          <div className="flex gap-2"><Badge tone="success">{plans.filter((p) => p.isActive).length} actifs</Badge><Badge tone="neutral">{plans.length} plans</Badge></div>
        </div>
      </Card>

      {isSuperAdmin && (
        <Card>
          <h2 className="text-xl font-bold">Plans super admin</h2>
          <form className="mt-4 grid gap-4 lg:grid-cols-[1fr_140px_140px_auto_auto]" onSubmit={createPlan}>
            <Input required placeholder="Nom du plan" value={form.label} onChange={(event) => setForm((current) => ({ ...current, label: event.target.value }))} />
            <Input required type="number" min={1} value={form.durationMonths} onChange={(event) => setForm((current) => ({ ...current, durationMonths: Number(event.target.value) }))} />
            <Input required type="number" min={0} value={form.priceDt} disabled={form.isFree} onChange={(event) => setForm((current) => ({ ...current, priceDt: Number(event.target.value) }))} />
            <label className="flex items-center gap-2 text-sm"><input type="checkbox" checked={form.isFree} onChange={(event) => setForm((current) => ({ ...current, isFree: event.target.checked, priceDt: event.target.checked ? 0 : current.priceDt }))} /> Gratuit</label>
            <Button type="submit" variant="primary">Créer</Button>
          </form>
        </Card>
      )}

      <Card>
        <h2 className="text-xl font-bold">Plans disponibles</h2>
        <div className="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
          {plans.map((plan) => (
            <article key={plan.id} className="rounded-2xl border border-[color:var(--ds-outline-variant)] bg-white p-4">
              <div className="flex items-start justify-between gap-3"><strong>{plan.label}</strong><Badge tone={plan.isActive ? 'success' : 'neutral'}>{plan.isActive ? 'Actif' : 'Inactif'}</Badge></div>
              <p className="mt-2 text-sm text-[color:var(--ds-on-surface-variant)]">{plan.durationMonths} mois · {plan.isFree ? 'Gratuit' : `${plan.priceDt} DT`}</p>
              {isSuperAdmin && <Button variant="secondary" className="mt-4 w-full" onClick={() => setPlans((current) => current.map((item) => item.id === plan.id ? { ...item, isActive: !item.isActive } : item))}>{plan.isActive ? 'Désactiver' : 'Activer'}</Button>}
            </article>
          ))}
        </div>
      </Card>

      <Card>
        <h2 className="text-xl font-bold">Abonnement boutique</h2>
        <div className="mt-4 grid gap-4 md:grid-cols-[1fr_1fr_auto]">
          <Select value={selectedBoutiqueId} onChange={(event) => setSelectedBoutiqueId(event.target.value)}>{boutiques.map((boutique) => <option key={boutique.id} value={boutique.id}>{boutique.name}</option>)}</Select>
          <Select value={selectedPlanId} onChange={(event) => setSelectedPlanId(event.target.value)}>{plans.filter((plan) => plan.isActive).map((plan) => <option key={plan.id} value={plan.id}>{plan.label} - {plan.isFree ? 'Gratuit' : `${plan.priceDt} DT`}</option>)}</Select>
          <Button variant="primary" onClick={createSubscription}>Créer abonnement</Button>
        </div>
        <div className="mt-6 space-y-3">
          {subscriptions.map((sub) => <div key={sub.id} className="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-[color:var(--ds-outline-variant)] bg-white p-4"><div><strong>{sub.boutiqueName ?? selectedBoutiqueId}</strong><p className="text-sm text-[color:var(--ds-on-surface-variant)]">{sub.plan} · {(sub.priceCents / 100).toFixed(2)} DT</p></div><div className="flex gap-2"><Badge tone={sub.status === 'active' ? 'success' : sub.status === 'pending' ? 'warning' : 'neutral'}>{sub.status}</Badge>{isSuperAdmin && sub.status === 'pending' && <Button variant="secondary" onClick={() => transition(sub, 'accept')}>Activer</Button>}{isSuperAdmin && sub.status === 'pending' && <Button variant="ghost" onClick={() => transition(sub, 'reject')}>Désactiver</Button>}</div></div>)}
          {subscriptions.length === 0 && <p className="text-sm text-[color:var(--ds-on-surface-variant)]">Aucun abonnement pour cette boutique.</p>}
        </div>
      </Card>
    </section>
  );
}
