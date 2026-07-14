import { PublicHeader } from '../../components/PublicHeader';
import { ReviewSection } from '../../components/ReviewSection';
import { formatReviewDate, reviewInitial, usePlatformReviews } from './platformReviews';

export function ApplicationReviewsPage() {
  const { reviews, isLoading } = usePlatformReviews();
  const averageRating = reviews.length > 0
    ? Math.round(reviews.reduce((sum, review) => sum + review.rating, 0) / reviews.length * 10) / 10
    : 0;

  return (
    <main className="lovable-home lovable-app-reviews">
      <PublicHeader />

      <section className="lovable-app-reviews__hero">
        <div className="lovable-container">
          <span className="lovable-pill"><span className="material-symbols-outlined">reviews</span>Avis application</span>
          <h1>Ce que les utilisateurs pensent de Hanooti.</h1>
          <p>Des retours sur l’expérience globale de l’application: marketplace, back-office, gestion commerciale et confiance côté client.</p>
          <div className="lovable-app-reviews__summary">
            <div><strong>{averageRating}</strong><StarRating rating={Math.round(averageRating)} /><span>Note moyenne</span></div>
             <div><strong>{isLoading ? '—' : reviews.length}</strong><span>Avis approuvés</span></div>
             <div><strong>{isLoading || reviews.length === 0 ? '—' : `${Math.round(averageRating / 5 * 100)}%`}</strong><span>Satisfaction calculée</span></div>
          </div>
        </div>
      </section>

      <section className="lovable-section">
        <div className="lovable-container">
          <div className="lovable-app-reviews__grid">
             {isLoading ? <div className="lovable-card lovable-review-card"><p>Chargement des avis...</p></div> : reviews.length > 0 ? reviews.map((review) => (
               <article className="lovable-card lovable-review-card" key={review.id}>
                 <div className="lovable-review-card__header">
                   <div className="lovable-review-card__author">
                     <span className="lovable-avatar">{reviewInitial(review.authorName)}</span>
                     <div><strong>{review.authorName}</strong><small>Utilisateur Hanooti</small></div>
                   </div>
                   <time dateTime={review.createdAt}>{formatReviewDate(review.createdAt)}</time>
                 </div>
                 <StarRating rating={review.rating} />
                 <p>{review.comment ?? 'Avis noté par un utilisateur Hanooti.'}</p>
                 {review.isVerifiedPurchase && <span className="lovable-verified"><span className="material-symbols-outlined">verified</span> Achat vérifié</span>}
               </article>
             )) : <div className="lovable-card lovable-review-card"><h3>Aucun avis publié</h3><p>Les premiers retours apparaîtront ici après validation.</p></div>}
          </div>
        </div>
      </section>
      <section className="lovable-section">
        <div className="lovable-container lovable-app-reviews__form">
          <ReviewSection boutiqueSlug="" scope="platform" />
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
