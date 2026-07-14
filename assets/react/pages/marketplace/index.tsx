import { MarketplacePage } from '../../screens/public/MarketplacePage';

type PublicBoutique = {
  name: string;
  slug: string;
  category?: string | null;
  city?: string | null;
  image?: string | null;
  href?: string;
  accent?: string | null;
  status?: string;
  logoUrl?: string | null;
  customDomain?: string | null;
  isPublished?: boolean;
  isVisiblePublicly?: boolean;
};

export function MarketplaceRoutePage({ title, description, boutiques }: { title: string; description: string; boutiques: PublicBoutique[] }) {
  return <MarketplacePage title={title} description={description} boutiques={boutiques} />;
}
