import { useState } from 'react';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { appIcons } from '../../icons/fontAwesome';
import { Badge, Button, Card, Input, Select } from '../../components/ui';

export function SettingsScreen() {
  return (
    <section className="space-y-6">
      <Card className="ds-hero">
        <p className="ds-hero__eyebrow">Paramètres</p>
        <h1 className="ds-hero__title">Paramètres boutique</h1>
        <p className="ds-hero__subtitle">Identité, logo, livraison, branding et réseaux sociaux.</p>
      </Card>

      <div className="ds-grid ds-grid--split">
        <div className="space-y-6">
          <Card>
            <h2 className="text-xl font-bold">Identité</h2>
            <div className="mt-4 grid gap-4">
              <Input placeholder="Nom de la boutique" defaultValue="Luxe Paris" />
              <Input placeholder="Domaine" defaultValue="luxe-paris.maboutique.fr" />
              <Input placeholder="Email de contact" defaultValue="contact@luxe-paris.fr" />
              <Input placeholder="Téléphone" defaultValue="+33 1 23 45 67 89" />
              <Input placeholder="Adresse" defaultValue="12 Rue de la Paix, 75002 Paris" />
            </div>
          </Card>

          <Card>
            <h2 className="text-xl font-bold">Branding</h2>
            <div className="mt-4 grid gap-4">
              <div className="flex items-center gap-4">
                <div className="flex h-16 w-16 items-center justify-center rounded-2xl bg-[color:var(--ds-secondary-container)]">
                  <FontAwesomeIcon icon={appIcons.image} className="text-2xl text-[color:var(--ds-primary)]" />
                </div>
                <Button variant="secondary" onClick={() => { window.alert('Sélection de logo à connecter au service upload.'); }}>Changer le logo</Button>
              </div>
              <div className="flex gap-3">
                {['#3525cd', '#4f46e5', '#505f76', '#fcf8ff', '#1b1b24'].map((color) => (
                  <div key={color} className="h-10 w-10 rounded-xl border-2 border-[color:var(--ds-outline-variant)]" style={{ backgroundColor: color }} />
                ))}
              </div>
            </div>
          </Card>

          <Card>
            <h2 className="text-xl font-bold">Réseaux sociaux</h2>
            <div className="mt-4 grid gap-4">
              <Input placeholder="Instagram" defaultValue="@luxe_paris" />
              <Input placeholder="Facebook" defaultValue="luxeparis" />
            </div>
          </Card>
        </div>

        <div className="space-y-6">
          <Card>
            <div className="flex items-center justify-between">
              <h2 className="text-xl font-bold">Livraison</h2>
              <Badge tone="success">API activée</Badge>
            </div>
            <p className="mt-2 text-sm text-[color:var(--ds-on-surface-variant)]">Configurer les comptes transporteurs et les endpoints API.</p>
            <div className="mt-5 space-y-4">
              <div className="rounded-2xl border border-[color:var(--ds-outline-variant)] bg-white p-4">
                <div className="flex items-center justify-between">
                  <div className="flex items-center gap-3">
                    <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-[color:var(--ds-secondary-container)]">
                      <FontAwesomeIcon icon={appIcons.truck} className="text-[color:var(--ds-primary)]" />
                    </div>
                    <div>
                      <strong>Chronopost</strong>
                      <p className="text-sm text-[color:var(--ds-on-surface-variant)]">Compte vérifié</p>
                    </div>
                  </div>
                  <div className="flex items-center gap-2">
                    <Badge tone="success">Actif</Badge>
                    <Button variant="ghost" onClick={() => { window.location.href = '/admin/delivery-accounts'; }}>
                      <FontAwesomeIcon icon={appIcons.edit} />
                    </Button>
                  </div>
                </div>
              </div>
              <div className="rounded-2xl border border-[color:var(--ds-outline-variant)] bg-white p-4">
                <div className="flex items-center justify-between">
                  <div className="flex items-center gap-3">
                    <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-[color:var(--ds-secondary-container)]">
                      <FontAwesomeIcon icon={appIcons.truck} className="text-[color:var(--ds-primary)]" />
                    </div>
                    <div>
                      <strong>DHL</strong>
                      <p className="text-sm text-[color:var(--ds-on-surface-variant)]">En attente de vérification</p>
                    </div>
                  </div>
                  <div className="flex items-center gap-2">
                    <Badge tone="warning">Non vérifié</Badge>
                    <Button variant="ghost" onClick={() => { window.location.href = '/admin/delivery-accounts'; }}>
                      <FontAwesomeIcon icon={appIcons.trash} />
                    </Button>
                  </div>
                </div>
              </div>
              <Select>
                <option>Ajouter un transporteur...</option>
                <option>Chronopost</option>
                <option>DHL</option>
                <option>Colissimo</option>
                <option>UPS</option>
              </Select>
            </div>
            <Button variant="primary" className="mt-6 w-full" onClick={() => { window.alert('Paramètres boutique enregistrés localement.'); }}>Enregistrer</Button>
          </Card>

          <Card>
            <h2 className="text-xl font-bold">Notifications</h2>
            <div className="mt-4 space-y-3">
              <label className="flex items-center gap-3 rounded-2xl border border-[color:var(--ds-outline-variant)] bg-white p-4">
                <input type="checkbox" className="h-5 w-5 accent-[color:var(--ds-primary)]" defaultChecked />
                <span className="text-sm">Notification de commande par email</span>
              </label>
              <label className="flex items-center gap-3 rounded-2xl border border-[color:var(--ds-outline-variant)] bg-white p-4">
                <input type="checkbox" className="h-5 w-5 accent-[color:var(--ds-primary)]" defaultChecked />
                <span className="text-sm">Alerte stock bas</span>
              </label>
              <label className="flex items-center gap-3 rounded-2xl border border-[color:var(--ds-outline-variant)] bg-white p-4">
                <input type="checkbox" className="h-5 w-5 accent-[color:var(--ds-primary)]" />
                <span className="text-sm">Rapport hebdomadaire</span>
              </label>
            </div>
          </Card>

          <Card>
            <div className="flex items-center justify-between">
              <h2 className="text-xl font-bold">Meta Pixel - Boutique</h2>
              <Badge tone="neutral">Par boutique</Badge>
            </div>
            <p className="mt-2 text-sm text-[color:var(--ds-on-surface-variant)]">
              Chaque boutique peut avoir son propre ID Pixel Facebook pour le suivi des conversions par boutique.
            </p>
            <div className="mt-5 space-y-4">
              <div className="rounded-2xl border border-[color:var(--ds-outline-variant)] bg-white p-4">
                <div className="flex items-center justify-between">
                  <div className="flex items-center gap-3">
                    <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-[color:var(--ds-secondary-container)]">
                      <FontAwesomeIcon icon={appIcons.facebook} className="text-[color:var(--ds-primary)]" />
                    </div>
                    <div>
                      <strong>Luxe Paris</strong>
                      <p className="text-sm text-[color:var(--ds-on-surface-variant)]">ID : 1234567890123456</p>
                    </div>
                  </div>
                  <div className="flex items-center gap-2">
                    <Badge tone="warning">Boutique 1</Badge>
                    <Badge tone="success">Actif</Badge>
                    <Button variant="ghost" onClick={() => { window.location.href = '/admin/theme'; }}>
                      <FontAwesomeIcon icon={appIcons.edit} />
                    </Button>
                  </div>
                </div>
              </div>
              <div className="rounded-2xl border border-[color:var(--ds-outline-variant)] bg-white p-4">
                <div className="flex items-center justify-between">
                  <div className="flex items-center gap-3">
                    <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-[color:var(--ds-secondary-container)]">
                      <FontAwesomeIcon icon={appIcons.facebook} className="text-[color:var(--ds-primary)]" />
                    </div>
                    <div>
                      <strong>Boutique Moderne</strong>
                      <p className="text-sm text-[color:var(--ds-on-surface-variant)]">ID : 9876543210987654</p>
                    </div>
                  </div>
                  <div className="flex items-center gap-2">
                    <Badge tone="warning">Boutique 2</Badge>
                    <Badge tone="success">Actif</Badge>
                    <Button variant="ghost" onClick={() => { window.location.href = '/admin/theme'; }}>
                      <FontAwesomeIcon icon={appIcons.edit} />
                    </Button>
                  </div>
                </div>
              </div>
              <Select>
                <option>Ajouter une boutique...</option>
                <option>Luxe Paris</option>
                <option>Boutique Moderne</option>
              </Select>
            </div>
          </Card>
        </div>
      </div>
    </section>
  );
}
