import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { useEffect, useState, type CSSProperties, type FormEvent } from 'react';
import { BrowserRouter, Navigate, Route, Routes, useLocation } from 'react-router-dom';
import { LoginPage } from './auth/LoginPage';
import { useAuth } from './auth/useAuth';
import { appIcons } from './icons/fontAwesome';
import { AdminChatPanel } from './admin/chat/AdminChatPanel';
import { Badge, Button, Card, Input, Select } from './components/ui';
import { BoutiqueManagementDashboardScreen } from './screens/admin/BoutiqueManagementDashboardScreen';
import { ChatbotConfigScreen } from './screens/admin/ChatbotConfigScreen';
import { FrontOfficeCustomizationScreen } from './screens/admin/FrontOfficeCustomizationScreen';
import { DashboardScreen } from './screens/admin/DashboardScreen';
import { MarketingPromotionsScreen } from './screens/admin/MarketingPromotionsScreen';
import { OrderDetailScreen } from './screens/admin/OrderDetailScreen';
import { OrdersScreen } from './screens/admin/OrdersScreen';
import { ProductInventoryScreen } from './screens/admin/ProductInventoryScreen';
import { SettingsScreen } from './screens/admin/SettingsScreen';
import { SubscriptionsScreen } from './screens/admin/SubscriptionsScreen';
import { DeliveryCompaniesScreen } from './screens/admin/DeliveryCompaniesScreen';
import { DeliveryAccountsScreen } from './screens/admin/DeliveryAccountsScreen';
import { SuperAdminDashboardScreen } from './screens/admin/SuperAdminDashboardScreen';
import { BackOfficeApp } from './backoffice/BackOfficeApp';
import { ApplicationReviewsPage } from './pages/application-reviews';
import { BoutiqueCentralRoutePage } from './pages/boutique-central';
import { ActiveBoutiquesPage } from './pages/boutiques';
import { CartRoutePage } from './pages/cart';
import { ChatbotPreviewRoutePage } from './pages/chatbot-preview';
import { CheckoutRoutePage } from './pages/checkout';
import { HomePage } from './pages/home';
import { MarketplaceRoutePage } from './pages/marketplace';
import { OrderConfirmationRoutePage } from './pages/order-confirmation';
import { ProductDetailRoutePage } from './pages/product-detail';
import { QuoteWizardRoutePage } from './pages/quote-wizard';
import { ShopHomeRoutePage } from './pages/shop-home';
import { ShopCatalogueRoutePage } from './pages/shop-catalogue';
import { ShopAboutRoutePage } from './pages/shop-about';
import { StorefrontRoutePage } from './pages/storefront';
import { isBoutiqueSubdomain, resolveBoutiqueSlug } from './screens/public/boutiqueRouting';
import { useBoutiqueTheme } from './theme/useBoutiqueTheme';

type AppIcon = keyof typeof appIcons;

type RouteConfig = {
  slug: string;
  title: string;
  path: string;
  section: string;
  description: string;
  icon: AppIcon;
  access: 'public' | 'admin';
};

type SubscriptionRecord = {
  id: string;
  boutiqueId: string;
  boutiqueName: string | null;
  plan: string;
  status: string;
  startDate: string | null;
  endDate: string | null;
  acceptedBy: string | null;
  acceptedAt: string | null;
  createdAt: string;
  priceCents: number;
};

type UserRecord = {
  id: string;
  email: string;
  displayName: string | null;
  roles: string[];
  boutiqueId: string | null;
  boutiqueName: string | null;
};

type BoutiqueRecord = {
  id: string;
  name: string;
  slug: string;
  status: string;
  contactEmail?: string | null;
  productsCount?: number;
  usersCount?: number;
};

type NotificationRecord = {
  id: string;
  type: string;
  title: string;
  message: string;
  boutiqueId?: string | null;
  read: boolean;
  createdAt: string;
};

type ChatMessage = {
  author: 'customer' | 'bot';
  message: string;
};

type DesignToken = {
  label: string;
  color: string;
};

type PublicBoutique = {
  name: string;
  category: string;
  city: string;
  image: string;
  href: string;
  accent: string;
  slug: string;
};

type OrderRecord = {
  id: string;
  channel: string;
  status: string;
  totalCents: number;
  currency: string;
  customerName?: string;
  createdAt: string;
  itemsCount?: number;
};

type CustomerRecord = {
  id: string;
  email: string;
  firstName?: string;
  lastName?: string;
  phone?: string;
  ordersCount?: number;
  totalSpentCents?: number;
  createdAt: string;
};

type ProductRecord = {
  id: string;
  name: string;
  sku?: string;
  priceCents: number;
  currency: string;
  status: string;
  categoryName?: string;
  stock?: number;
  imageUrl?: string;
  createdAt: string;
};

type CategoryRecord = {
  id: string;
  name: string;
  slug: string;
  parentId?: string | null;
  productsCount?: number;
  createdAt: string;
};

type PromotionRecord = {
  id: string;
  name: string;
  type: string;
  value: number;
  status: string;
  startDate?: string;
  endDate?: string;
  createdAt: string;
};

type StockMovementRecord = {
  id: string;
  productName: string;
  type: string;
  quantity: number;
  reason: string;
  createdAt: string;
};

type LoyaltyRecord = {
  id: string;
  customerEmail: string;
  points: number;
  balance: number;
  createdAt: string;
};

type SponsorRecord = {
  id: string;
  name: string;
  scope: string;
  active: boolean;
  logoUrl?: string;
  targetUrl?: string;
  createdAt: string;
};

function useApiData<T>(url: string | null): { data: T | null; isLoaded: boolean } {
  const [data, setData] = useState<T | null>(null);
  const [isLoaded, setIsLoaded] = useState(false);

  useEffect(() => {
    if (!url) {
      setIsLoaded(true);
      return;
    }

    let isMounted = true;

    fetch(url)
      .then((response) => response.ok ? response.json() as Promise<T> : Promise.reject(new Error(`Unable to load ${url}.`)))
      .then((payload) => {
        if (isMounted) {
          setData(payload);
          setIsLoaded(true);
        }
      })
      .catch(() => {
        if (isMounted) {
          setIsLoaded(true);
        }
      });

    return () => {
      isMounted = false;
    };
  }, [url]);

  return { data, isLoaded };
}

function resolveRoute(pathname: string, publicRoutes: RouteConfig[], adminRoutes: RouteConfig[]): RouteConfig | null {
  if (publicRoutes.length === 0 && adminRoutes.length === 0) {
    return null;
  }

  if (pathname === '/' || pathname === '') {
    return publicRoutes[0] ?? adminRoutes[0] ?? null;
  }

  const publicRoute = publicRoutes.find((route) => route.path === pathname);
  if (publicRoute) {
    return publicRoute;
  }

  if (pathname === '/admin') {
    return adminRoutes[0] ?? null;
  }

  if (pathname.startsWith('/admin/')) {
    return adminRoutes.find((route) => route.path === pathname) ?? adminRoutes[0] ?? null;
  }

  return publicRoutes[0] ?? null;
}

export function App() {
  return (
    <BrowserRouter>
      <AppRoutes />
    </BrowserRouter>
  );
}

function AppRoutes() {
  const { user, isLoading, signIn, signUp, signOut, getAccessToken } = useAuth();
  const { theme } = useBoutiqueTheme();
  const location = useLocation();
  const { data: routesData } = useApiData<{ publicRoutes: RouteConfig[]; adminRoutes: RouteConfig[] }>('/api/routes');
  const publicRoutes = routesData?.publicRoutes ?? [];
  const adminRoutes = routesData?.adminRoutes ?? [];
  const userRoles = user?.profile.roles ?? [];
  const canAccessBackOffice = userRoles.some((role) => ['ROLE_SUPER_ADMIN', 'ROLE_BOUTIQUE_ADMIN', 'ROLE_CAISSIER'].includes(role));
  const subdomainSlug = isBoutiqueSubdomain();
  const route = resolveRoute(location.pathname, publicRoutes, adminRoutes);

  if (!routesData || !route) {
    return <main className="auth-shell">Chargement...</main>;
  }

  const renderAdminRoute = (adminRoute: RouteConfig) => {
    if (isLoading) {
      return <main className="auth-shell">Chargement de la session...</main>;
    }

    if (!user) {
      return <LoginPage onSignIn={signIn} onSignUp={signUp} />;
    }

    const allowedRoles = ['ROLE_SUPER_ADMIN', 'ROLE_BOUTIQUE_ADMIN', 'ROLE_CAISSIER'];
    const hasBackOfficeAccess = user.profile.roles.some((role) => allowedRoles.includes(role));

    if (!hasBackOfficeAccess) {
      return <RoleDeniedPage onSignOut={signOut} userEmail={user.profile.email ?? user.profile.sub} />;
    }

    return (
      <BackOfficeApp
        userEmail={user.profile.email ?? user.profile.sub}
        userRoles={user.profile.roles}
        userBoutiques={user.profile.boutiques ?? []}
        getAccessToken={getAccessToken}
        onSignOut={signOut}
      />
    );
  };

  return (
    <Routes>
      <Route path="/login" element={<Navigate to="/auth/login" replace />} />
      <Route path="/register" element={<Navigate to="/auth/register" replace />} />
      <Route path="/auth/login" element={<LoginPage onSignIn={signIn} onSignUp={signUp} initialMode="login" />} />
      <Route path="/auth/register" element={<LoginPage onSignIn={signIn} onSignUp={signUp} initialMode="register" />} />
      <Route path="/avis" element={<ApplicationReviewsPage />} />
      <Route path="/boutiques/:boutiqueSlug/cart" element={<CartRoutePage />} />
      {subdomainSlug ? (
        <>
          <Route path="/products/:productSlug" element={<ProductDetailRoutePage title="Produit" />} />
          <Route path="/cart" element={<CartRoutePage />} />
        </>
      ) : null}
      {publicRoutes.map((publicRoute) => (
        <Route key={`public-${publicRoute.slug}-${publicRoute.path}`} path={toReactRouterPath(publicRoute)} element={<PublicPage route={publicRoute} canAccessBackOffice={canAccessBackOffice} />} />
      ))}
      {adminRoutes.map((adminRoute) => (
        <Route key={`admin-${adminRoute.slug}-${adminRoute.path}`} path={adminRoute.path} element={renderAdminRoute(adminRoute)} />
      ))}
      <Route path="/admin/*" element={<Navigate to="/admin" replace />} />
      <Route path="*" element={<Navigate to="/" replace />} />
    </Routes>
  );
}

