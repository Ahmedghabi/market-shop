import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../../auth/useAuth';
import { FloatingInfoChat } from '../../components/FloatingInfoChat';

export function HomePage({ canAccessBackOffice }: { canAccessBackOffice: boolean }) {
  const navigate = useNavigate();
  const { user, signOut } = useAuth();
  const isAuthenticated = Boolean(user);
  const boutiques = [
    { initial: 'L', name: "L'Atelier Cuir", category: 'Artisanat & Mode', rating: 5, reviews: '127 personnes ont noté', href: '/boutiques/atelier-cuir' },
    { initial: 'T', name: 'TechDirect B2B', category: 'High-Tech', rating: 4, reviews: '89 personnes ont noté', href: '/boutiques/techdirect-b2b' },
    { initial: 'G', name: 'Gourmet Select', category: 'Alimentation', rating: 5, reviews: '203 personnes ont noté', href: '/boutiques/gourmet-select' },
    { initial: 'D', name: 'Design & Co', category: 'Mobilier & Déco', rating: 4, reviews: '156 personnes ont noté', href: '/boutiques/design-co' },
    { initial: 'M', name: 'Maison Pro', category: 'Équipement', rating: 5, reviews: '118 personnes ont noté', href: '/boutiques/maison-pro' },
    { initial: 'B', name: 'Beauty Stock', category: 'Beauté', rating: 4, reviews: '74 personnes ont noté', href: '/boutiques/beauty-stock' },
  ];
  const products = [
    { shop: "L'Atelier Cuir", name: 'Sac en cuir tannage végétal', price: '189,00 DT', icon: 'shopping_bag', badge: 'Nouveau' },
    { shop: 'TechDirect B2B', name: 'Casque sans fil Pro X2', price: '249,00 DT', icon: 'headphones', badge: 'Nouveau' },
    { shop: 'Gourmet Select', name: "Coffret huile d'olive AOP", price: '62,00 DT', icon: 'restaurant', badge: 'Édition limitée' },
    { shop: 'Design & Co', name: 'Lampe sculpturale chêne', price: '320,00 DT', icon: 'lightbulb', badge: 'Nouveau' },
  ];
  const reviews = [
    { initial: 'M', name: 'Meriem A.', role: 'Boutique indépendante', date: 'il y a 2 jours', rating: 5, text: 'Hanooty nous a donné une présence professionnelle claire et un back-office beaucoup plus simple à gérer.', verified: true },
    { initial: 'Y', name: 'Youssef B.', role: 'Acheteur B2B', date: 'il y a 1 semaine', rating: 5, text: 'La recherche de boutiques est fluide, les informations sont lisibles et le parcours inspire confiance.', verified: true },
    { initial: 'S', name: 'Sofia K.', role: 'Admin marketplace', date: 'il y a 3 jours', rating: 4, text: 'Interface moderne, rapide à comprendre. Les équipes gagnent du temps sur la gestion quotidienne.', verified: true },
    { initial: 'A', name: 'Amine R.', role: 'Commerçant', date: 'il y a 5 jours', rating: 5, text: 'La plateforme donne une image premium aux boutiques sans compliquer la mise en ligne des produits.', verified: false },
    { initial: 'N', name: 'Nadia L.', role: 'Responsable opérations', date: 'il y a 1 semaine', rating: 5, text: 'Les modules commandes, stocks et livraison sont bien centralisés. C’est exactement ce qu’il nous fallait.', verified: true },
    { initial: 'T', name: 'Tarik M.', role: 'Client professionnel', date: 'il y a 2 semaines', rating: 4, text: 'Expérience propre, rassurante et rapide. On comprend tout de suite où cliquer.', verified: true },
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
          <Link className="lovable-brand" to="/">
            <span className="material-symbols-outlined" aria-hidden="true">storefront</span>
            <span>Hanooty</span>
          </Link>
          <nav className="lovable-nav" aria-label="Navigation principale">
            <a href="#boutiques">Boutiques</a>
            <a href="#nouveautes">Nouveautés</a>
            <Link to="/avis">Avis</Link>
            <a href="#fonctionnement">Comment ça marche</a>
          </nav>
          <div className="lovable-header__actions">
            {canAccessBackOffice && (
              <button className="lovable-button lovable-button--sm" type="button" onClick={() => { navigate('/admin'); }}>Back office</button>
            )}
            {!canAccessBackOffice && isAuthenticated && (
              <button className="lovable-button lovable-button--secondary lovable-button--sm" type="button" onClick={() => { void signOut(); }}>Déconnexion</button>
            )}
            {!canAccessBackOffice && !isAuthenticated && (
              <button className="lovable-button lovable-button--sm" type="button" onClick={() => { navigate('/auth/login'); }}>Connexion</button>
            )}
          </div>
        </div>
      </header>

      <section className="lovable-hero">
        <div className="lovable-container lovable-hero__inner">
          <span className="lovable-pill">Solution Professionnelle</span>
          <h1>La Marketplace B2B pour les Indépendants</h1>
          <p>Propulsez votre activité commerciale avec une plateforme dédiée. Hanooty simplifie la gestion de votre boutique, de l&apos;inventaire à la vente sécurisée.</p>
          <div className="lovable-hero__actions">
            <button className="lovable-button" type="button" onClick={() => { navigate('/boutiques'); }}>Explorer les Boutiques <span className="material-symbols-outlined">arrow_forward</span></button>
            <button className="lovable-button lovable-button--secondary" type="button" onClick={() => { navigate('/admin'); }}>Créer ma Boutique</button>
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
                <div className="lovable-rating"><StarRating rating={boutique.rating} /><span>{boutique.reviews}</span></div>
                <div className="lovable-badges"><span><span className="material-symbols-outlined">verified</span> Vérifié</span></div>
                <button type="button" onClick={() => { navigate(boutique.href); }}>Voir la boutique <span className="material-symbols-outlined">arrow_forward</span></button>
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
          <SectionHeader eyebrow="reviews" eyebrowText="Témoignages" title="Avis Hanooty" text="Découvrez ce que les utilisateurs disent de l’application Hanooty." />
          <div className="lovable-review-grid">
            {reviews.map((review) => (
              <article className="lovable-card lovable-review-card" key={`${review.name}-${review.role}`}>
                <div className="lovable-review-card__header">
                  <div className="lovable-review-card__author"><span className="lovable-avatar">{review.initial}</span><div><strong>{review.name}</strong><small>{review.role}</small></div></div>
                  <time>{review.date}</time>
                </div>
                <StarRating rating={review.rating} />
                <p>{review.text}</p>
                {review.verified && <span className="lovable-verified"><span className="material-symbols-outlined">verified</span> Achat vérifié</span>}
              </article>
            ))}
          </div>
          <button className="lovable-button lovable-button--secondary lovable-centered-button" type="button" onClick={() => { navigate('/avis'); }}>Voir tous les avis <span className="material-symbols-outlined">arrow_forward</span></button>
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
              <button type="submit">Recevoir les conseils</button>
            </form>
          </div>
        </div>
      </section>

      <footer className="lovable-footer">
        <div className="lovable-container">
          <div className="lovable-footer__top">
            <div className="lovable-footer__brand"><Link className="lovable-brand" to="/"><span className="material-symbols-outlined">storefront</span><span>Hanooty</span></Link><p>La plateforme de référence pour les commerçants indépendants cherchant excellence et efficacité.</p><div className="lovable-socials"><a href="#">facebook</a><a href="#">alternate_email</a><a href="#">linkedin</a></div></div>
            <FooterColumn title="Explorer" links={['Boutiques', 'Vendeurs', 'Catégories']} />
            <FooterColumn title="Société" links={['À propos', 'Contact', 'Blog']} />
            <FooterColumn title="Légal" links={['CGV', 'Confidentialité', 'Cookies']} />
          </div>
          <p className="lovable-footer__bottom">© 2026 Market Shop - Hanooty. Tous droits réservés.</p>
        </div>
      </footer>
      {localStorage.getItem('hanooty_global_chat_enabled') !== 'false' && (
        <FloatingInfoChat title="Assistant Hanooty" welcomeMessage="Bonjour, je suis l’assistant général Hanooty. Comment puis-je vous aider ?" />
      )}
    </main>
  );
}

function StarRating({ rating }: { rating: number }) {
  return (
    <span className="lovable-stars" aria-label={`${rating} étoiles sur 5`}>
      {Array.from({ length: 5 }, (_, index) => (
        <span className="material-symbols-outlined" aria-hidden="true" key={index}>{index < rating ? 'star' : 'star_outline'}</span>
      ))}
    </span>
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
      {align === 'split' && <Link to="/boutiques">Tout voir <span className="material-symbols-outlined">arrow_forward</span></Link>}
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
