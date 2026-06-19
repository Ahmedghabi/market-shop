import { useEffect, useState, type FormEvent } from 'react';
import { Badge, Button, Card, Input } from '../../components/ui';

type BoutiqueThemeRecord = {
  id: string;
  name: string;
  slug: string;
  status: string;
  primaryColor?: string | null;
  secondaryColor?: string | null;
  domain?: string | null;
  logoUrl?: string | null;
  contactEmail?: string | null;
  contactPhone?: string | null;
  address?: string | null;
  socialLinks?: Record<string, string>;
  metaPixelId?: string | null;
};

type DisplaySettings = {
  pages: Record<string, boolean>;
  categories: Record<string, boolean>;
  products: {
    showPrices: boolean;
    showStock: boolean;
    showReviews: boolean;
    showBadges: boolean;
  };
  layout: 'editorial' | 'grid' | 'compact';
};

const defaultDisplaySettings: DisplaySettings = {
  pages: { home: true, products: true, offers: true, reviews: true, quote: false, loyalty: true },
  categories: { mode: true, accessoires: true, maison: true, beaute: false, alimentaire: true, hightech: false },
  products: { showPrices: true, showStock: true, showReviews: true, showBadges: true },
  layout: 'editorial',
};

const pages = [
  { key: 'home', label: 'Accueil' },
  { key: 'products', label: 'Produits' },
  { key: 'offers', label: 'Offres' },
  { key: 'reviews', label: 'Avis' },
  { key: 'quote', label: 'Devis' },
  { key: 'loyalty', label: 'Fidélité' },
];

const categories = [
  { key: 'mode', label: 'Mode' },
  { key: 'accessoires', label: 'Accessoires' },
  { key: 'maison', label: 'Maison' },
  { key: 'beaute', label: 'Beauté' },
  { key: 'alimentaire', label: 'Alimentaire' },
  { key: 'hightech', label: 'High-tech' },
];