function toReactRouterPath(route: RouteConfig): string {
  if (route.slug === 'boutique-storefront') {
    return '/boutiques/:boutiqueSlug';
  }

  if (route.slug === 'product-detail') {
    return '/boutiques/:boutiqueSlug/products/:productSlug';
  }

  return route.path;
}

function PublicPage({ route, canAccessBackOffice }: { route: RouteConfig; canAccessBackOffice: boolean }) {
  const location = useLocation();
  const needsBoutiques = ['boutiques', 'boutique-storefront', 'product-detail'].includes(route.slug);
  const { data: boutiquesData } = useApiData<{ member?: PublicBoutique[]; items?: PublicBoutique[] }>(needsBoutiques ? '/api/boutiques' : null);
  const boutiques = boutiquesData?.member ?? boutiquesData?.items ?? [];

  const boutiqueSlug = location.pathname.match(/^\/boutiques\/([^/]+)/)?.[1] ?? '';
  const currentBoutique = boutiques.find((b) => b.slug === boutiqueSlug);

  if (route.slug === 'home') {
    const subdomainSlug = resolveBoutiqueSlug(/^\/boutiques\/([^/]+)/);
    if (subdomainSlug) {
      return <StorefrontRoutePage title="Boutique" description="Découvrez nos produits" />;
    }

    return <HomePage canAccessBackOffice={canAccessBackOffice} />;
  }

  if (route.slug === 'boutiques') {
    return <ActiveBoutiquesPage boutiques={boutiques} />;
  }

  if (route.slug === 'admin-home') {
    return <BoutiqueCentralRoutePage title={route.title} description={route.description} />;
  }

  if (route.slug === 'front-chatbot') {
    return <ChatbotPreviewRoutePage title={route.title} description={route.description} />;
  }

  if (route.slug === 'boutique-storefront') {
    return <StorefrontRoutePage title={currentBoutique ? currentBoutique.name : route.title} description={currentBoutique ? `Boutique ${currentBoutique.name}` : route.description} />;
  }

  if (route.slug === 'product-detail') {
    return <ProductDetailRoutePage title={route.title} />;
  }

  if (route.slug === 'cart') {
    return <CartRoutePage />;
  }

  if (route.slug === 'checkout') {
    return <CheckoutRoutePage />;
  }

  if (route.slug === 'quote') {
    return <QuoteWizardRoutePage />;
  }

  if (route.slug === 'confirmation') {
    return <OrderConfirmationRoutePage />;
  }

  if (route.slug === 'shop-home') {
    return <ShopHomeRoutePage />;
  }

  if (route.slug === 'shop-catalogue') {
    return <ShopCatalogueRoutePage />;
  }

  if (route.slug === 'shop-about') {
    return <ShopAboutRoutePage />;
  }

  return <MarketplaceRoutePage title={route.title} description={route.description} boutiques={boutiques} />;
}

