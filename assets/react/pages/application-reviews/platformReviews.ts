import { useEffect, useState } from 'react';

export type PlatformReview = {
  id: string;
  authorName: string;
  rating: number;
  comment: string | null;
  isVerifiedPurchase: boolean;
  createdAt: string;
};

type CollectionPayload<T> = T[] | {
  member?: T[];
  items?: T[];
  'hydra:member'?: T[];
};

function unwrapCollection<T>(payload: CollectionPayload<T>): T[] {
  if (Array.isArray(payload)) return payload;

  return payload.member ?? payload.items ?? payload['hydra:member'] ?? [];
}

export function usePlatformReviews() {
  const [reviews, setReviews] = useState<PlatformReview[]>([]);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    const controller = new AbortController();

    fetch('/api/platform/reviews', { signal: controller.signal })
      .then(async (response) => {
        if (!response.ok) throw new Error('Impossible de charger les avis.');

        return unwrapCollection(await response.json() as CollectionPayload<PlatformReview>);
      })
      .then((items) => setReviews(items.filter((review) => review.comment || review.rating > 0)))
      .catch((error: unknown) => {
        if (!(error instanceof DOMException && error.name === 'AbortError')) setReviews([]);
      })
      .finally(() => {
        if (!controller.signal.aborted) setIsLoading(false);
      });

    return () => controller.abort();
  }, []);

  return { reviews, isLoading };
}

export function formatReviewDate(value: string): string {
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return '';

  return date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' });
}

export function reviewInitial(name: string): string {
  return name.trim().charAt(0).toUpperCase() || '?';
}
