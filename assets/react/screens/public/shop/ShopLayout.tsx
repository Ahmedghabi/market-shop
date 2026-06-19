import { type ReactNode } from 'react';

function CartIcon() {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
      <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
    </svg>
  );
}

function StoreIcon() {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
      <line x1="3" y1="6" x2="21" y2="6"/>
      <path d="M16 10a4 4 0 0 1-8 0"/>
    </svg>
  );
}

function FacebookIcon() {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>
    </svg>
  );
}

function TwitterIcon() {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <path d="M22 4s-.7 2.1-2 3.4c1.6 10-9.4 17.3-18 11.6 2.2.1 4.4-.6 6-2C3 15.5.5 9.6 3 5c2.2 2.6 5.6 4.1 9 4-.9-4.2 4-6.6 7-3.8 1.1 0 3-1.2 3-1.2z"/>
    </svg>
  );
}

function LinkedInIcon() {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/>
      <rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/>
    </svg>
  );
}

function MailIcon() {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round">
      <rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 7l-10 7L2 7"/>
    </svg>
  );
}

const footerLinks = {
  shop: [
    { label: 'Toutes les boutiques', href: '/boutiques' },
    { label: 'Nouveautés', href: '/shop/catalogue' },
    { label: 'Promotions', href: '/shop/catalogue' },
    { label: 'Catégories', href: '/shop/catalogue' },
  ],
  company: [
    { label: 'À propos', href: '/shop/a-propos' },
    { label: 'Contact', href: '/shop/a-propos' },
    { label: 'Blog', href: '/shop/a-propos' },
    { label: 'Presse', href: '/shop/a-propos' },
  ],
  legal: [
    { label: 'CGV', href: '#' },
    { label: 'Confidentialité', href: '#' },
    { label: 'Cookies', href: '#' },
    { label: 'Mentions légales', href: '#' },
  ],
  help: [
    { label: 'FAQ', href: '/shop/a-propos' },
    { label: 'Livraison', href: '/shop/a-propos' },
    { label: 'Retours', href: '/shop/a-propos' },
    { label: 'Service client', href: '/shop/a-propos' },
  ],
};

const allNavLinks = [
  { label: 'Accueil', href: '/shop' },
  { label: 'Catalogue', href: '/shop/catalogue' },
  { label: 'À propos', href: '/shop/a-propos' },
];

export function ShopHeader({ activePath, cartCount = 0 }: { activePath: string; cartCount?: number }) {
  return (
    <header className="shop-header">
      <div className="shop-header__inner">
        <a href="/shop" className="shop-logo">
          <StoreIcon />
          <span>Hanooty</span>
        </a>
        <nav className="shop-nav" aria-label="Navigation boutique">
          {allNavLinks.map((link) => (
            <a
              key={link.href}
              href={link.href}
              className={activePath === link.href ? 'active' : ''}
            >
              {link.label}
            </a>
          ))}
        </nav>
        <div className="shop-header__actions">
          <a href="/admin" className="shop-btn shop-btn--ghost shop-btn--sm">Connexion</a>
          <a href="/admin" className="shop-btn shop-btn--primary shop-btn--sm">S&apos;inscrire</a>
          <button className="shop-cart-btn" type="button" onClick={() => { window.location.href = '/cart'; }}>
            <CartIcon />
            {cartCount > 0 && <span className="shop-cart-btn__count">{cartCount}</span>}
          </button>
        </div>
      </div>
    </header>
  );
}

export function ShopFooter() {
  return (
    <footer className="shop-footer">
      <div className="shop-footer__inner">
        <div className="shop-footer__grid">
          <div className="shop-footer__brand">
            <a href="/shop" className="shop-logo">
              <StoreIcon />
              <span>Hanooty</span>
            </a>
            <p>La plateforme de référence pour les commerçants indépendants cherchant excellence et efficacité.</p>
            <div className="shop-footer__social">
              <a href="#" aria-label="Facebook"><FacebookIcon /></a>
              <a href="#" aria-label="Twitter"><TwitterIcon /></a>
              <a href="#" aria-label="LinkedIn"><LinkedInIcon /></a>
            </div>
          </div>
          <FooterColumn title="Boutique" links={footerLinks.shop} />
          <FooterColumn title="Société" links={footerLinks.company} />
          <FooterColumn title="Légal" links={footerLinks.legal} />
          <FooterColumn title="Aide" links={footerLinks.help} />
        </div>
        <div className="shop-footer__bottom">
          &copy; 2026 Market Shop - Hanooty. Tous droits réservés.
        </div>
      </div>
    </footer>
  );
}

function FooterColumn({ title, links }: { title: string; links: Array<{ label: string; href: string }> }) {
  return (
    <div className="shop-footer__col">
      <h4>{title}</h4>
      {links.map((link) => (
        <a key={link.label} href={link.href}>{link.label}</a>
      ))}
    </div>
  );
}

export function ShopLayout({ children, activePath }: { children: ReactNode; activePath: string }) {
  return (
    <div className="shop">
      <ShopHeader activePath={activePath} />
      {children}
      <ShopFooter />
    </div>
  );
}

export { MailIcon, CartIcon, StoreIcon };