function LovableHomePage({ canAccessBackOffice }: { canAccessBackOffice: boolean }) {
  const boutiques = [
    { initial: 'L', name: "L'Atelier Cuir", category: 'Artisanat & Mode', rating: '4.8', reviews: '127 personnes ont noté', status: 'Ouvert', hours: '9h-18h' },
    { initial: 'T', name: 'TechDirect B2B', category: 'High-Tech', rating: '4.6', reviews: '89 personnes ont noté', status: 'Fermé', hours: '8h-17h' },
    { initial: 'G', name: 'Gourmet Select', category: 'Alimentation', rating: '4.9', reviews: '203 personnes ont noté', status: 'Ouvert', hours: '7h-19h' },
    { initial: 'D', name: 'Design & Co', category: 'Mobilier & Déco', rating: '4.7', reviews: '156 personnes ont noté', status: 'Fermé', hours: '10h-18h' },
  ];
  const products = [
    { shop: "L'Atelier Cuir", name: 'Sac en cuir tannage végétal', price: '189,00 €', icon: 'shopping_bag', badge: 'Nouveau' },
    { shop: 'TechDirect B2B', name: 'Casque sans fil Pro X2', price: '249,00 €', icon: 'headphones', badge: 'Nouveau' },
    { shop: 'Gourmet Select', name: "Coffret huile d'olive AOP", price: '62,00 €', icon: 'restaurant', badge: 'Édition limitée' },
    { shop: 'Design & Co', name: 'Lampe sculpturale chêne', price: '320,00 €', icon: 'lightbulb', badge: 'Nouveau' },
  ];
  const reviews = [
    { initial: 'M', name: 'Marie D.', shop: "L'Atelier Cuir", date: 'il y a 2 jours', rating: '5 / 5', text: 'Qualité exceptionnelle du cuir, livraison rapide et emballage soigné. Le sac dépasse mes attentes !', verified: true },
    { initial: 'J', name: 'Jean-Pierre L.', shop: 'TechDirect B2B', date: 'il y a 1 semaine', rating: '4.5 / 5', text: 'Service pro, casque conforme à la description. Prix compétitif pour le B2B, je recommande.', verified: true },
    { initial: 'S', name: 'Sophie B.', shop: 'Gourmet Select', date: 'il y a 3 jours', rating: '5 / 5', text: "L'huile d'olive est d'une finesse incroyable. Mes clients sont conquis. Commande régulière assurée !", verified: true },
    { initial: 'A', name: 'Alexandre M.', shop: 'Design & Co', date: 'il y a 5 jours', rating: '4 / 5', text: 'Belle lampe, design original. Le délai de fabrication était un peu long mais le résultat vaut le coup.', verified: false },
    { initial: 'C', name: 'Claire R.', shop: "L'Atelier Cuir", date: 'il y a 1 semaine', rating: '5 / 5', text: 'Deuxième commande et toujours aussi satisfaite. Le service client est réactif et attentionné.', verified: true },
    { initial: 'T', name: 'Thomas G.', shop: 'TechDirect B2B', date: 'il y a 2 semaines', rating: '4.5 / 5', text: "Excellent rapport qualité-prix pour mes commandes professionnelles. Livraison toujours à l'heure.", verified: true },
  ];
  const steps = [
    { icon: 'person_add', title: 'Créez votre compte', text: 'Inscrivez-vous en quelques clics et complétez votre profil professionnel pour inspirer confiance.' },
    { icon: 'settings_suggest', title: 'Personnalisez votre boutique', text: 'Ajoutez votre logo, vos couleurs et listez vos produits avec descriptions précises et photos HD.' },
    { icon: 'payments', title: 'Commencez à vendre', text: 'Recevez des commandes, gérez vos expéditions et soyez payé directement de manière sécurisée.' },
  ];
  const benefits = [
    { icon: 'trending_up', title: 'Visibilité accrue', text: "Accédez à un réseau de milliers d'acheteurs B2B en France et à l'international." },
    { icon: 'dashboard_customize', title: 'Outils de gestion pro', text: 'Une console admin puissante pour gérer stocks, factures et statistiques en temps réel.' },
    { icon: 'verified_user', title: 'Paiement sécurisé', text: 'Toutes les transactions sont cryptées et protégées par nos protocoles bancaires certifiés.' },
  ];

  return (
    <main className="lovable-home">
      <header className="lovable-header">
        <div className="lovable-container lovable-header__inner">
          <a className="lovable-brand" href="/">
            <span className="material-symbols-outlined" aria-hidden="true">storefront</span>
            <span>Hanooty</span>
          </a>
          <nav className="lovable-nav" aria-label="Navigation principale">
            <a href="#boutiques">Boutiques</a>
            <a href="#nouveautes">Nouveautés</a>
            <a href="#avis">Avis</a>
            <a href="#fonctionnement">Comment ça marche</a>
          </nav>
          <div className="lovable-header__actions">
            <button className="lovable-link-button" type="button" onClick={() => { window.location.href = '/admin'; }}>{canAccessBackOffice ? 'Back office' : 'Connexion'}</button>
            <button className="lovable-button lovable-button--sm" type="button" onClick={() => { window.location.href = '/admin'; }}>S&apos;inscrire</button>
          </div>
        </div>
      </header>

      <section className="lovable-hero">
        <div className="lovable-container lovable-hero__inner">
          <span className="lovable-pill">Solution Professionnelle</span>
          <h1>La Marketplace B2B pour les Indépendants</h1>
          <p>Propulsez votre activité commerciale avec une plateforme dédiée. Hanooty simplifie la gestion de votre boutique, de l&apos;inventaire à la vente sécurisée.</p>
          <div className="lovable-hero__actions">
            <button className="lovable-button" type="button" onClick={() => { window.location.href = '/boutiques'; }}>Explorer les Boutiques <span className="material-symbols-outlined">arrow_forward</span></button>
            <button className="lovable-button lovable-button--secondary" type="button" onClick={() => { window.location.href = '/admin'; }}>Créer ma Boutique</button>
          </div>
          <div className="lovable-stats">
            <div><strong>2,500+</strong><span>Boutiques actives</span></div>
            <div><strong>98%</strong><span>Satisfaction client</span></div>
            <div><strong>15K+</strong><span>Produits référencés</span></div>
          </div>
        </div>
      </section>

      <section className="lovable-section lovable-section--muted" id="boutiques">
        <div className="lovable-container">
          <SectionHeader title="Boutiques à la Une" text="Découvrez les marchands qui font la différence sur Hanooty." align="split" />
          <div className="lovable-boutique-grid">
            {boutiques.map((boutique) => (
              <article className="lovable-card lovable-boutique-card" key={boutique.name}>
                <div className="lovable-avatar lovable-avatar--lg">{boutique.initial}</div>
                <h3>{boutique.name}</h3>
                <p>{boutique.category}</p>
                <div className="lovable-rating"><strong>{boutique.rating}</strong><span>{boutique.reviews}</span></div>
                <div className="lovable-badges"><span className={boutique.status === 'Ouvert' ? 'is-open' : 'is-closed'}>{boutique.status} <small>· {boutique.hours}</small></span><span><span className="material-symbols-outlined">verified</span> Vérifié</span></div>
                <button type="button" onClick={() => { window.location.href = '/boutiques'; }}>Voir la boutique <span className="material-symbols-outlined">arrow_forward</span></button>
              </article>
            ))}
          </div>
        </div>
      </section>

      <section className="lovable-section" id="nouveautes">
        <div className="lovable-container">
          <SectionHeader title="Nouveaux Produits" text="Les dernières nouveautés ajoutées par nos marchands." align="split" />
          <div className="lovable-product-grid">
            {products.map((product) => (
              <article className="lovable-card lovable-product-card" key={product.name}>
                <div className="lovable-product-card__media">
                  <span className="lovable-product-badge">{product.badge}</span>
                  <span className="material-symbols-outlined lovable-product-icon">{product.icon}</span>
                  <button type="button" aria-label="Ajouter au panier"><span className="material-symbols-outlined">add_shopping_cart</span></button>
                </div>
                <div className="lovable-product-card__body">
                  <p>{product.shop}</p>
                  <h3>{product.name}</h3>
                  <strong>{product.price}</strong>
                </div>
              </article>
            ))}
          </div>
        </div>
      </section>

      <section className="lovable-section lovable-section--reviews" id="avis">
        <div className="lovable-container">
          <SectionHeader eyebrow="reviews" eyebrowText="Témoignages" title="Avis Clients" text="Découvrez ce que nos acheteurs professionnels disent de leurs boutiques favorites." />
          <div className="lovable-review-grid">
            {reviews.map((review) => (
              <article className="lovable-card lovable-review-card" key={`${review.name}-${review.shop}`}>
                <div className="lovable-review-card__header">
                  <div className="lovable-review-card__author"><span className="lovable-avatar">{review.initial}</span><div><strong>{review.name}</strong><small>{review.shop}</small></div></div>
                  <time>{review.date}</time>
                </div>
                <strong className="lovable-review-card__rating">{review.rating}</strong>
                <p>{review.text}</p>
                {review.verified && <span className="lovable-verified"><span className="material-symbols-outlined">verified</span> Achat vérifié</span>}
              </article>
            ))}
          </div>
          <button className="lovable-button lovable-button--secondary lovable-centered-button" type="button">Voir tous les avis <span className="material-symbols-outlined">arrow_forward</span></button>
        </div>
      </section>

      <section className="lovable-section" id="fonctionnement">
        <div className="lovable-container">
          <SectionHeader eyebrow="rocket_launch" eyebrowText="Démarrage rapide" title="Comment ça marche" text="Trois étapes simples pour transformer votre commerce." />
          <div className="lovable-step-grid">
            {steps.map((step, index) => (
              <article className="lovable-card lovable-step-card" key={step.title}>
                <span className="lovable-step-card__number">{index + 1}</span>
                <span className="material-symbols-outlined lovable-step-card__icon">{step.icon}</span>
                <h3>{step.title}</h3>
                <p>{step.text}</p>
              </article>
            ))}
          </div>
        </div>
      </section>

      <section className="lovable-section lovable-benefits">
        <div className="lovable-container">
          <SectionHeader eyebrow="stars" eyebrowText="Avantages" title="Pourquoi Hanooty ?" />
          <div className="lovable-benefit-grid">
            {benefits.map((benefit) => (
              <article className="lovable-card lovable-benefit-card" key={benefit.title}>
                <span className="material-symbols-outlined">{benefit.icon}</span>
                <h3>{benefit.title}</h3>
                <p>{benefit.text}</p>
              </article>
            ))}
          </div>
        </div>
      </section>

      <section className="lovable-newsletter">
        <div className="lovable-container">
          <div className="lovable-newsletter__card">
            <span className="material-symbols-outlined">mail</span>
            <h2>Restez informé</h2>
            <p>Recevez nos conseils pour booster votre boutique et les dernières tendances du marché.</p>
            <form>
              <input type="email" placeholder="vous@email.com" aria-label="Adresse email" />
              <button type="submit">S&apos;inscrire</button>
            </form>
          </div>
        </div>
      </section>

      <footer className="lovable-footer">
        <div className="lovable-container">
          <div className="lovable-footer__top">
            <div className="lovable-footer__brand"><a className="lovable-brand" href="/"><span className="material-symbols-outlined">storefront</span><span>Hanooty</span></a><p>La plateforme de référence pour les commerçants indépendants cherchant excellence et efficacité.</p><div className="lovable-socials"><a href="#">facebook</a><a href="#">alternate_email</a><a href="#">linkedin</a></div></div>
            <FooterColumn title="Explorer" links={['Boutiques', 'Vendeurs', 'Catégories']} />
            <FooterColumn title="Société" links={['À propos', 'Contact', 'Blog']} />
            <FooterColumn title="Légal" links={['CGV', 'Confidentialité', 'Cookies']} />
          </div>
          <p className="lovable-footer__bottom">© 2026 Market Shop - Hanooty. Tous droits réservés.</p>
        </div>
      </footer>
    </main>
  );
}

function SectionHeader({ eyebrow, eyebrowText, title, text, align = 'center' }: { eyebrow?: string; eyebrowText?: string; title: string; text?: string; align?: 'center' | 'split' }) {
  return (
    <div className={`lovable-section-header lovable-section-header--${align}`}>
      <div>
        {eyebrow && <span className="lovable-pill"><span className="material-symbols-outlined">{eyebrow}</span>{eyebrowText}</span>}
        <h2>{title}</h2>
        {text && <p>{text}</p>}
      </div>
      {align === 'split' && <a href="/boutiques">Tout voir <span className="material-symbols-outlined">arrow_forward</span></a>}
    </div>
  );
}

function FooterColumn({ title, links }: { title: string; links: string[] }) {
  return (
    <div className="lovable-footer__column">
      <h4>{title}</h4>
      {links.map((link) => <a href="#" key={link}>{link}</a>)}
    </div>
  );
}

function PublicBoutiques({ boutiques }: { boutiques: PublicBoutique[] }) {
  return (
    <section className="public-boutiques" id="boutiques" aria-label="Boutiques disponibles">
      <div className="public-section-heading">
        <p className="auth-eyebrow">Boutiques</p>
        <h2>Toutes les boutiques</h2>
        <p>Chaque boutique garde son thème, ses pages, ses produits et son expérience client.</p>
      </div>
      <div className="public-boutique-grid">
        {boutiques.map((boutique) => (
          <a className="public-boutique-card" href={boutique.href} key={boutique.slug} style={{ '--boutique-accent': boutique.accent } as CSSProperties}>
            <img src={boutique.image} alt={`Aperçu ${boutique.name}`} />
            <div>
              <span>{boutique.category}</span>
              <strong>{boutique.name}</strong>
              <small>{boutique.city}</small>
            </div>
          </a>
        ))}
        {boutiques.length === 0 && <p className="empty-state">Aucune boutique publiée pour le moment.</p>}
      </div>
    </section>
  );
}

function PublicChatbot({ initialMessages }: { initialMessages: ChatMessage[] }) {
  const [messages, setMessages] = useState(initialMessages);
  const [draft, setDraft] = useState('');

  useEffect(() => {
    setMessages(initialMessages);
  }, [initialMessages]);

  function sendMessage() {
    const trimmed = draft.trim();
    if (!trimmed) {
      return;
    }

    setMessages((current) => [
      ...current,
      { author: 'customer', message: trimmed },
    ]);
    setDraft('');
  }

  return (
    <section className="public-boutiques" id="boutiques">
      <div className="public-section-heading">
        <p className="auth-eyebrow">Assistant IA</p>
        <h2>Conversation client dynamique</h2>
        <p>L&apos;assistant répond aux questions produits, disponibilité, livraison et promotions.</p>
      </div>
      <article className="panel chatbot-panel">
        {messages.map((message, index) => (
          <div className={`chat-line ${message.author}`} key={`${message.author}-${index}`}>{message.message}</div>
        ))}
        <div className="chat-input-row">
          <input value={draft} onChange={(event) => setDraft(event.target.value)} onKeyDown={(event) => event.key === 'Enter' && sendMessage()} placeholder="Écrire une question client..." />
          <button type="button" onClick={sendMessage}>Envoyer</button>
        </div>
      </article>
    </section>
  );
}

