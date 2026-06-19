import { ShopLayout, CartIcon, StoreIcon, MailIcon } from './ShopLayout';

function StarIcon() {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" stroke="none">
      <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
    </svg>
  );
}

function HeroTagIcon() {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
    </svg>
  );
}

function CheckIcon() {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
      <polyline points="20 6 9 17 4 12"/>
    </svg>
  );
}

function TrendingIcon() {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/>
    </svg>
  );
}

function DashboardIcon() {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
    </svg>
  );
}

function ShieldIcon() {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
    </svg>
  );
}

function PersonAddIcon() {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/>
    </svg>
  );
}

function SettingsIcon() {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
    </svg>
  );
}

function PaymentIcon() {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/>
    </svg>
  );
}

const featuredProducts = [
  { shop: "L'Atelier Cuir", name: 'Sac en cuir tannage végétal', price: '189,00 €', badge: 'Nouveau', icon: 'bag' },
  { shop: 'TechDirect B2B', name: 'Casque sans fil Pro X2', price: '249,00 €', badge: 'Nouveau', icon: 'headphones' },
  { shop: 'Gourmet Select', name: "Coffret huile d'olive AOP", price: '62,00 €', badge: 'Édition limitée', icon: 'food' },
  { shop: 'Design & Co', name: 'Lampe sculpturale chêne', price: '320,00 €', badge: 'Nouveau', icon: 'lamp' },
];

const testimonials = [
  { initial: 'M', name: 'Marie D.', shop: "L'Atelier Cuir", date: 'il y a 2 jours', rating: '5 / 5', text: 'Qualité exceptionnelle du cuir, livraison rapide et emballage soigné. Le sac dépasse mes attentes !', verified: true },
  { initial: 'J', name: 'Jean-Pierre L.', shop: 'TechDirect B2B', date: 'il y a 1 semaine', rating: '4.5 / 5', text: 'Service pro, casque conforme à la description. Prix compétitif pour le B2B, je recommande.', verified: true },
  { initial: 'S', name: 'Sophie B.', shop: 'Gourmet Select', date: 'il y a 3 jours', rating: '5 / 5', text: "L'huile d'olive est d'une finesse incroyable. Mes clients sont conquis !", verified: true },
];

const steps = [
  { icon: 'person', title: 'Créez votre compte', text: 'Inscrivez-vous en quelques clics et complétez votre profil professionnel pour inspirer confiance.' },
  { icon: 'settings', title: 'Personnalisez votre boutique', text: 'Ajoutez votre logo, vos couleurs et listez vos produits avec descriptions précises et photos HD.' },
  { icon: 'payment', title: 'Commencez à vendre', text: 'Recevez des commandes, gérez vos expéditions et soyez payé directement de manière sécurisée.' },
];

const benefits = [
  { icon: 'trending', title: 'Visibilité accrue', text: "Accédez à un réseau de milliers d'acheteurs B2B en France et à l'international." },
  { icon: 'dashboard', title: 'Outils de gestion pro', text: 'Une console admin puissante pour gérer stocks, factures et statistiques en temps réel.' },
  { icon: 'shield', title: 'Paiement sécurisé', text: 'Toutes les transactions sont cryptées et protégées par nos protocoles bancaires certifiés.' },
];

