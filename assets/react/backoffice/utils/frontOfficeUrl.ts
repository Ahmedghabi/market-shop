type FrontBoutique = {
  slug: string;
  status?: string;
  isPublished?: boolean;
  customDomain?: string | null;
};

export function frontOfficeUrl(boutique: FrontBoutique): string {
  if ((boutique.status && boutique.status !== 'active') || boutique.isPublished === false) {
    return `/boutiques/${boutique.slug}`;
  }

  if (boutique.customDomain) {
    return `https://${boutique.customDomain.replace(/^https?:\/\//, '').replace(/\/+$/, '')}`;
  }

  const { protocol, hostname, port } = window.location;
  if (/^\d+\.\d+\.\d+\.\d+$/.test(hostname)) {
    return `/boutiques/${boutique.slug}`;
  }

  const labels = hostname.split('.');
  const baseDomain = hostname === 'localhost' || hostname.endsWith('.localhost')
    ? 'localhost'
    : labels.length > 2 ? labels.slice(1).join('.') : hostname;
  const portSuffix = port ? `:${port}` : '';

  return `${protocol}//${boutique.slug}.${baseDomain}${portSuffix}`;
}