function RoleDeniedPage({ onSignOut, userEmail }: { onSignOut: () => Promise<void>; userEmail: string }) {
  return (
    <main className="auth-shell">
      <section className="auth-card">
        <div className="brand-mark" aria-hidden="true">
          <FontAwesomeIcon icon={appIcons.security} />
        </div>
        <p className="auth-eyebrow">Hanooty</p>
        <h1>Accès restreint</h1>
        <p>
          Votre rôle ne permet pas d&apos;accéder au back-office.
          Seuls les Super Admins, Admins boutique et Caissiers peuvent y accéder.
        </p>
        <div className="auth-info">
          Connecté en tant que <strong>{userEmail}</strong>
        </div>
        <button type="button" onClick={onSignOut}>
          <FontAwesomeIcon icon={appIcons.logout} /> Se déconnecter
        </button>
      </section>
    </main>
  );
}

function BackOffice({
  route,
  adminRoutes,
  theme,
  userEmail,
  userRoles,
  userBoutiques,
  getAccessToken,
  onSignOut,
}: {
  route: RouteConfig;
  adminRoutes: RouteConfig[];
  theme: ReturnType<typeof useBoutiqueTheme>['theme'];
  userEmail: string;
  userRoles: string[];
  userBoutiques: Array<{ id: string; name: string; slug: string; status: string }>;
  getAccessToken: () => string | null;
  onSignOut: () => Promise<void>;
}) {
  const [search, setSearch] = useState('');
  const [notice, setNotice] = useState('Données chargées depuis API Platform.');
  const [boutiques, setBoutiques] = useState<BoutiqueRecord[]>([]);
  const [notifications, setNotifications] = useState<NotificationRecord[]>([]);
  const isSuperAdmin = userRoles.includes('ROLE_SUPER_ADMIN');
  const defaultBoutique = userBoutiques[0];

  useEffect(() => {
    const token = getAccessToken();
    if (!token) {
      return;
    }

    void Promise.all([
      apiGet<{ member?: BoutiqueRecord[]; items?: BoutiqueRecord[] }>('/api/boutiques', token),
      apiGet<{ member?: NotificationRecord[]; items?: NotificationRecord[] }>('/api/notifications', token),
    ]).then(([boutiquePayload, notificationPayload]) => {
      setBoutiques(boutiquePayload.member ?? boutiquePayload.items ?? []);
      setNotifications(notificationPayload.member ?? notificationPayload.items ?? []);
    }).catch((exception) => {
      setNotice(exception instanceof Error ? exception.message : 'Chargement API impossible.');
    });
  }, [getAccessToken]);

  return (
    <div className="app-shell">
      <SideNav activeSlug={route.slug} adminRoutes={adminRoutes} userEmail={userEmail} defaultBoutique={defaultBoutique} />
      <header className="top-bar">
        <div className="search-box">
          <FontAwesomeIcon icon={appIcons.store} />
          <input value={search} onChange={(event) => setSearch(event.target.value)} placeholder="Rechercher modules, commandes, stock ou clients..." type="search" />
        </div>
        <div className="top-actions">
          <a className="ghost-link" href="/">Front-office</a>
          <span className="status-pill"><FontAwesomeIcon icon={appIcons.security} /> {userEmail}</span>
          <button className="ghost-button" type="button" onClick={onSignOut}>
            <FontAwesomeIcon icon={appIcons.logout} /> Sortir
          </button>
        </div>
      </header>
      {route.slug === 'design-system' ? (
        <DesignSystemPage designTokens={[]} />
      ) : (
        <AdminModulePage
          route={route}
          isSuperAdmin={isSuperAdmin}
          boutiques={boutiques.filter((boutique) =>
            `${boutique.name} ${boutique.slug} ${boutique.status}`.toLowerCase().includes(search.toLowerCase()),
          )}
          notifications={notifications}
          notice={notice}
          getAccessToken={getAccessToken}
          onBoutiquesChange={setBoutiques}
          onNotice={setNotice}
        />
      )}
    </div>
  );
}

function SideNav({ activeSlug, adminRoutes, userEmail, defaultBoutique }: { activeSlug: string; adminRoutes: RouteConfig[]; userEmail: string; defaultBoutique?: { id: string; name: string; slug: string; status: string } }) {
  const initials = (defaultBoutique?.name ?? userEmail).split(' ').map((part) => part[0]).join('').slice(0, 2).toUpperCase();

  return (
    <aside className="side-nav">
      <div className="side-brand">
        <h1>Boutique OS</h1>
        <p>Back-office dynamique</p>
      </div>
      <nav className="side-menu" aria-label="Navigation back-office">
        {adminRoutes.map((route) => (
          <a className={activeSlug === route.slug ? 'active' : ''} href={route.path} key={route.slug}>
            <FontAwesomeIcon icon={appIcons[route.icon] ?? appIcons.shop} />
            <span>{route.title}</span>
          </a>
        ))}
      </nav>
      <div className="side-profile">
        <div className="avatar">{initials}</div>
        <div>
          <strong>{defaultBoutique?.name ?? 'Boutique'}</strong>
          <span>{defaultBoutique?.status === 'published' ? 'Flagship Store' : (defaultBoutique?.status ?? 'Compte')}</span>
        </div>
      </div>
    </aside>
  );
}

function AdminModulePage({
  route,
  isSuperAdmin,
  boutiques,
  notifications,
  notice,
  getAccessToken,
  onBoutiquesChange,
  onNotice,
}: {
  route: RouteConfig;
  isSuperAdmin: boolean;
  boutiques: BoutiqueRecord[];
  notifications: NotificationRecord[];
  notice: string;
  getAccessToken: () => string | null;
  onBoutiquesChange: (boutiques: BoutiqueRecord[]) => void;
  onNotice: (notice: string) => void;
}) {
  const isBoutiqueModule = route.slug === 'boutiques' || route.slug === 'users';
  const activeBoutique = boutiques[0];

  return (
    <main className="dashboard-main">
      <section className="dashboard-hero">
        <div>
          <p className="auth-eyebrow">{route.section}</p>
          <h2>{route.title}</h2>
          <p>{route.description}</p>
        </div>
        <span className="status-pill"><FontAwesomeIcon icon={appIcons[route.icon] ?? appIcons.shop} /> {isBoutiqueModule ? `${boutiques.length} boutique(s)` : route.section}</span>
      </section>

      <div className="action-notice">{notice}</div>

      {notifications.length > 0 && (
        <section className="notification-strip" aria-label="Notifications internes">
          {notifications.slice(0, 3).map((notification) => (
            <article key={notification.id}>
              <strong>{notification.title}</strong>
              <span>{notification.message}</span>
            </article>
          ))}
        </section>
      )}

      {isBoutiqueModule && (
        <BoutiqueManagementPanel boutiques={boutiques} isSuperAdmin={isSuperAdmin} getAccessToken={getAccessToken} onBoutiquesChange={onBoutiquesChange} onNotice={onNotice} />
      )}

      {!isBoutiqueModule && (
        <DynamicModuleContent route={route} boutique={activeBoutique} boutiques={boutiques} boutiquesCount={boutiques.length} notifications={notifications} isSuperAdmin={isSuperAdmin} getAccessToken={getAccessToken} onNotice={onNotice} />
      )}
    </main>
  );
}

function DynamicModuleContent({
  route,
  boutique,
  boutiques,
  boutiquesCount,
  notifications,
  isSuperAdmin,
  getAccessToken,
  onNotice,
}: {
  route: RouteConfig;
  boutique?: BoutiqueRecord;
  boutiques: BoutiqueRecord[];
  boutiquesCount: number;
  notifications: NotificationRecord[];
  isSuperAdmin: boolean;
  getAccessToken: () => string | null;
  onNotice: (notice: string) => void;
}) {
  if (route.slug === 'dashboard' || route.slug === 'super-admin-dashboard' || route.slug === 'boutique-dashboard') {
    if (route.slug === 'super-admin-dashboard') {
      return <SuperAdminDashboardScreen boutiquesCount={boutiquesCount} getAccessToken={getAccessToken} notifications={notifications} />;
    }

    if (route.slug === 'boutique-dashboard') {
      return <BoutiqueManagementDashboardScreen boutiquesCount={boutique ? 1 : 0} />;
    }

    return <DashboardPanel boutique={boutique} />;
  }

  if (!boutique) {
    return <EmptyApiPanel title={route.title} message="Aucune boutique associée à ce compte." />;
  }

  if (route.slug === 'sponsors') {
    return <SponsorManagementPanel boutique={boutique} isSuperAdmin={isSuperAdmin} getAccessToken={getAccessToken} onNotice={onNotice} />;
  }

  if (route.slug === 'settings') {
    return <SettingsScreen />;
  }

  if (route.slug === 'theme') {
    return <FrontOfficeCustomizationScreen boutique={boutique} getAccessToken={getAccessToken} onNotice={onNotice} />;
  }

  if (route.slug === 'orders') {
    return <OrdersScreen />;
  }

  if (route.slug === 'order-detail') {
    return <OrderDetailScreen />;
  }

  if (route.slug === 'products' || route.slug === 'product-inventory' || route.slug === 'stock-movements') {
    return <ProductInventoryScreen boutique={boutique ? { id: boutique.id, name: boutique.name } : undefined} getAccessToken={getAccessToken} onNotice={onNotice} />;
  }

  if (route.slug === 'promotions') {
    return <MarketingPromotionsScreen />;
  }

  if (route.slug === 'chatbot-config') {
    return <ChatbotConfigScreen />;
  }

  if (route.slug === 'chat') {
    const token = getAccessToken();

    return token ? <AdminChatPanel apiBaseUrl="/api" token={token} /> : <EmptyApiPanel title={route.title} message="Session expirée. Reconnectez-vous." />;
  }

  if (route.slug === 'subscriptions') {
    return <SubscriptionsScreen boutiques={boutiques.map((item) => ({ id: item.id, name: item.name }))} getAccessToken={getAccessToken} isSuperAdmin={isSuperAdmin} onNotice={onNotice} />;
  }

  if (route.slug === 'delivery-companies') {
    return <DeliveryCompaniesScreen getAccessToken={getAccessToken} isSuperAdmin={isSuperAdmin} onNotice={onNotice} />;
  }

  if (route.slug === 'delivery-accounts') {
    return <DeliveryAccountsScreen boutique={boutique ? { id: boutique.id, name: boutique.name } : undefined} boutiques={boutiques.map((item) => ({ id: item.id, name: item.name }))} getAccessToken={getAccessToken} onNotice={onNotice} />;
  }

  const module = moduleConfig(route.slug, boutique.id);

  if (!module) {
    return <EmptyApiPanel title={route.title} message="Module non encore raccordé à une API." />;
  }

  return <ApiCollectionPanel config={module} getAccessToken={getAccessToken} onNotice={onNotice} />;
}