function ProductIcon({ icon }: { icon: string }) {
  const props = { width: 64, height: 64, viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', strokeWidth: '1.5', strokeLinecap: 'round' as const, strokeLinejoin: 'round' as const };

  switch (icon) {
    case 'bag':
      return <svg {...props}><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>;
    case 'headphones':
      return <svg {...props}><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>;
    case 'food':
      return <svg {...props}><path d="M12 2v20M2 12h20M12 2a10 10 0 0 0-10 10M12 2a10 10 0 0 1 10 10"/></svg>;
    case 'lamp':
      return <svg {...props}><path d="M9 18h6"/><path d="M10 22h4"/><path d="M15.09 14c.18-.98.65-1.74 1.41-2.5A4.65 4.65 0 0 0 18 8 6 6 0 0 0 6 8c0 1 .23 2.23 1.5 3.5A4.61 4.61 0 0 1 8.91 14"/></svg>;
    default:
      return <svg {...props}><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>;
  }
}

export function ShopHome() {
  return (
    <ShopLayout activePath="/shop">
      {/* Hero */}
      <section className="shop-hero">
        <div className="shop-hero__inner">
          <div className="shop-hero__tag">
            <HeroTagIcon />
            Solution Professionnelle
          </div>
          <h1>La Marketplace B2B <span>pour les Indépendants</span></h1>
          <p>
            Propulsez votre activité commerciale avec une plateforme dédiée.
            Hanooty simplifie la gestion de votre boutique, de l&apos;inventaire à la vente sécurisée.
          </p>
          <div className="shop-hero__actions">
            <a href="/shop/catalogue" className="shop-btn shop-btn--primary shop-btn--lg">
              Explorer les produits
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>
            <a href="/admin" className="shop-btn shop-btn--secondary shop-btn--lg">Créer ma Boutique</a>
          </div>
          <div className="shop-hero__stats">
            <div><strong>2,500+</strong><span>Boutiques actives</span></div>
            <div><strong>98%</strong><span>Satisfaction client</span></div>
            <div><strong>15K+</strong><span>Produits référencés</span></div>
          </div>
        </div>
      </section>

      {/* Featured Products */}
      <section className="shop-section shop-section--alt">
        <div className="shop-section__inner">
          <div className="shop-section__header shop-section__header--split">
            <div>
              <h2>Nouveaux Produits</h2>
              <p>Les dernières nouveautés ajoutées par nos marchands.</p>
            </div>
            <a href="/shop/catalogue">Tout voir <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></a>
          </div>
          <div className="shop-featured-grid">
            {featuredProducts.map((product) => (
              <article className="shop-featured-card" key={product.name}>
                <div className="shop-featured-card__media">
                  <span className="shop-featured-card__badge">{product.badge}</span>
                  <ProductIcon icon={product.icon} />
                  <button className="shop-featured-card__cart" type="button" aria-label="Ajouter au panier">
                    <CartIcon />
                  </button>
                </div>
                <div className="shop-featured-card__body">
                  <p className="shop-card__shop">{product.shop}</p>
                  <h3>{product.name}</h3>
                  <strong>{product.price}</strong>
                </div>
              </article>
            ))}
          </div>
        </div>
      </section>

      {/* Testimonials */}
      <section className="shop-section shop-section--muted">
        <div className="shop-section__inner">
          <div className="shop-section__header">
            <h2>Avis Clients</h2>
            <p>Découvrez ce que nos acheteurs professionnels disent de leurs boutiques favorites.</p>
          </div>
          <div className="shop-testimonials">
            {testimonials.map((review) => (
              <article className="shop-testimonial-card" key={`${review.name}-${review.shop}`}>
                <div className="shop-testimonial-card__header">
                  <div className="shop-testimonial-card__avatar">{review.initial}</div>
                  <div className="shop-testimonial-card__author">
                    <strong>{review.name}</strong>
                    <small>{review.shop} · {review.date}</small>
                  </div>
                </div>
                <span className="shop-testimonial-card__rating">
                  {[1, 2, 3, 4, 5].map((star) => (
                    <StarIcon key={star} />
                  ))}
                  {' '}{review.rating}
                </span>
                <p>{review.text}</p>
                {review.verified && (
                  <span className="shop-testimonial-card__verified">
                    <CheckIcon /> Achat vérifié
                  </span>
                )}
              </article>
            ))}
          </div>
        </div>
      </section>

      {/* How It Works */}
      <section className="shop-section shop-section--alt">
        <div className="shop-section__inner">
          <div className="shop-section__header">
            <h2>Comment ça marche</h2>
            <p>Trois étapes simples pour transformer votre commerce.</p>
          </div>
          <div className="shop-steps">
            {steps.map((step, index) => (
              <article className="shop-step-card" key={step.title}>
                <span className="shop-step-card__number">{index + 1}</span>
                <div className="shop-step-card__icon">
                  {step.icon === 'person' ? <PersonAddIcon /> : step.icon === 'settings' ? <SettingsIcon /> : <PaymentIcon />}
                </div>
                <h3>{step.title}</h3>
                <p>{step.text}</p>
              </article>
            ))}
          </div>
        </div>
      </section>

      {/* Benefits */}
      <section className="shop-section shop-section--muted">
        <div className="shop-section__inner">
          <div className="shop-section__header">
            <h2>Pourquoi Hanooty ?</h2>
          </div>
          <div className="shop-benefits">
            {benefits.map((benefit) => (
              <article className="shop-benefit-card" key={benefit.title}>
                <div className="shop-benefit-card__icon">
                  {benefit.icon === 'trending' ? <TrendingIcon /> : benefit.icon === 'dashboard' ? <DashboardIcon /> : <ShieldIcon />}
                </div>
                <h3>{benefit.title}</h3>
                <p>{benefit.text}</p>
              </article>
            ))}
          </div>
        </div>
      </section>

      {/* Newsletter */}
      <section className="shop-newsletter">
        <div className="shop-section__inner">
          <div className="shop-newsletter__card">
            <MailIcon />
            <h2>Restez informé</h2>
            <p>Recevez nos conseils pour booster votre boutique et les dernières tendances du marché.</p>
            <form className="shop-newsletter__form" onSubmit={(e) => e.preventDefault()}>
              <input type="email" placeholder="vous@email.com" aria-label="Adresse email" />
              <button type="submit" className="shop-btn shop-btn--primary">S&apos;inscrire</button>
            </form>
          </div>
        </div>
      </section>
    </ShopLayout>
  );
}
