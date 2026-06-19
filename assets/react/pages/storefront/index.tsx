import { StorefrontPage } from '../../screens/public/StorefrontPage';

export function StorefrontRoutePage({ title, description }: { title: string; description: string }) {
  return <StorefrontPage title={title} description={description} />;
}