function DashboardPanel({ boutique }: { boutique?: BoutiqueRecord }) {
  return (
    <DashboardScreen
      boutiqueName={boutique?.name}
      boutiqueStatus={boutique?.status ? statusLabel(boutique.status) : undefined}
      productsCount={boutique?.productsCount ?? 0}
      usersCount={boutique?.usersCount ?? 0}
    />
  );
}

function EmptyApiPanel({ title, message }: { title: string; message: string }) {
  return (
    <section className="dashboard-grid">
      <article className="panel panel-wide">
        <div className="panel-header">
          <div>
            <h3>{title}</h3>
            <p>{message}</p>
          </div>
        </div>
        <p className="empty-state">Aucune donnée disponible depuis l'API.</p>
      </article>
    </section>
  );
}

type ModuleConfig = {
  title: string;
  endpoint: string;
  columns: Array<{ label: string; value: (item: Record<string, unknown>) => string }>;
};

function moduleConfig(slug: string, boutiqueId: string): ModuleConfig | null {
  const endpoint = (path: string) => `/api/boutiques/${boutiqueId}/${path}`;
  const text = (key: string) => (item: Record<string, unknown>) => String(item[key] ?? '-');
  const money = (key: string) => (item: Record<string, unknown>) => {
    const cents = Number(item[key] ?? 0);
    return `${(cents / 100).toFixed(2)} EUR`;
  };
  const date = (key: string) => (item: Record<string, unknown>) => {
    const value = item[key];
    return typeof value === 'string' && value ? new Date(value).toLocaleDateString('fr-FR') : '-';
  };

  const configs: Record<string, ModuleConfig> = {
    customers: {
      title: 'Clients',
      endpoint: endpoint('customers'),
      columns: [
        { label: 'Nom', value: (item) => `${item.firstName ?? ''} ${item.lastName ?? ''}`.trim() || '-' },
        { label: 'Email', value: text('email') },
        { label: 'Téléphone', value: text('phone') },
        { label: 'Créé le', value: date('createdAt') },
      ],
    },
    orders: {
      title: 'Commandes',
      endpoint: endpoint('orders'),
      columns: [
        { label: 'Canal', value: text('channel') },
        { label: 'Statut', value: text('status') },
        { label: 'Total', value: money('totalCents') },
        { label: 'Créée le', value: date('createdAt') },
      ],
    },
    products: {
      title: 'Produits',
      endpoint: endpoint('products'),
      columns: [
        { label: 'Nom', value: text('name') },
        { label: 'SKU', value: text('sku') },
        { label: 'Prix', value: money('priceCents') },
        { label: 'Statut', value: text('status') },
      ],
    },
    categories: {
      title: 'Catégories',
      endpoint: endpoint('categories'),
      columns: [
        { label: 'Nom', value: text('name') },
        { label: 'Slug', value: text('slug') },
        { label: 'Produits', value: text('productsCount') },
        { label: 'Créée le', value: date('createdAt') },
      ],
    },
    promotions: {
      title: 'Promotions',
      endpoint: endpoint('promotions'),
      columns: [
        { label: 'Nom', value: text('name') },
        { label: 'Type', value: text('type') },
        { label: 'Valeur', value: text('value') },
        { label: 'Statut', value: text('status') },
      ],
    },
    'product-inventory': {
      title: 'Inventaire',
      endpoint: endpoint('products'),
      columns: [
        { label: 'Produit', value: text('name') },
        { label: 'SKU', value: text('sku') },
        { label: 'Stock', value: text('stock') },
        { label: 'Statut', value: text('status') },
      ],
    },
    'stock-movements': {
      title: 'Mouvements de stock',
      endpoint: endpoint('stock-movements'),
      columns: [
        { label: 'Produit', value: text('productName') },
        { label: 'Type', value: text('type') },
        { label: 'Quantité', value: text('quantity') },
        { label: 'Date', value: date('createdAt') },
      ],
    },
    pos: {
      title: 'Caisse POS',
      endpoint: endpoint('orders'),
      columns: [
        { label: 'Commande', value: text('id') },
        { label: 'Canal', value: text('channel') },
        { label: 'Total', value: money('totalCents') },
        { label: 'Statut', value: text('status') },
      ],
    },
    loyalty: {
      title: 'Fidélité',
      endpoint: endpoint('loyalty'),
      columns: [
        { label: 'Client', value: text('customerEmail') },
        { label: 'Points', value: text('points') },
        { label: 'Solde', value: text('balance') },
        { label: 'Date', value: date('createdAt') },
      ],
    },
    sponsors: {
      title: 'Sponsors boutique',
      endpoint: endpoint('sponsors'),
      columns: [
        { label: 'Sponsor', value: text('name') },
        { label: 'Scope', value: text('scope') },
        { label: 'Actif', value: (item) => item.active ? 'Oui' : 'Non' },
        { label: 'URL', value: text('targetUrl') },
      ],
    },
    'chatbot-config': {
      title: 'Messages chatbot',
      endpoint: endpoint('messages'),
      columns: [
        { label: 'Client', value: text('customerEmail') },
        { label: 'Message', value: text('message') },
        { label: 'Statut', value: text('status') },
        { label: 'Date', value: date('createdAt') },
      ],
    },
  };

  return configs[slug] ?? null;
}

function ApiCollectionPanel({ config, getAccessToken, onNotice }: { config: ModuleConfig; getAccessToken: () => string | null; onNotice: (notice: string) => void }) {
  const [items, setItems] = useState<Array<Record<string, unknown>>>([]);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    const token = getAccessToken();
    if (!token) {
      setIsLoading(false);
      return;
    }

    setIsLoading(true);
    apiGet<{ member?: Array<Record<string, unknown>>; items?: Array<Record<string, unknown>> } | Array<Record<string, unknown>>>(config.endpoint, token)
      .then((payload) => {
        if (Array.isArray(payload)) {
          setItems(payload);
          return;
        }
        setItems(payload.member ?? payload.items ?? []);
      })
      .catch((exception) => {
        setItems([]);
        onNotice(exception instanceof Error ? exception.message : 'Chargement impossible.');
      })
      .finally(() => setIsLoading(false));
  }, [config.endpoint, getAccessToken, onNotice]);

  return (
    <section className="dashboard-grid">
      <article className="panel panel-wide">
        <div className="panel-header">
          <div>
            <h3>{config.title}</h3>
            <p>Données chargées depuis {config.endpoint}</p>
          </div>
          <span className="status-pill">{isLoading ? 'Chargement' : `${items.length} entrée(s)`}</span>
        </div>
        <div className="data-table">
          {items.map((item, index) => (
            <div className="data-row" key={String(item.id ?? index)}>
              {config.columns.map((column) => (
                <span key={column.label}>{column.value(item)}</span>
              ))}
            </div>
          ))}
          {!isLoading && items.length === 0 && <p className="empty-state">Aucune donnée disponible depuis l'API.</p>}
        </div>
      </article>
    </section>
  );
}

