import { useState, useCallback } from 'react';
import { useApiClient, useApiData } from '../../hooks/useApi';
import { Card, CardBody } from '../../components/Card';
import { Button } from '../../components/Button';
import { Badge } from '../../components/Badge';
import { LoadingState, ErrorState } from '../../components/States';
import { PageHeader } from '../../layout/Shell';
import { useNotification } from '../../hooks/useNotification';
import { Modal } from '../../components/Modal';
import { ConfirmDialog } from '../../components/ConfirmDialog';
import { FormField, Input, Select, Textarea } from '../../components/FormField';
import { useBoutique } from '../../hooks/useBoutique';
import { BoutiqueFormSelect, resolveFormBoutiqueId } from '../../components/BoutiqueFormSelect';

type SubscriptionPlan = {
  id: string;
  name: string;
  description?: string | null;
  priceTnd: number;
  durationMonths: number;
  isFree: boolean;
  modules?: string[] | null;
  isActive: boolean;
  isVisible: boolean;
};

type SubscriptionPlanForm = {
  name: string;
  description: string;
  priceTnd: string;
  durationMonths: string;
  isFree: boolean;
  isActive: boolean;
  isVisible: boolean;
  modules: string;
};

type Subscription = {
  id: string;
  plan?: string;
  status: string;
  startDate?: string;
  endDate?: string;
  createdAt: string;
};

const emptyForm: SubscriptionPlanForm = {
  name: '',
  description: '',
  priceTnd: '0',
  durationMonths: '1',
  isFree: false,
  isActive: true,
  isVisible: true,
  modules: '',
};

