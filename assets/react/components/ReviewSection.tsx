import { useEffect, useState } from 'react';
import { Badge, Button, Card, Textarea } from './ui';

type ReviewOutput = {
  id: string;
  productId?: string | null;
  authorName: string;
  rating: number;
  comment: string | null;
  createdAt: string;
};

type ReviewSectionProps = {
  boutiqueSlug: string;
  productId?: string;
};

export function ReviewSection({ boutiqueSlug: _boutiqueSlug, productId }: ReviewSectionProps) {
  const [reviews, setReviews] = useState<ReviewOutput[]>([]);
  const [authorName, setAuthorName] = useState('');
  const [rating, setRating] = useState(5);
  const [comment, setComment] = useState('');
  const [submitted, setSubmitted] = useState(false);
  const [error, setError] = useState('');

  const endpoint = '/api/reviews';

  useEffect(() => {
    fetch(endpoint)
      .then((response) => response.ok ? response.json() : [])
      .then((data) => {
        const items = (data as { member?: ReviewOutput[] }).member ?? data;
        const allReviews = Array.isArray(items) ? items : [];
        setReviews(productId ? allReviews.filter((review) => review.productId === productId) : allReviews);
      })
      .catch(() => {});
  }, [endpoint, productId]);

  async function handleSubmit(event: React.FormEvent) {
    event.preventDefault();
    setError('');

    try {
      const response = await fetch(endpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ authorName, rating, comment: comment || null }),
      });

      if (!response.ok) {
        setError('Erreur lors de l\'envoi. Veuillez réessayer.');
        return;
      }

      setSubmitted(true);
      setAuthorName('');
      setRating(5);
      setComment('');

      const newReview = await response.json();
      setReviews((prev) => [newReview, ...prev]);
    } catch {
      setError('Erreur réseau. Veuillez réessayer.');
    }
  }

  const avgRating = reviews.length > 0
    ? Math.round(reviews.reduce((s, r) => s + r.rating, 0) / reviews.length * 10) / 10
    : 0;

  return (
    <div>
      <div className="mb-6 flex items-end justify-between gap-4">
        <div>
          <h3 className="text-xl font-bold">Avis clients</h3>
          {reviews.length > 0 && (
            <p className="mt-1 text-sm text-[color:var(--ds-on-surface-variant)]">
              {avgRating}/5 - {reviews.length} avis
            </p>
          )}
        </div>
        <Badge tone="neutral">{reviews.length} avis</Badge>
      </div>

      {reviews.length === 0 && (
        <Card className="mb-6 text-center py-8">
          <p className="text-[color:var(--ds-on-surface-variant)]">Aucun avis pour le moment. Soyez le premier !</p>
        </Card>
      )}

      <div className="mb-8 grid gap-4">
        {reviews.map((review) => (
          <Card key={review.id} className="bg-[color:var(--ds-surface-container-lowest)]">
            <div className="flex items-start justify-between gap-3">
              <div>
                <strong>{review.authorName}</strong>
                <div className="mt-1 text-sm text-[color:var(--ds-primary)]">{'★'.repeat(review.rating)}{'☆'.repeat(5 - review.rating)}</div>
              </div>
              <span className="text-xs text-[color:var(--ds-on-surface-variant)]">
                {new Date(review.createdAt).toLocaleDateString('fr-FR')}
              </span>
            </div>
            {review.comment && <p className="mt-3 text-sm text-[color:var(--ds-on-surface-variant)]">{review.comment}</p>}
          </Card>
        ))}
      </div>

      {submitted ? (
        <Card className="bg-[color:var(--ds-success)]/10 border border-[color:var(--ds-success)]/20">
          <p className="text-sm font-semibold text-[color:var(--ds-success)]">Merci ! Votre avis a été soumis et sera visible après modération.</p>
        </Card>
      ) : (
        <Card>
          <h4 className="mb-4 font-bold">Laisser un avis</h4>
          <form onSubmit={handleSubmit} className="space-y-4">
            <div>
              <label className="mb-1 block text-sm font-semibold text-[color:var(--ds-on-surface-variant)]">Votre nom</label>
              <input
                className="ds-input w-full"
                value={authorName}
                onChange={(e) => setAuthorName(e.target.value)}
                placeholder="Jean Dupont"
                required
              />
            </div>
            <div>
              <label className="mb-1 block text-sm font-semibold text-[color:var(--ds-on-surface-variant)]">Note</label>
              <div className="flex gap-2">
                {[1, 2, 3, 4, 5].map((star) => (
                  <button
                    key={star}
                    type="button"
                    onClick={() => setRating(star)}
                    className={`h-10 w-10 rounded-lg border text-lg transition ${
                      star <= rating
                        ? 'border-[color:var(--ds-primary)] bg-[color:var(--ds-primary)] text-white'
                        : 'border-[color:var(--ds-outline-variant)] text-[color:var(--ds-on-surface-variant)]'
                    }`}
                  >
                    ★
                  </button>
                ))}
              </div>
            </div>
            <div>
              <label className="mb-1 block text-sm font-semibold text-[color:var(--ds-on-surface-variant)]">Commentaire (optionnel)</label>
              <Textarea
                value={comment}
                onChange={(e) => setComment(e.target.value)}
                placeholder="Partagez votre expérience..."
                rows={3}
              />
            </div>
            {error && <p className="text-sm text-[color:var(--ds-error)]">{error}</p>}
            <Button variant="primary" type="submit">Publier mon avis</Button>
          </form>
        </Card>
      )}
    </div>
  );
}