function SponsorManagementPanel({ boutique, isSuperAdmin, getAccessToken, onNotice }: { boutique: BoutiqueRecord; isSuperAdmin: boolean; getAccessToken: () => string | null; onNotice: (notice: string) => void }) {
  const [sponsors, setSponsors] = useState<SponsorRecord[]>([]);
  const [boutiqueSponsors, setBoutiqueSponsors] = useState<SponsorRecord[]>([]);
  const [sponsorForm, setSponsorForm] = useState({ name: '', scope: 'global', logoUrl: '', targetUrl: '' });
  const [selectedSponsorId, setSelectedSponsorId] = useState('');
  const [position, setPosition] = useState(0);
  const [isSubmitting, setIsSubmitting] = useState(false);

  async function refresh(token: string) {
    const [allSponsors, assignedSponsors] = await Promise.all([
      apiGet<{ member?: SponsorRecord[]; items?: SponsorRecord[] } | SponsorRecord[]>('/api/sponsors', token),
      apiGet<{ member?: SponsorRecord[]; items?: SponsorRecord[] } | SponsorRecord[]>(`/api/boutiques/${boutique.id}/sponsors`, token),
    ]);
    setSponsors(Array.isArray(allSponsors) ? allSponsors : allSponsors.member ?? allSponsors.items ?? []);
    setBoutiqueSponsors(Array.isArray(assignedSponsors) ? assignedSponsors : assignedSponsors.member ?? assignedSponsors.items ?? []);
  }

  useEffect(() => {
    const token = getAccessToken();
    if (token) {
      refresh(token).catch((exception) => onNotice(exception instanceof Error ? exception.message : 'Chargement sponsors impossible.'));
    }
  }, [boutique.id, getAccessToken, onNotice]);

  async function createSponsor(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    const token = getAccessToken();
    if (!token) return;

    setIsSubmitting(true);
    try {
      await apiSend('/api/sponsors', token, 'POST', {
        name: sponsorForm.name,
        scope: sponsorForm.scope,
        logoUrl: sponsorForm.logoUrl || null,
        targetUrl: sponsorForm.targetUrl || null,
        active: true,
      });
      setSponsorForm({ name: '', scope: 'global', logoUrl: '', targetUrl: '' });
      await refresh(token);
      onNotice('Sponsor créé.');
    } catch (exception) {
      onNotice(exception instanceof Error ? exception.message : 'Création sponsor impossible.');
    } finally {
      setIsSubmitting(false);
    }
  }

  async function assignSponsor(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    const token = getAccessToken();
    if (!token || !selectedSponsorId) return;

    setIsSubmitting(true);
    try {
      await apiSend(`/api/boutiques/${boutique.id}/sponsors`, token, 'POST', { sponsorId: selectedSponsorId, position, active: true });
      setSelectedSponsorId('');
      setPosition(0);
      await refresh(token);
      onNotice('Sponsor assigné à la boutique.');
    } catch (exception) {
      onNotice(exception instanceof Error ? exception.message : 'Assignation sponsor impossible.');
    } finally {
      setIsSubmitting(false);
    }
  }

  return (
    <section className="dashboard-grid boutique-management-grid">
      {isSuperAdmin && (
        <article className="panel">
          <div className="panel-header">
            <div>
              <h3>Créer un sponsor</h3>
              <p>Création globale par Super Admin.</p>
            </div>
          </div>
          <form className="module-form" onSubmit={createSponsor}>
            <label>Nom<input value={sponsorForm.name} onChange={(event) => setSponsorForm((current) => ({ ...current, name: event.target.value }))} required /></label>
            <label>Scope<select value={sponsorForm.scope} onChange={(event) => setSponsorForm((current) => ({ ...current, scope: event.target.value }))}><option value="global">Global</option><option value="boutique">Boutique</option></select></label>
            <label>Logo URL<input value={sponsorForm.logoUrl} onChange={(event) => setSponsorForm((current) => ({ ...current, logoUrl: event.target.value }))} /></label>
            <label>URL cible<input value={sponsorForm.targetUrl} onChange={(event) => setSponsorForm((current) => ({ ...current, targetUrl: event.target.value }))} /></label>
            <button type="submit" disabled={isSubmitting}>{isSubmitting ? 'Création...' : 'Créer sponsor'}</button>
          </form>
        </article>
      )}

      <article className="panel">
        <div className="panel-header">
          <div>
            <h3>Assigner sponsor</h3>
            <p>Boutique : {boutique.name}</p>
          </div>
        </div>
        <form className="module-form" onSubmit={assignSponsor}>
          <label>Sponsor<select value={selectedSponsorId} onChange={(event) => setSelectedSponsorId(event.target.value)} required><option value="">— Choisir —</option>{sponsors.map((sponsor) => <option key={sponsor.id} value={sponsor.id}>{sponsor.name}</option>)}</select></label>
          <label>Position<input type="number" value={position} onChange={(event) => setPosition(Number(event.target.value))} /></label>
          <button type="submit" disabled={isSubmitting || !selectedSponsorId}>Assigner</button>
        </form>
      </article>

      <article className="panel panel-wide">
        <div className="panel-header">
          <div>
            <h3>Sponsors assignés</h3>
            <p>Données chargées depuis l'API.</p>
          </div>
          <span className="status-pill">{boutiqueSponsors.length} sponsor(s)</span>
        </div>
        <div className="data-table">
          {boutiqueSponsors.map((sponsor) => (
            <div className="data-row" key={sponsor.id}>
              <strong>{sponsor.name}</strong>
              <span>{sponsor.scope}</span>
              <span>{sponsor.targetUrl ?? '-'}</span>
              <span className="badge">{sponsor.active ? 'Actif' : 'Inactif'}</span>
            </div>
          ))}
          {boutiqueSponsors.length === 0 && <p className="empty-state">Aucun sponsor assigné.</p>}
        </div>
      </article>
    </section>
  );
}