export function FrontOfficeCustomizationScreen({
  boutique,
  getAccessToken,
  onNotice,
}: {
  boutique?: BoutiqueThemeRecord;
  getAccessToken: () => string | null;
  onNotice: (notice: string) => void;
}) {
  const [primaryColor, setPrimaryColor] = useState(boutique?.primaryColor ?? '#3525cd');
  const [secondaryColor, setSecondaryColor] = useState(boutique?.secondaryColor ?? '#505f76');
  const [logoUrl, setLogoUrl] = useState(boutique?.logoUrl ?? '');
  const [domain, setDomain] = useState(boutique?.domain ?? '');
  const [contactEmail, setContactEmail] = useState(boutique?.contactEmail ?? '');
  const [contactPhone, setContactPhone] = useState(boutique?.contactPhone ?? '');
  const [address, setAddress] = useState(boutique?.address ?? '');
  const [metaPixelId, setMetaPixelId] = useState(boutique?.metaPixelId ?? '');
  const [chatEnabled, setChatEnabled] = useState(true);
  const [display, setDisplay] = useState<DisplaySettings>(defaultDisplaySettings);
  const [isSaving, setIsSaving] = useState(false);

  const storageKey = boutique ? `hanooty-front-office-${boutique.id}` : 'hanooty-front-office-default';
  const enabledPages = pages.filter((page) => display.pages[page.key]);
  const enabledCategories = categories.filter((category) => display.categories[category.key]);

  useEffect(() => {
    setPrimaryColor(boutique?.primaryColor ?? '#3525cd');
    setSecondaryColor(boutique?.secondaryColor ?? '#505f76');
    setLogoUrl(boutique?.logoUrl ?? '');
    setDomain(boutique?.domain ?? '');
    setContactEmail(boutique?.contactEmail ?? '');
    setContactPhone(boutique?.contactPhone ?? '');
    setAddress(boutique?.address ?? '');
    setMetaPixelId(boutique?.metaPixelId ?? '');
  }, [boutique]);

  useEffect(() => {
    const stored = window.localStorage.getItem(storageKey);
    if (!stored) {
      setDisplay(defaultDisplaySettings);
      setChatEnabled(window.localStorage.getItem(`hanooty_chat_enabled_${boutique?.slug ?? 'default'}`) !== 'false');
      return;
    }

    try {
      setDisplay({ ...defaultDisplaySettings, ...JSON.parse(stored) as DisplaySettings });
      setChatEnabled(window.localStorage.getItem(`hanooty_chat_enabled_${boutique?.slug ?? 'default'}`) !== 'false');
    } catch {
      setDisplay(defaultDisplaySettings);
    }
  }, [storageKey]);

  function updatePage(key: string, enabled: boolean) {
    setDisplay((current) => ({ ...current, pages: { ...current.pages, [key]: enabled } }));
  }

  function updateCategory(key: string, enabled: boolean) {
    setDisplay((current) => ({ ...current, categories: { ...current.categories, [key]: enabled } }));
  }

  function updateProductOption(key: keyof DisplaySettings['products'], enabled: boolean) {
    setDisplay((current) => ({ ...current, products: { ...current.products, [key]: enabled } }));
  }

  async function saveTheme(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    if (!boutique) {
      onNotice('Aucune boutique sélectionnée pour personnaliser le front-office.');
      return;
    }

    const token = getAccessToken();
    if (!token) {
      onNotice('Session expirée. Reconnectez-vous.');
      return;
    }

    setIsSaving(true);
    window.localStorage.setItem(storageKey, JSON.stringify(display));
    window.localStorage.setItem(`hanooty_chat_enabled_${boutique.slug}`, chatEnabled ? 'true' : 'false');

    try {
      const response = await fetch(`/api/boutiques/${boutique.id}`, {
        method: 'PATCH',
        headers: { Authorization: `Bearer ${token}`, 'Content-Type': 'application/json' },
        body: JSON.stringify({
          name: boutique.name,
          slug: boutique.slug,
          status: boutique.status,
          primaryColor,
          secondaryColor,
          logoUrl: logoUrl || null,
          domain: domain || null,
          contactEmail: contactEmail || null,
          contactPhone: contactPhone || null,
          address: address || null,
          socialLinks: boutique.socialLinks ?? {},
          metaPixelId: metaPixelId || null,
        }),
      });

      if (!response.ok) {
        throw new Error(`API ${response.status}: sauvegarde thème impossible.`);
      }

      onNotice('Personnalisation front-office enregistrée.');
    } catch (exception) {
      onNotice(exception instanceof Error ? exception.message : 'Sauvegarde thème impossible.');
    } finally {
      setIsSaving(false);
    }
  }

  if (!boutique) {
    return (
      <Card>
        <h2 className="text-xl font-bold">Personnalisation indisponible</h2>
        <p className="mt-2 text-sm text-[color:var(--ds-on-surface-variant)]">Aucune boutique n’est associée à ce compte.</p>
      </Card>
    );
  }

  return (
    <form className="space-y-6" onSubmit={saveTheme}>
      <Card className="ds-hero">
        <div className="flex flex-wrap items-start justify-between gap-4">
          <div>
            <p className="ds-hero__eyebrow">Front-office</p>
            <h1 className="ds-hero__title">Personnaliser {boutique.name}</h1>
            <p className="ds-hero__subtitle">Thème général, catégories visibles, affichage produit et aperçu boutique.</p>
          </div>
          <div className="flex flex-wrap gap-2">
            <Badge tone="success">{enabledPages.length} pages</Badge>
            <Badge tone="neutral">{enabledCategories.length} catégories</Badge>
          </div>
        </div>
      </Card>

      <div className="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
        <div className="space-y-6">
          <Card>
            <h2 className="text-xl font-bold">Thème général</h2>
            <p className="mt-2 text-sm text-[color:var(--ds-on-surface-variant)]">Ces couleurs sont utilisées par la vitrine boutique, les boutons et les accents visuels.</p>
            <div className="mt-5 grid gap-4 sm:grid-cols-2">
              <label className="block">
                <span className="mb-2 block text-sm font-semibold">Couleur principale</span>
                <Input type="color" value={primaryColor} onChange={(event) => setPrimaryColor(event.target.value)} className="h-12" />
              </label>
              <label className="block">
                <span className="mb-2 block text-sm font-semibold">Couleur secondaire</span>
                <Input type="color" value={secondaryColor} onChange={(event) => setSecondaryColor(event.target.value)} className="h-12" />
              </label>
              <label className="block sm:col-span-2">
                <span className="mb-2 block text-sm font-semibold">Logo URL</span>
                <Input value={logoUrl} onChange={(event) => setLogoUrl(event.target.value)} placeholder="https://.../logo.png" />
              </label>
              <label className="block sm:col-span-2">
                <span className="mb-2 block text-sm font-semibold">Domaine boutique</span>
                <Input value={domain} onChange={(event) => setDomain(event.target.value)} placeholder="boutique.example.com" />
              </label>
              <label className="block sm:col-span-2">
                <span className="mb-2 block text-sm font-semibold">Meta Pixel boutique</span>
                <Input value={metaPixelId} onChange={(event) => setMetaPixelId(event.target.value)} placeholder="ID Pixel propre à cette boutique" />
                <span className="mt-1 block text-xs text-[color:var(--ds-on-surface-variant)]">Le Pixel applicatif global reste géré uniquement par le super admin.</span>
              </label>
            </div>
          </Card>

          <Card>
            <h2 className="text-xl font-bold">Contacts boutique</h2>
            <p className="mt-2 text-sm text-[color:var(--ds-on-surface-variant)]">Ces informations sont affichées sur la vitrine et utilisées par les clients pour contacter la boutique.</p>
            <div className="mt-5 grid gap-4">
              <Input type="email" value={contactEmail} onChange={(event) => setContactEmail(event.target.value)} placeholder="Email de contact" />
              <Input value={contactPhone} onChange={(event) => setContactPhone(event.target.value)} placeholder="Téléphone" />
              <Input value={address} onChange={(event) => setAddress(event.target.value)} placeholder="Adresse" />
            </div>
          </Card>

          <Card>
            <div className="flex items-center justify-between gap-4">
              <div>
                <h2 className="text-xl font-bold">Chat boutique</h2>
                <p className="mt-2 text-sm text-[color:var(--ds-on-surface-variant)]">Activer ou désactiver la chatbox sur cette boutique.</p>
              </div>
              <label className="flex items-center gap-3 text-sm font-semibold">
                <input type="checkbox" checked={chatEnabled} onChange={(event) => setChatEnabled(event.target.checked)} className="h-5 w-5 accent-[color:var(--ds-primary)]" />
                {chatEnabled ? 'Activé' : 'Désactivé'}
              </label>
            </div>
          </Card>

          <Card>
            <h2 className="text-xl font-bold">Pages affichées</h2>
            <div className="mt-4 grid gap-3 sm:grid-cols-2">
              {pages.map((page) => (
                <label key={page.key} className="flex items-center justify-between rounded-2xl border border-[color:var(--ds-outline-variant)] bg-white p-4">
                  <span className="font-medium">{page.label}</span>
                  <input type="checkbox" checked={display.pages[page.key]} onChange={(event) => updatePage(page.key, event.target.checked)} className="h-5 w-5 accent-[color:var(--ds-primary)]" />
                </label>
              ))}
            </div>
          </Card>

          <Card>
            <h2 className="text-xl font-bold">Catégories visibles</h2>
            <p className="mt-2 text-sm text-[color:var(--ds-on-surface-variant)]">Choisissez les catégories mises en avant sur la vitrine.</p>
            <div className="mt-4 flex flex-wrap gap-3">
              {categories.map((category) => (
                <button
                  key={category.key}
                  type="button"
                  onClick={() => updateCategory(category.key, !display.categories[category.key])}
                  className={`rounded-full border px-4 py-2 text-sm font-semibold ${display.categories[category.key] ? 'border-transparent text-white' : 'border-[color:var(--ds-outline-variant)] bg-white text-[color:var(--ds-on-surface-variant)]'}`}
                  style={display.categories[category.key] ? { backgroundColor: primaryColor } : undefined}
                >
                  {category.label}
                </button>
              ))}
            </div>
          </Card>
        </div>

        <div className="space-y-6">
          <Card>
            <h2 className="text-xl font-bold">Affichage produits</h2>
            <div className="mt-4 space-y-3">
              {[
                ['showPrices', 'Afficher les prix'],
                ['showStock', 'Afficher le stock'],
                ['showReviews', 'Afficher les avis'],
                ['showBadges', 'Afficher les badges produit'],
              ].map(([key, label]) => (
                <label key={key} className="flex items-center justify-between rounded-2xl border border-[color:var(--ds-outline-variant)] bg-white p-4">
                  <span className="font-medium">{label}</span>
                  <input type="checkbox" checked={display.products[key as keyof DisplaySettings['products']]} onChange={(event) => updateProductOption(key as keyof DisplaySettings['products'], event.target.checked)} className="h-5 w-5 accent-[color:var(--ds-primary)]" />
                </label>
              ))}
            </div>
          </Card>

          <Card>
            <h2 className="text-xl font-bold">Layout boutique</h2>
            <div className="mt-4 grid gap-3">
              {[
                ['editorial', 'Éditorial premium'],
                ['grid', 'Grille catalogue'],
                ['compact', 'Compact conversion'],
              ].map(([value, label]) => (
                <label key={value} className="flex items-center gap-3 rounded-2xl border border-[color:var(--ds-outline-variant)] bg-white p-4">
                  <input type="radio" name="layout" checked={display.layout === value} onChange={() => setDisplay((current) => ({ ...current, layout: value as DisplaySettings['layout'] }))} className="h-5 w-5 accent-[color:var(--ds-primary)]" />
                  <span className="font-medium">{label}</span>
                </label>
              ))}
            </div>
          </Card>

          <Card>
            <h2 className="text-xl font-bold">Aperçu vitrine</h2>
            <div className="mt-4 overflow-hidden rounded-3xl border border-[color:var(--ds-outline-variant)] bg-white">
              <div className="p-5 text-white" style={{ background: `linear-gradient(135deg, ${primaryColor}, ${secondaryColor})` }}>
                <div className="flex items-center justify-between gap-3">
                  <strong>{boutique.name}</strong>
                  <span className="rounded-full bg-white/20 px-3 py-1 text-xs">{display.layout}</span>
                </div>
                <p className="mt-4 max-w-sm text-sm text-white/85">Vitrine personnalisée avec navigation, catégories et produits selon vos réglages.</p>
              </div>
              <div className="space-y-4 p-5">
                <div className="flex flex-wrap gap-2">
                  {enabledPages.map((page) => <Badge key={page.key} tone="neutral">{page.label}</Badge>)}
                </div>
                <div className="grid gap-3 sm:grid-cols-2">
                  {enabledCategories.slice(0, 4).map((category) => (
                    <div key={category.key} className="rounded-2xl bg-[color:var(--ds-surface-container-low)] p-4">
                      <strong>{category.label}</strong>
                      <p className="mt-1 text-xs text-[color:var(--ds-on-surface-variant)]">Catégorie affichée</p>
                    </div>
                  ))}
                </div>
                <div className="rounded-2xl bg-[color:var(--ds-surface-container-low)] p-4 text-sm">
                  <strong>Contact</strong>
                  <p className="mt-1 text-[color:var(--ds-on-surface-variant)]">{contactEmail || 'Email non renseigné'} · {contactPhone || 'Téléphone non renseigné'}</p>
                  <p className="mt-1 text-[color:var(--ds-on-surface-variant)]">Chat: {chatEnabled ? 'visible' : 'masqué'}</p>
                </div>
                <div className="rounded-2xl border border-[color:var(--ds-outline-variant)] p-4">
                  <div className="flex items-start justify-between gap-3">
                    <div>
                      <strong>Produit exemple</strong>
                      {display.products.showReviews && <p className="mt-1 text-xs text-[color:var(--ds-on-surface-variant)]">Avis visibles</p>}
                    </div>
                    {display.products.showBadges && <Badge tone="success">Nouveau</Badge>}
                  </div>
                  <div className="mt-3 flex justify-between text-sm">
                    {display.products.showPrices && <span>129,00 EUR</span>}
                    {display.products.showStock && <span>Stock disponible</span>}
                  </div>
                </div>
              </div>
            </div>
          </Card>

          <Button type="submit" variant="primary" className="w-full" disabled={isSaving}>
            {isSaving ? 'Publication...' : 'Publier la personnalisation'}
          </Button>
        </div>
      </div>
    </form>
  );
}
