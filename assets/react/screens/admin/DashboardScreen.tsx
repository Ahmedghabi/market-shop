import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { appIcons } from '../../icons/fontAwesome';
import { Badge, Button, Card } from '../../components/ui';

type DashboardScreenProps = {
  boutiqueName?: string;
  boutiqueStatus?: string;
  productsCount?: number;
  usersCount?: number;
};

export function DashboardScreen({ boutiqueName, boutiqueStatus, productsCount = 0, usersCount = 0 }: DashboardScreenProps) {
  return (
    <section className="space-y-6">
      <Card className="ds-hero">
        <div className="grid gap-6 lg:grid-cols-[1fr_auto] lg:items-center">
          <div>
            <p className="ds-hero__eyebrow">Admin Console</p>
            <h1 className="ds-hero__title">Dashboard Admin</h1>
            <p className="ds-hero__subtitle">Vue d’ensemble boutique, commandes, produits et performance.</p>
          </div>
          <div className="flex flex-wrap items-center gap-3">
            <Badge tone="success">{boutiqueStatus ?? 'Temps réel'}</Badge>
            <Button variant="secondary" onClick={() => { window.location.href = '/admin/settings'; }}><FontAwesomeIcon icon={appIcons.store} /> {boutiqueName ?? 'Boutique'}</Button>
          </div>
        </div>
      </Card>

      <div className="ds-grid ds-grid--cards">
        <Card>
          <p className="text-sm text-[color:var(--ds-on-surface-variant)]">Boutique</p>
          <h2 className="mt-2 text-3xl font-bold">{boutiqueName ?? 'Aucune'}</h2>
          <p className="mt-2 text-sm text-[color:var(--ds-on-surface-variant)]">Statut: {boutiqueStatus ?? '-'}</p>
        </Card>
        <Card>
          <p className="text-sm text-[color:var(--ds-on-surface-variant)]">Produits</p>
          <h2 className="mt-2 text-3xl font-bold">{productsCount}</h2>
          <p className="mt-2 text-sm text-[color:var(--ds-on-surface-variant)]">Synchronisés via l’API</p>
        </Card>
        <Card>
          <p className="text-sm text-[color:var(--ds-on-surface-variant)]">Utilisateurs</p>
          <h2 className="mt-2 text-3xl font-bold">{usersCount}</h2>
          <p className="mt-2 text-sm text-[color:var(--ds-on-surface-variant)]">Accès back-office</p>
        </Card>
        <Card>
          <p className="text-sm text-[color:var(--ds-on-surface-variant)]">Commandes</p>
          <h2 className="mt-2 text-3xl font-bold">API</h2>
          <p className="mt-2 text-sm text-[color:var(--ds-on-surface-variant)]">Commandes, expédition et livraison</p>
        </Card>
      </div>

      <div className="ds-grid ds-grid--split">
        <Card>
          <div className="mb-4 flex items-center justify-between">
            <h3 className="text-xl font-bold">Évolution des ventes</h3>
            <select className="ds-select w-auto">
              <option>7 derniers jours</option>
              <option>30 derniers jours</option>
            </select>
          </div>
          <div className="grid h-72 place-items-center rounded-2xl bg-[linear-gradient(180deg,var(--ds-surface-container-low),white)]">
            <div className="h-40 w-full max-w-3xl rounded-2xl border border-dashed border-[color:var(--ds-outline-variant)] bg-white/70 p-6">
              <div className="flex h-full items-end gap-3">
                {[42, 58, 52, 70, 62, 78, 90].map((value, index) => (
                  <div key={index} className="flex flex-1 flex-col items-center gap-2">
                    <div className="w-full rounded-t-2xl bg-[color:var(--ds-primary-container)]" style={{ height: `${value}%` }} />
                    <span className="text-xs text-[color:var(--ds-on-surface-variant)]">{['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'][index]}</span>
                  </div>
                ))}
              </div>
            </div>
          </div>
        </Card>

        <Card>
          <h3 className="text-xl font-bold">Commandes récentes</h3>
          <div className="mt-4 overflow-x-auto">
            <table className="ds-table">
              <thead>
                <tr>
                  <th>N°</th>
                  <th>Client</th>
                  <th>Montant</th>
                  <th>Statut</th>
                </tr>
              </thead>
              <tbody>
                <tr><td>#ORD-7829</td><td>Jean Dupont</td><td>245,90 €</td><td><Badge tone="success">Expédié</Badge></td></tr>
                <tr><td>#ORD-7828</td><td>Marie Leclerc</td><td>1 120,00 €</td><td><Badge tone="warning">En attente</Badge></td></tr>
                <tr><td>#ORD-7827</td><td>Robert Bernard</td><td>89,00 €</td><td><Badge tone="error">Annulé</Badge></td></tr>
              </tbody>
            </table>
          </div>
        </Card>
      </div>
    </section>
  );
}