function BoutiqueManagementPanel({
  boutiques,
  isSuperAdmin,
  getAccessToken,
  onBoutiquesChange,
  onNotice,
}: {
  boutiques: BoutiqueRecord[];
  isSuperAdmin: boolean;
  getAccessToken: () => string | null;
  onBoutiquesChange: (boutiques: BoutiqueRecord[]) => void;
  onNotice: (notice: string) => void;
}) {
  const [form, setForm] = useState({
    name: '',
    slug: '',
    contactEmail: '',
    primaryColor: '#3525cd',
    secondaryColor: '#505f76',
  });
  const [isSubmitting, setIsSubmitting] = useState(false);

  const [userForm, setUserForm] = useState({
    email: '',
    password: '',
    displayName: '',
    role: 'ROLE_BOUTIQUE_ADMIN',
    boutiqueId: '',
  });
  const [isUserSubmitting, setIsUserSubmitting] = useState(false);
  const [users, setUsers] = useState<UserRecord[]>([]);

  const [subscriptions, setSubscriptions] = useState<SubscriptionRecord[]>([]);
  const [selectedBoutiqueId, setSelectedBoutiqueId] = useState('');
  const [selectedPlan, setSelectedPlan] = useState('3months');
  const [isSubSubmitting, setIsSubSubmitting] = useState(false);

  const roleOptions = [
    { value: 'ROLE_BOUTIQUE_ADMIN', label: 'Admin boutique' },
    { value: 'ROLE_CAISSIER', label: 'Caissier' },
  ];

  const planOptions = [
    { value: '3months', label: '3 mois — 29,99 €', months: 3 },
    { value: '6months', label: '6 mois — 49,99 €', months: 6 },
    { value: '1year', label: '1 an — 89,99 €', months: 12 },
  ];

  async function refreshBoutiques(token: string) {
    const payload = await apiGet<{ member?: BoutiqueRecord[]; items?: BoutiqueRecord[] }>('/api/boutiques', token);
    onBoutiquesChange(payload.member ?? payload.items ?? []);
  }

  async function loadUsers(token: string) {
    try {
      const payload = await apiGet<{ users: UserRecord[] }>('/api/users', token);
      setUsers(payload.users ?? []);
    } catch {
      setUsers([]);
    }
  }

  async function loadSubscriptions(token: string, boutiqueId: string) {
    try {
      const payload = await apiGet<SubscriptionRecord[]>(`/api/boutiques/${boutiqueId}/subscriptions`, token);
      setSubscriptions(payload);
    } catch {
      setSubscriptions([]);
    }
  }

  useEffect(() => {
    if (isSuperAdmin) {
      const token = getAccessToken();
      if (token) {
        loadUsers(token);
      }
    }
  }, [isSuperAdmin, getAccessToken]);

  useEffect(() => {
    if (selectedBoutiqueId) {
      const token = getAccessToken();
      if (token) {
        loadSubscriptions(token, selectedBoutiqueId);
      }
    } else {
      setSubscriptions([]);
    }
  }, [selectedBoutiqueId, getAccessToken]);

  async function createBoutique(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    const token = getAccessToken();
    if (!token) {
      onNotice('Session expirée. Reconnectez-vous.');
      return;
    }

    setIsSubmitting(true);
    try {
      await apiSend('/api/boutiques', token, 'POST', {
        ...form,
        status: 'pending',
        logoUrl: null,
        domain: null,
        contactPhone: null,
        address: null,
        socialLinks: {},
      });
      await refreshBoutiques(token);
      setForm({ name: '', slug: '', contactEmail: '', primaryColor: '#3525cd', secondaryColor: '#505f76' });
      onNotice(isSuperAdmin ? 'Boutique créée et approuvée.' : 'Boutique créée avec le statut En attente de validation.');
    } catch (exception) {
      onNotice(exception instanceof Error ? exception.message : 'Création boutique impossible.');
    } finally {
      setIsSubmitting(false);
    }
  }

  async function createUser(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    const token = getAccessToken();
    if (!token) {
      onNotice('Session expirée. Reconnectez-vous.');
      return;
    }

    setIsUserSubmitting(true);
    try {
      const body: Record<string, unknown> = {
        email: userForm.email,
        password: userForm.password,
        displayName: userForm.displayName,
        roles: [userForm.role],
      };
      if (userForm.boutiqueId) {
        body.boutiqueId = userForm.boutiqueId;
      }
      await apiSend('/api/auth/admin-create-user', token, 'POST', body);
      await loadUsers(token);
      setUserForm({ email: '', password: '', displayName: '', role: 'ROLE_BOUTIQUE_ADMIN', boutiqueId: '' });
      onNotice('Utilisateur créé avec succès.');
    } catch (exception) {
      onNotice(exception instanceof Error ? exception.message : 'Création utilisateur impossible.');
    } finally {
      setIsUserSubmitting(false);
    }
  }

  async function createSubscription(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    const token = getAccessToken();
    if (!token || !selectedBoutiqueId) {
      onNotice('Sélectionnez une boutique.');
      return;
    }

    setIsSubSubmitting(true);
    try {
      await apiSend(`/api/boutiques/${selectedBoutiqueId}/subscriptions`, token, 'POST', { plan: selectedPlan });
      await loadSubscriptions(token, selectedBoutiqueId);
      onNotice('Demande d\'abonnement envoyée. En attente de validation Super Admin.');
    } catch (exception) {
      onNotice(exception instanceof Error ? exception.message : 'Abonnement impossible.');
    } finally {
      setIsSubSubmitting(false);
    }
  }

  async function transitionSubscription(boutiqueId: string, subId: string, action: 'accept' | 'reject') {
    const token = getAccessToken();
    if (!token) return;

    try {
      await apiSend(`/api/boutiques/${boutiqueId}/subscriptions/${subId}/${action}`, token, 'PATCH');
      await loadSubscriptions(token, boutiqueId);
      onNotice(`Abonnement ${action === 'accept' ? 'accepté' : 'refusé'}.`);
    } catch (exception) {
      onNotice(exception instanceof Error ? exception.message : 'Action impossible.');
    }
  }

  async function transitionBoutique(id: string, action: 'approve' | 'reject' | 'publish') {
    const token = getAccessToken();
    if (!token) {
      onNotice('Session expirée. Reconnectez-vous.');
      return;
    }

    try {
      await apiSend(`/api/boutiques/${id}/${action}`, token, 'PATCH');
      await refreshBoutiques(token);
      onNotice(`Statut boutique mis à jour : ${action}.`);
    } catch (exception) {
      onNotice(exception instanceof Error ? exception.message : 'Transition impossible.');
    }
  }

  const activeSubscription = subscriptions.find((s) => s.status === 'active');
  const pendingBoutiques = boutiques.filter((boutique) => boutique.status === 'pending').length;
  const activeSubscriptions = subscriptions.filter((subscription) => subscription.status === 'active').length;

  function subscriptionStatusLabel(status: string): string {
    const labels: Record<string, string> = {
      pending: 'En attente',
      active: 'Actif',
      expired: 'Expiré',
      cancelled: 'Annulé',
      rejected: 'Refusé',
    };
    return labels[status] ?? status;
  }

  function boutiqueTone(status: string): 'success' | 'warning' | 'error' | 'neutral' {
    if (status === 'approved' || status === 'published') return 'success';
    if (status === 'pending') return 'warning';
    if (status === 'rejected') return 'error';
    return 'neutral';
  }

  return (
    <section className="space-y-6">
      <Card className="ds-hero">
        <div className="grid gap-6 lg:grid-cols-[1fr_auto] lg:items-center">
          <div>
            <p className="ds-hero__eyebrow">Boutique management</p>
            <h1 className="ds-hero__title">Gestion des boutiques</h1>
            <p className="ds-hero__subtitle">Créez les boutiques, pilotez les abonnements et gardez une vue claire sur les comptes plateforme.</p>
          </div>
          <div className="grid gap-3 sm:grid-cols-3">
            <Card className="bg-[color:var(--ds-surface-container-lowest)]/90 p-4">
              <p className="text-xs font-bold uppercase tracking-[0.15em] text-[color:var(--ds-on-surface-variant)]">Boutiques</p>
              <strong className="mt-2 block text-3xl">{boutiques.length}</strong>
            </Card>
            <Card className="bg-[color:var(--ds-surface-container-lowest)]/90 p-4">
              <p className="text-xs font-bold uppercase tracking-[0.15em] text-[color:var(--ds-on-surface-variant)]">En attente</p>
              <strong className="mt-2 block text-3xl">{pendingBoutiques}</strong>
            </Card>
            <Card className="bg-[color:var(--ds-surface-container-lowest)]/90 p-4">
              <p className="text-xs font-bold uppercase tracking-[0.15em] text-[color:var(--ds-on-surface-variant)]">Actifs</p>
              <strong className="mt-2 block text-3xl">{activeSubscriptions}</strong>
            </Card>
          </div>
        </div>
      </Card>

      <div className="grid gap-6 xl:grid-cols-2">
        <Card>
          <div className="flex items-start justify-between gap-4">
            <div>
              <p className="ds-hero__eyebrow">Boutique</p>
              <h2 className="mt-2 text-2xl font-bold">Créer une boutique</h2>
              <p className="mt-1 text-sm text-[color:var(--ds-on-surface-variant)]">{isSuperAdmin ? 'Boutique approuvée immédiatement.' : 'Boutique créée avec validation Super Admin.'}</p>
            </div>
            <Badge tone={isSuperAdmin ? 'success' : 'warning'}>{isSuperAdmin ? 'Auto-validé' : 'En attente'}</Badge>
          </div>

          <form className="mt-6 grid gap-4" onSubmit={createBoutique}>
            <label className="block">
              <span className="mb-2 block text-sm font-semibold text-[color:var(--ds-on-surface-variant)]">Nom</span>
              <Input value={form.name} onChange={(event) => setForm((current) => ({ ...current, name: event.target.value }))} required placeholder="Maison Atelier" />
            </label>
            <label className="block">
              <span className="mb-2 block text-sm font-semibold text-[color:var(--ds-on-surface-variant)]">Slug</span>
              <Input value={form.slug} onChange={(event) => setForm((current) => ({ ...current, slug: event.target.value }))} required pattern="[a-z0-9-]+" placeholder="maison-atelier" />
            </label>
            <label className="block">
              <span className="mb-2 block text-sm font-semibold text-[color:var(--ds-on-surface-variant)]">Email contact</span>
              <Input type="email" value={form.contactEmail} onChange={(event) => setForm((current) => ({ ...current, contactEmail: event.target.value }))} placeholder="contact@boutique.fr" />
            </label>
            <div className="grid gap-4 sm:grid-cols-2">
              <label className="block">
                <span className="mb-2 block text-sm font-semibold text-[color:var(--ds-on-surface-variant)]">Couleur primaire</span>
                <Input type="color" value={form.primaryColor} onChange={(event) => setForm((current) => ({ ...current, primaryColor: event.target.value }))} className="h-12" />
              </label>
              <label className="block">
                <span className="mb-2 block text-sm font-semibold text-[color:var(--ds-on-surface-variant)]">Couleur secondaire</span>
                <Input type="color" value={form.secondaryColor} onChange={(event) => setForm((current) => ({ ...current, secondaryColor: event.target.value }))} className="h-12" />
              </label>
            </div>
            <Button type="submit" variant="primary" disabled={isSubmitting}>{isSubmitting ? 'Création...' : 'Créer la boutique'}</Button>
          </form>
        </Card>

        {isSuperAdmin && (
          <Card>
            <div>
              <p className="ds-hero__eyebrow">Utilisateur</p>
              <h2 className="mt-2 text-2xl font-bold">Créer un utilisateur</h2>
              <p className="mt-1 text-sm text-[color:var(--ds-on-surface-variant)]">Ajouter un admin ou un caissier et l&apos;assigner à une boutique.</p>
            </div>

            <form className="mt-6 grid gap-4" onSubmit={createUser}>
              <label className="block">
                <span className="mb-2 block text-sm font-semibold text-[color:var(--ds-on-surface-variant)]">Email</span>
                <Input type="email" value={userForm.email} onChange={(event) => setUserForm((current) => ({ ...current, email: event.target.value }))} required placeholder="admin@boutique.fr" />
              </label>
              <label className="block">
                <span className="mb-2 block text-sm font-semibold text-[color:var(--ds-on-surface-variant)]">Mot de passe</span>
                <Input type="password" value={userForm.password} onChange={(event) => setUserForm((current) => ({ ...current, password: event.target.value }))} required minLength={8} placeholder="8 caractères minimum" />
              </label>
              <label className="block">
                <span className="mb-2 block text-sm font-semibold text-[color:var(--ds-on-surface-variant)]">Nom complet</span>
                <Input value={userForm.displayName} onChange={(event) => setUserForm((current) => ({ ...current, displayName: event.target.value }))} placeholder="Admin Boutique" />
              </label>
              <label className="block">
                <span className="mb-2 block text-sm font-semibold text-[color:var(--ds-on-surface-variant)]">Rôle</span>
                <Select value={userForm.role} onChange={(event) => setUserForm((current) => ({ ...current, role: event.target.value }))}>
                  {roleOptions.map((option) => <option key={option.value} value={option.value}>{option.label}</option>)}
                </Select>
              </label>
              <label className="block">
                <span className="mb-2 block text-sm font-semibold text-[color:var(--ds-on-surface-variant)]">Assigner à une boutique (optionnel)</span>
                <Select value={userForm.boutiqueId} onChange={(event) => setUserForm((current) => ({ ...current, boutiqueId: event.target.value }))}>
                  <option value="">Sans boutique</option>
                  {boutiques.map((boutique) => <option key={boutique.id} value={boutique.id}>{boutique.name}</option>)}
                </Select>
              </label>
              <Button type="submit" variant="secondary" disabled={isUserSubmitting}>{isUserSubmitting ? 'Création...' : 'Créer l\'utilisateur'}</Button>
            </form>
          </Card>
        )}
      </div>

      <div className="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
        <Card>
          <div className="flex items-center justify-between gap-4">
            <div>
              <p className="ds-hero__eyebrow">Boutiques</p>
              <h2 className="mt-2 text-2xl font-bold">Boutiques accessibles</h2>
              <p className="mt-1 text-sm text-[color:var(--ds-on-surface-variant)]">{isSuperAdmin ? 'Accès total plateforme.' : 'Vue limitée aux boutiques associées à votre compte.'}</p>
            </div>
            <Badge tone="neutral">{boutiques.length} boutique{boutiques.length !== 1 ? 's' : ''}</Badge>
          </div>

          <div className="mt-6 space-y-3">
            {boutiques.map((boutique) => (
              <div key={boutique.id} className="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-[color:var(--ds-outline-variant)] bg-[color:var(--ds-surface-container-lowest)] p-4">
                <div>
                  <strong className="block">{boutique.name}</strong>
                  <p className="text-sm text-[color:var(--ds-on-surface-variant)]">{boutique.slug} · {boutique.contactEmail ?? 'Contact non renseigné'}</p>
                </div>
                <div className="flex flex-wrap items-center gap-2">
                  <Badge tone={boutiqueTone(boutique.status)}>{statusLabel(boutique.status)}</Badge>
                  {isSuperAdmin && boutique.status === 'pending' && <Button type="button" variant="primary" onClick={() => transitionBoutique(boutique.id, 'approve')}>Approuver</Button>}
                  {isSuperAdmin && boutique.status === 'pending' && <Button type="button" variant="ghost" className="text-[color:var(--ds-error)]" onClick={() => transitionBoutique(boutique.id, 'reject')}>Rejeter</Button>}
                  {isSuperAdmin && boutique.status === 'approved' && <Button type="button" variant="secondary" onClick={() => transitionBoutique(boutique.id, 'publish')}>Publier</Button>}
                </div>
              </div>
            ))}
            {boutiques.length === 0 && <p className="py-2 text-sm text-[color:var(--ds-on-surface-variant)]">Aucune boutique disponible pour ce compte.</p>}
          </div>
        </Card>

        <div className="space-y-6">
          <Card>
            <div className="flex items-center justify-between gap-4">
              <div>
                <p className="ds-hero__eyebrow">Abonnements</p>
                <h2 className="mt-2 text-2xl font-bold">Abonnement boutique</h2>
                <p className="mt-1 text-sm text-[color:var(--ds-on-surface-variant)]">Souscrire un abonnement pour activer les fonctionnalités de la boutique.</p>
              </div>
              {activeSubscription && <Badge tone="success">{activeSubscription.plan}</Badge>}
            </div>

            <form className="mt-6 grid gap-4" onSubmit={createSubscription}>
              <label className="block">
                <span className="mb-2 block text-sm font-semibold text-[color:var(--ds-on-surface-variant)]">Boutique</span>
                <Select value={selectedBoutiqueId} onChange={(event) => setSelectedBoutiqueId(event.target.value)} required>
                  <option value="">Choisir une boutique</option>
                  {boutiques.map((boutique) => <option key={boutique.id} value={boutique.id}>{boutique.name}</option>)}
                </Select>
              </label>
              <label className="block">
                <span className="mb-2 block text-sm font-semibold text-[color:var(--ds-on-surface-variant)]">Forfait</span>
                <Select value={selectedPlan} onChange={(event) => setSelectedPlan(event.target.value)}>
                  {planOptions.map((option) => <option key={option.value} value={option.value}>{option.label}</option>)}
                </Select>
              </label>
              <Button type="submit" variant="primary" disabled={isSubSubmitting || !selectedBoutiqueId}>
                {isSubSubmitting ? 'En cours...' : isSuperAdmin ? 'Créer un abonnement' : 'Souscrire'}
              </Button>
            </form>

            {subscriptions.length > 0 && (
              <div className="mt-6 space-y-3">
                {subscriptions.map((sub) => (
                  <div key={sub.id} className="rounded-2xl border border-[color:var(--ds-outline-variant)] bg-[color:var(--ds-surface-container-lowest)] p-4">
                    <div className="flex flex-wrap items-center justify-between gap-3">
                      <div>
                        <strong className="block">{sub.boutiqueName}</strong>
                        <p className="text-sm text-[color:var(--ds-on-surface-variant)]">{planOptions.find((p) => p.value === sub.plan)?.label ?? sub.plan}</p>
                      </div>
                      <Badge tone={subscriptionStatusLabel(sub.status) === 'Actif' ? 'success' : subscriptionStatusLabel(sub.status) === 'En attente' ? 'warning' : subscriptionStatusLabel(sub.status) === 'Refusé' ? 'error' : 'neutral'}>
                        {subscriptionStatusLabel(sub.status)}
                      </Badge>
                    </div>
                    <div className="mt-3 grid gap-2 text-sm text-[color:var(--ds-on-surface-variant)] sm:grid-cols-2">
                      <span>Début: {sub.startDate ? new Date(sub.startDate).toLocaleDateString('fr-FR') : '-'}</span>
                      <span>Fin: {sub.endDate ? new Date(sub.endDate).toLocaleDateString('fr-FR') : '-'}</span>
                    </div>
                    {isSuperAdmin && sub.status === 'pending' && (
                      <div className="mt-4 flex flex-wrap gap-2">
                        <Button type="button" variant="secondary" onClick={() => transitionSubscription(sub.boutiqueId, sub.id, 'accept')}>Accepter</Button>
                        <Button type="button" variant="ghost" className="text-[color:var(--ds-error)]" onClick={() => transitionSubscription(sub.boutiqueId, sub.id, 'reject')}>Refuser</Button>
                      </div>
                    )}
                  </div>
                ))}
              </div>
            )}
          </Card>

          {isSuperAdmin && users.length > 0 && (
            <Card>
              <div className="flex items-center justify-between gap-4">
                <div>
                  <p className="ds-hero__eyebrow">Utilisateurs</p>
                  <h2 className="mt-2 text-2xl font-bold">Utilisateurs de la plateforme</h2>
                </div>
                <Badge tone="neutral">{users.length} utilisateur{users.length !== 1 ? 's' : ''}</Badge>
              </div>
              <div className="mt-6 space-y-3">
                {users.map((user) => (
                  <div key={user.id} className="rounded-2xl border border-[color:var(--ds-outline-variant)] bg-[color:var(--ds-surface-container-lowest)] p-4">
                    <strong className="block">{user.displayName ?? user.email}</strong>
                    <p className="text-sm text-[color:var(--ds-on-surface-variant)]">{user.email} · {user.roles.includes('ROLE_SUPER_ADMIN') ? 'Super Admin' : user.roles.includes('ROLE_BOUTIQUE_ADMIN') ? 'Admin' : 'Caissier'}</p>
                    <p className="mt-1 text-sm text-[color:var(--ds-on-surface-variant)]">{user.boutiqueName ?? 'Aucune boutique'}</p>
                  </div>
                ))}
              </div>
            </Card>
          )}
        </div>
      </div>
    </section>
  );
}

