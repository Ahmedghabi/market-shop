import { MarketplacePage } from '../../screens/public/MarketplacePage';

type PublicBoutique = {
  name: string;
  category: string;
  city: string;
  image: string;
  href: string;
  accent: string;
  slug: string;
};

export function MarketplaceRoutePage({ title, description, boutiques }: { title: string; description: string; boutiques: PublicBoutique[] }) {
  return <MarketplacePage title={title} description={description} boutiques={boutiques} />;
}
