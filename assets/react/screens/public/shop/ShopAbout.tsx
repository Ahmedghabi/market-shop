import { ShopLayout } from './ShopLayout';

function QuoteIcon() {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1" strokeLinecap="round" strokeLinejoin="round">
      <path d="M3 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2H4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V20c0 1 0 1 1 1z"/>
      <path d="M15 21c3 0 7-1 7-8V5c0-1.25-.757-2.017-2-2h-4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2h.75c0 2.25.25 4-2.75 4v3c0 1 0 1 1 1z"/>
    </svg>
  );
}

function TargetIcon() {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/>
    </svg>
  );
}

function EyeIcon() {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/>
    </svg>
  );
}

function RocketIcon() {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"/>
      <path d="M12 15l-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/>
      <path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"/>
      <path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/>
    </svg>
  );
}

function MailIcon() {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 7l-10 7L2 7"/>
    </svg>
  );
}

function PhoneIcon() {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
    </svg>
  );
}

function MapPinIcon() {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
    </svg>
  );
}

const team = [
  { initial: 'AK', name: 'Amir Khelil', role: 'CEO & Fondateur', bio: 'Expert en e-commerce avec 15 ans d\'expérience dans le retail digital.' },
  { initial: 'SB', name: 'Sarra Ben Ali', role: 'CTO', bio: 'Architecte logiciel passionnée par les solutions SaaS scalables.' },
  { initial: 'MR', name: 'Mehdi Riahi', role: 'Head of Product', bio: 'Transforme les besoins clients en expériences produit exceptionnelles.' },
  { initial: 'LB', name: 'Leila Bouaziz', role: 'Marketing Director', bio: 'Stratège marketing digital spécialisée dans la growth B2B.' },
];

const values = [
  { title: 'Notre Mission', text: 'Offrir aux commerçants indépendants les mêmes outils technologiques que les grandes enseignes, pour leur permettre de se concentrer sur ce qui compte vraiment : leurs produits et leurs clients.', icon: 'target' },
  { title: 'Notre Vision', text: 'Devenir la plateforme de référence pour le commerce indépendant en France et en Afrique du Nord, en créant un écosystème où chaque boutique peut prospérer en ligne.', icon: 'eye' },
  { title: 'Notre Engagement', text: 'La satisfaction de nos marchands est notre priorité absolue. Nous nous engageons à fournir un support réactif, des fonctionnalités innovantes et une plateforme fiable.', icon: 'rocket' },
];

export function ShopAbout() {
  return (
    <ShopLayout activePath="/shop/a-propos">
      {/* Hero */}
      <section className="shop-about-hero">
        <div className="shop-about-hero__inner">
          <h1>À propos de Hanooty</h1>
          <p>Nous construisons l&apos;avenir du commerce indépendant avec des outils puissants, une communauté engageante et une plateforme digne des plus grands.</p>
        </div>
      </section>

      {/* Story */}
      <section className="shop-section shop-section--alt">
        <div className="shop-section__inner">
          <div className="shop-about-story">
            <div className="shop-about-story__text">
              <h2>Notre Histoire</h2>
              <p>
                Hanooty est née d&apos;une observation simple : les commerçants indépendants sont l&apos;épine dorsale
                de notre économie, mais ils manquent cruellement d&apos;outils digitaux à leur portée.
              </p>
              <p>
                Fondée en 2024, notre équipe a entrepris de créer une plateforme SaaS complète qui démocratise
                l&apos;accès aux technologies e-commerce. De la gestion de stock au paiement en ligne, en passant
                par le marketing et la fidélisation, Hanooty offre tout ce dont un commerçant a besoin pour
                vendre en ligne, sans complexité ni investissement démesuré.
              </p>
              <p>
                Aujourd&apos;hui, plus de 2 500 boutiques nous font confiance pour propulser leur activité.
              </p>
            </div>
            <div className="shop-about-story__image">
              <QuoteIcon />
            </div>
          </div>
        </div>
      </section>

      {/* Values */}
      <section className="shop-section shop-section--muted">
        <div className="shop-section__inner">
          <div className="shop-section__header">
            <h2>Nos Valeurs</h2>
          </div>
          <div className="shop-bento">
            {values.map((val) => (
              <article className="shop-bento-card shop-bento-card--wide" key={val.title}>
                <div className="shop-bento-card__icon">
                  {val.icon === 'target' ? <TargetIcon /> : val.icon === 'eye' ? <EyeIcon /> : <RocketIcon />}
                </div>
                <h3>{val.title}</h3>
                <p>{val.text}</p>
              </article>
            ))}
          </div>
        </div>
      </section>

      {/* Team */}
      <section className="shop-section shop-section--alt">
        <div className="shop-section__inner">
          <div className="shop-section__header">
            <h2>Notre Équipe</h2>
            <p>Des passionnés de technologie et de commerce, unis par une mission commune.</p>
          </div>
          <div className="shop-team">
            {team.map((member) => (
              <article className="shop-team-card" key={member.name}>
                <div className="shop-team-card__avatar">{member.initial}</div>
                <h3>{member.name}</h3>
                <span>{member.role}</span>
                <p>{member.bio}</p>
              </article>
            ))}
          </div>
        </div>
      </section>

      {/* Contact */}
      <section className="shop-section shop-section--muted">
        <div className="shop-section__inner">
          <div className="shop-section__header">
            <h2>Contactez-nous</h2>
            <p>Une question ? Une suggestion ? Nous sommes là pour vous.</p>
          </div>
          <div className="shop-contact">
            <div className="shop-contact__info">
              <p>N&apos;hésitez pas à nous contacter. Notre équipe vous répondra dans les plus brefs délais.</p>
              <div className="shop-contact__details">
                <div className="shop-contact__detail">
                  <MailIcon />
                  <span>contact@hanooty.com</span>
                </div>
                <div className="shop-contact__detail">
                  <PhoneIcon />
                  <span>+216 70 000 000</span>
                </div>
                <div className="shop-contact__detail">
                  <MapPinIcon />
                  <span>Tunis, Tunisie</span>
                </div>
              </div>
            </div>
            <form className="shop-contact__form" onSubmit={(e) => e.preventDefault()}>
              <label htmlFor="about-name">Nom complet</label>
              <input id="about-name" type="text" placeholder="Votre nom" />
              <label htmlFor="about-email">Email</label>
              <input id="about-email" type="email" placeholder="vous@email.com" />
              <label htmlFor="about-message">Message</label>
              <textarea id="about-message" placeholder="Votre message..." />
              <button type="submit" className="shop-btn shop-btn--primary">Envoyer</button>
            </form>
          </div>
        </div>
      </section>
    </ShopLayout>
  );
}