async function apiGet<T>(url: string, token: string): Promise<T> {
  const response = await fetch(url, { headers: { Authorization: `Bearer ${token}` } });

  if (!response.ok) {
    throw new Error(`API ${response.status}: ${url}`);
  }

  return response.json() as Promise<T>;
}

async function apiSend<T = unknown>(url: string, token: string, method: 'POST' | 'PATCH' | 'DELETE', body?: unknown): Promise<T> {
  const response = await fetch(url, {
    method,
    headers: {
      Authorization: `Bearer ${token}`,
      ...(body ? { 'Content-Type': 'application/json' } : {}),
    },
    body: body ? JSON.stringify(body) : undefined,
  });

  if (!response.ok) {
    const payload = await response.json().catch(() => ({ detail: `API ${response.status}: ${url}` })) as { detail?: string; message?: string };
    throw new Error(payload.detail ?? payload.message ?? `API ${response.status}: ${url}`);
  }

  return response.status === 204 ? (undefined as T) : response.json() as Promise<T>;
}

async function publicApi<T = unknown>(url: string, method: 'GET' | 'POST' | 'PATCH' | 'DELETE' = 'GET', body?: unknown): Promise<T> {
  const response = await fetch(url, {
    method,
    credentials: 'include',
    headers: body ? { 'Content-Type': 'application/json' } : undefined,
    body: body ? JSON.stringify(body) : undefined,
  });

  if (!response.ok) {
    const payload = await response.json().catch(() => ({ detail: `API ${response.status}: ${url}` })) as { detail?: string; message?: string };
    throw new Error(payload.detail ?? payload.message ?? `API ${response.status}: ${url}`);
  }

  return response.status === 204 ? (undefined as T) : response.json() as Promise<T>;
}

function formatMoney(cents: number, currency: string): string {
  return new Intl.NumberFormat('fr-FR', { style: 'currency', currency }).format(cents / 100);
}

function statusLabel(status: string): string {
  const labels: Record<string, string> = {
    draft: 'Brouillon',
    pending: 'En attente',
    approved: 'Approuvée',
    rejected: 'Rejetée',
    published: 'Publiée',
  };

  return labels[status] ?? status;
}

function MetricCard({ icon, label, value, trend }: { icon: string; label: string; value: string; trend: string }) {
  return (
    <article className="metric-card">
      <div className="metric-icon"><FontAwesomeIcon icon={appIcons[icon as AppIcon] ?? appIcons.shop} /></div>
      <span>{label}</span>
      <strong>{value}</strong>
      <small>{trend}</small>
    </article>
  );
}

function DesignSystemPage({ designTokens }: { designTokens: DesignToken[] }) {
  return (
    <main className="dashboard-main">
      <section className="dashboard-hero">
        <div>
          <p className="auth-eyebrow">System</p>
          <h2>Design System</h2>
          <p>Tokens utilisés par les pages publiques et le back-office dynamique.</p>
        </div>
        <span className="status-pill">{designTokens.length} token(s)</span>
      </section>
      <section className="design-system-grid">
        {designTokens.map((token) => (
          <article className="token-card" key={token.label}>
            <span style={{ background: token.color }} />
            <strong>{token.label}</strong>
            <small>{token.color}</small>
          </article>
        ))}
      </section>
    </main>
  );
}
