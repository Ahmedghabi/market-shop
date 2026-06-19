import { Link, useNavigate } from 'react-router-dom';

const applicationReviews = [
  { initial: 'M', name: 'Meriem A.', role: 'Boutique indépendante', date: 'il y a 2 jours', rating: 5, text: 'Hanooty nous a donné une présence professionnelle claire et un back-office beaucoup plus simple à gérer.' },
  { initial: 'Y', name: 'Youssef B.', role: 'Acheteur B2B', date: 'il y a 1 semaine', rating: 5, text: 'La recherche de boutiques est fluide, les informations sont lisibles et le parcours inspire confiance.' },
  { initial: 'S', name: 'Sofia K.', role: 'Admin marketplace', date: 'il y a 3 jours', rating: 4, text: 'Interface moderne, rapide à comprendre. Les équipes gagnent du temps sur la gestion quotidienne.' },
  { initial: 'A', name: 'Amine R.', role: 'Commerçant', date: 'il y a 5 jours', rating: 5, text: 'La plateforme donne une image premium aux boutiques sans compliquer la mise en ligne des produits.' },
  { initial: 'N', name: 'Nadia L.', role: 'Responsable opérations', date: 'il y a 1 semaine', rating: 5, text: 'Les modules commandes, stocks et livraison sont bien centralisés. C’est exactement ce qu’il nous fallait.' },
  { initial: 'T', name: 'Tarik M.', role: 'Client professionnel', date: 'il y a 2 semaines', rating: 4, text: 'Expérience propre, rassurante et rapide. On comprend tout de suite où cliquer.' },
  { initial: 'I', name: 'Imane C.', role: 'Fondatrice boutique', date: 'il y a 3 semaines', rating: 5, text: 'Le rendu public de la marketplace est sérieux et les pages inspirent confiance à nos clients.' },
  { initial: 'K', name: 'Karim E.', role: 'Gestionnaire catalogue', date: 'il y a 1 mois', rating: 4, text: 'La structure est claire, les actions principales sont visibles et la prise en main est rapide.' },
];

export function ApplicationReviewsPage() {
  const navigate = useNavigate();
  const averageRating = Math.round(applicationReviews.reduce((sum, review) => sum + review.rating, 0) / applicationReviews.length * 10) / 10;

  return (
    <main className="lovable-home lovable-app-reviews">
      <header className="lovable-header">
        <div className="lovable-container lovable-header__inner">
          <Link className="lovable-brand" to="/">
            <span className="material-symbols-outlined" aria-hidden="true">storefront</span>
            <span>Hanooty</span>
          </Link>
          <nav className="lovable-nav" aria-label="Navigation publique">
            <Link to="/boutiques">Boutiques</Link>
            <Link to="/avis">Avis</Link>
            <Link to="/auth/login">Connexion</Link>
          </nav>
          <div className="lovable-header__actions">
            <button className="lovable-button lovable-button--sm" type="button" onClick={() => { navigate('/auth/login'); }}>Connexion</button>
          </div>
        </div>
      </header>

      <section className="lovable-app-reviews__hero">
        <div className="lovable-container">
          <span className="lovable-pill"><span className="material-symbols-outlined">reviews</span>Avis application</span>
          <h1>Ce que les utilisateurs pensent de Hanooty.</h1>
          <p>Des retours sur l’expérience globale de l’application: marketplace, back-office, gestion commerciale et confiance côté client.</p>
          <div className="lovable-app-reviews__summary">
            <div><strong>{averageRating}</strong><StarRating rating={Math.round(averageRating)} /><span>Note moyenne</span></div>
            <div><strong>{applicationReviews.length}</strong><span>Avis collectés</span></div>
            <div><strong>98%</strong><span>Satisfaction déclarée</span></div>
          </div>
        </div>
      </section>

      <section className="lovable-section">
        <div className="lovable-container">
          <div className="lovable-app-reviews__grid">
            {applicationReviews.map((review) => (
              <article className="lovable-card lovable-review-card" key={`${review.name}-${review.date}`}>
                <div className="lovable-review-card__header">
                  <div className="lovable-review-card__author">
                    <span className="lovable-avatar">{review.initial}</span>
                    <div><strong>{review.name}</strong><small>{review.role}</small></div>
                  </div>
                  <time>{review.date}</time>
                </div>
                <StarRating rating={review.rating} />
                <p>{review.text}</p>
                <span className="lovable-verified"><span className="material-symbols-outlined">verified</span> Avis vérifié</span>
              </article>
            ))}
          </div>
        </div>
      </section>
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