export function SubscriptionsPage({ getAccessToken }: { getAccessToken: () => string | null }) {
  const api = useApiClient(getAccessToken);
  const { showNotice } = useNotification();
  const { boutique } = useBoutique();
  const [modalOpen, setModalOpen] = useState(false);
  const [editing, setEditing] = useState<SubscriptionPlan | null>(null);
  const [form, setForm] = useState<SubscriptionPlanForm>(emptyForm);
  const [saving, setSaving] = useState(false);
  const [deleteId, setDeleteId] = useState<string | null>(null);
  const [subscriptionModalOpen, setSubscriptionModalOpen] = useState(false);
  const [subscriptionBoutiqueId, setSubscriptionBoutiqueId] = useState('');
  const [subscriptionPlanId, setSubscriptionPlanId] = useState('');
  const [savingSubscriptionRequest, setSavingSubscriptionRequest] = useState(false);

  const fetchPlans = useCallback(() => api.getCollection<SubscriptionPlan>('/admin/subscription-plans'), [api]);
  const fetchSub = useCallback(() => api.get<{ member: Subscription[] }>('/subscriptions').catch(() => ({ member: [] })), [api]);

  const { data: plans, isLoading: plansLoading, error: plansError, refresh: refreshPlans } = useApiData(fetchPlans, []);
  const { data: subsData } = useApiData(fetchSub, []);

  const plansList = plans?.member ?? [];
  const subscriptions = subsData?.member ?? [];

  const openCreate = () => {
    setEditing(null);
    setForm(emptyForm);
    setModalOpen(true);
  };

  const openEdit = (plan: SubscriptionPlan) => {
    setEditing(plan);
    setForm({
      name: plan.name,
      description: plan.description ?? '',
      priceTnd: String(plan.priceTnd ?? 0),
      durationMonths: String(plan.durationMonths ?? 1),
      isFree: plan.isFree,
      isActive: plan.isActive,
      isVisible: plan.isVisible,
      modules: (plan.modules ?? []).join(', '),
    });
    setModalOpen(true);
  };

  const payloadFromForm = () => ({
    name: form.name.trim(),
    description: form.description.trim() || null,
    durationMonths: Math.max(1, Number.parseInt(form.durationMonths, 10) || 1),
    priceTnd: form.isFree ? 0 : Math.max(0, Number.parseInt(form.priceTnd, 10) || 0),
    isFree: form.isFree,
    isVisible: form.isVisible,
    isActive: form.isActive,
    modules: form.modules.split(',').map((module) => module.trim()).filter(Boolean),
  });

  const savePlan = async () => {
    if (!form.name.trim()) {
      showNotice('Le nom du plan est obligatoire', 'error');
      return;
    }

    setSaving(true);
    try {
      const payload = payloadFromForm();
      if (editing) {
        await api.patch(`/admin/subscription-plans/${editing.id}`, payload);
        showNotice('Plan modifié', 'success');
      } else {
        await api.post('/admin/subscription-plans', payload);
        showNotice('Plan créé', 'success');
      }
      setModalOpen(false);
      refreshPlans();
    } catch (error) {
      showNotice(error instanceof Error ? error.message : 'Erreur lors de la sauvegarde', 'error');
    } finally {
      setSaving(false);
    }
  };

  const updatePlan = async (plan: SubscriptionPlan, changes: Partial<SubscriptionPlan>) => {
    try {
      await api.patch(`/admin/subscription-plans/${plan.id}`, {
        name: plan.name,
        description: plan.description ?? null,
        durationMonths: plan.durationMonths,
        priceTnd: plan.priceTnd,
        isFree: plan.isFree,
        isVisible: plan.isVisible,
        isActive: plan.isActive,
        modules: plan.modules ?? [],
        ...changes,
      });
      showNotice('Plan mis à jour', 'success');
      refreshPlans();
    } catch (error) {
      showNotice(error instanceof Error ? error.message : 'Erreur lors de la mise à jour', 'error');
    }
  };

  const deletePlan = async () => {
    if (!deleteId) return;
    try {
      await api.delete(`/admin/subscription-plans/${deleteId}`);
      showNotice('Plan supprimé', 'success');
      setDeleteId(null);
      refreshPlans();
    } catch (error) {
      showNotice(error instanceof Error ? error.message : 'Erreur lors de la suppression', 'error');
    }
  };

  const openSubscriptionChange = () => {
    setSubscriptionBoutiqueId('');
    setSubscriptionPlanId('');
    setSubscriptionModalOpen(true);
  };

  const requestSubscriptionChange = async () => {
    const boutiqueId = resolveFormBoutiqueId(boutique?.id, subscriptionBoutiqueId);
    if (!boutiqueId) {
      showNotice('Sélectionnez une boutique.', 'error');
      return;
    }
    if (!subscriptionPlanId) {
      showNotice('Sélectionnez un plan.', 'error');
      return;
    }

    setSavingSubscriptionRequest(true);
    try {
      await api.post('/subscription-requests', { boutiqueId, subscriptionPlanId });
      showNotice('Demande de modification envoyée', 'success');
      setSubscriptionModalOpen(false);
    } catch (error) {
      showNotice(error instanceof Error ? error.message : 'Erreur lors de la demande', 'error');
    } finally {
      setSavingSubscriptionRequest(false);
    }
  };

  return (
    <div>
      <PageHeader
        title="Abonnements"
        description="Gestion Super Admin des plans: prix, durée, publication, activation et modules inclus."
        actions={<Button variant="primary" onClick={openCreate}>Nouveau plan</Button>}
      />
      <Card>
        <CardBody>
          {plansLoading ? <LoadingState /> : plansError ? <ErrorState message={plansError} onRetry={refreshPlans} /> : (
            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(300px, 1fr))', gap: 20 }}>
              {plansList.map((plan) => (
                <div key={plan.id} className="bo-card" style={{ padding: 24, border: '1px solid var(--bo-border)', borderRadius: 12 }}>
                  <div style={{ display: 'flex', justifyContent: 'space-between', gap: 12, alignItems: 'flex-start' }}>
                    <div>
                      <h3 style={{ margin: '0 0 8px' }}>{plan.name}</h3>
                      {plan.description && <p style={{ fontSize: 14, color: 'var(--bo-text-secondary)', margin: '0 0 16px' }}>{plan.description}</p>}
                    </div>
                    <div style={{ display: 'flex', gap: 6, flexWrap: 'wrap', justifyContent: 'flex-end' }}>
                      <Badge tone={plan.isActive ? 'success' : 'neutral'}>{plan.isActive ? 'Actif' : 'Inactif'}</Badge>
                      <Badge tone={plan.isVisible ? 'success' : 'warning'}>{plan.isVisible ? 'Publié' : 'Masqué'}</Badge>
                    </div>
                  </div>
                  <div style={{ fontSize: 28, fontWeight: 700, marginBottom: 12 }}>
                    {plan.isFree ? 'Gratuit' : `${plan.priceTnd.toFixed(0)} TND`}
                    <span style={{ fontSize: 14, fontWeight: 400, color: 'var(--bo-text-muted)' }}>/{plan.durationMonths} mois</span>
                  </div>
                  {plan.modules && plan.modules.length > 0 && (
                    <div style={{ display: 'flex', flexWrap: 'wrap', gap: 6, marginBottom: 16 }}>
                      {plan.modules.map((m) => <Badge key={m} tone="neutral">{m}</Badge>)}
                    </div>
                  )}
                  <div style={{ display: 'flex', flexWrap: 'wrap', gap: 8, marginTop: 16 }}>
                    <Button variant="secondary" size="sm" onClick={() => openEdit(plan)}>Modifier</Button>
                    <Button variant="ghost" size="sm" onClick={() => updatePlan(plan, { isActive: !plan.isActive })}>{plan.isActive ? 'Désactiver' : 'Activer'}</Button>
                    <Button variant="ghost" size="sm" onClick={() => updatePlan(plan, { isVisible: !plan.isVisible })}>{plan.isVisible ? 'Dépublier' : 'Publier'}</Button>
                    <Button variant="danger" size="sm" onClick={() => setDeleteId(plan.id)}>Supprimer</Button>
                  </div>
                </div>
              ))}
            </div>
          )}
        </CardBody>
      </Card>

      {subscriptions.length > 0 && (
        <div style={{ marginTop: 24 }}>
          <h3 style={{ marginBottom: 12 }}>Mon abonnement</h3>
          <Card>
            <CardBody>
              {subscriptions.map((sub) => (
                <div key={sub.id} style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', padding: '12px 0', borderBottom: '1px solid var(--bo-border)' }}>
                  <div>
                    <strong>{sub.plan ?? 'N/A'}</strong>
                    <div style={{ fontSize: 13, color: 'var(--bo-text-secondary)' }}>
                      {sub.startDate ? `Du ${new Date(sub.startDate).toLocaleDateString('fr-FR')}` : ''}
                      {sub.endDate ? ` au ${new Date(sub.endDate).toLocaleDateString('fr-FR')}` : ''}
                    </div>
                  </div>
                  <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                    <Badge tone={sub.status === 'active' ? 'success' : 'neutral'}>{sub.status}</Badge>
                    <Button variant="secondary" size="sm" onClick={openSubscriptionChange}>Modifier abonnement</Button>
                  </div>
                </div>
              ))}
            </CardBody>
          </Card>
        </div>
      )}

      <Modal
        isOpen={modalOpen}
        onClose={() => setModalOpen(false)}
        title={editing ? 'Modifier le plan' : 'Créer un plan'}
        width="620px"
        footer={(
          <>
            <Button variant="secondary" onClick={() => setModalOpen(false)}>Annuler</Button>
            <Button variant="primary" onClick={savePlan} disabled={saving}>{saving ? 'En cours...' : editing ? 'Enregistrer' : 'Créer'}</Button>
          </>
        )}
      >
        <div style={{ display: 'grid', gap: 14 }}>
          <FormField label="Nom" required>
            <Input value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} placeholder="Business 12 mois" />
          </FormField>
          <FormField label="Description">
            <Textarea value={form.description} onChange={(e) => setForm({ ...form, description: e.target.value })} rows={3} />
          </FormField>
          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 12 }}>
            <FormField label="Durée (mois)" required>
              <Input type="number" min={1} value={form.durationMonths} onChange={(e) => setForm({ ...form, durationMonths: e.target.value })} />
            </FormField>
            <FormField label="Prix TND" required>
              <Input type="number" min={0} disabled={form.isFree} value={form.priceTnd} onChange={(e) => setForm({ ...form, priceTnd: e.target.value })} />
            </FormField>
          </div>
          <FormField label="Modules" hint="Codes séparés par des virgules, ex: reviews, coupons, pos">
            <Input value={form.modules} onChange={(e) => setForm({ ...form, modules: e.target.value })} />
          </FormField>
          <div style={{ display: 'flex', flexWrap: 'wrap', gap: 16 }}>
            <label><input type="checkbox" checked={form.isFree} onChange={(e) => setForm({ ...form, isFree: e.target.checked, priceTnd: e.target.checked ? '0' : form.priceTnd })} /> Gratuit</label>
            <label><input type="checkbox" checked={form.isActive} onChange={(e) => setForm({ ...form, isActive: e.target.checked })} /> Actif</label>
            <label><input type="checkbox" checked={form.isVisible} onChange={(e) => setForm({ ...form, isVisible: e.target.checked })} /> Publié</label>
          </div>
        </div>
      </Modal>

      <Modal
        isOpen={subscriptionModalOpen}
        onClose={() => setSubscriptionModalOpen(false)}
        title="Modifier abonnement"
        width="520px"
        footer={(
          <>
            <Button variant="secondary" onClick={() => setSubscriptionModalOpen(false)}>Annuler</Button>
            <Button variant="primary" onClick={requestSubscriptionChange} disabled={savingSubscriptionRequest}>{savingSubscriptionRequest ? 'Envoi...' : 'Envoyer la demande'}</Button>
          </>
        )}
      >
        <div style={{ display: 'grid', gap: 14 }}>
          <BoutiqueFormSelect value={subscriptionBoutiqueId} onChange={setSubscriptionBoutiqueId} />
          <FormField label="Nouveau plan" required>
            <Select required value={subscriptionPlanId} onChange={(event) => setSubscriptionPlanId(event.target.value)}>
              <option value="">Sélectionner un plan</option>
              {plansList.filter((plan) => plan.isActive && plan.isVisible).map((plan) => (
                <option key={plan.id} value={plan.id}>{plan.name} - {plan.isFree ? 'Gratuit' : `${plan.priceTnd} TND`}</option>
              ))}
            </Select>
          </FormField>
        </div>
      </Modal>

      <ConfirmDialog
        isOpen={deleteId !== null}
        title="Supprimer le plan"
        message="Cette action supprime le plan d'abonnement. Elle peut échouer si le plan est déjà lié à des abonnements."
        confirmLabel="Supprimer"
        onConfirm={deletePlan}
        onClose={() => setDeleteId(null)}
        danger
      />
    </div>
  );
}
