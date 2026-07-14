import { Link } from 'react-router-dom';
import { BrandLogo } from './BrandLogo';

type PublicHeaderProps = {
  canAccessBackOffice?: boolean;
  isAuthenticated?: boolean;
  onSignOut?: () => void | Promise<void>;
};

export function PublicHeader({ canAccessBackOffice = false, isAuthenticated = false, onSignOut }: PublicHeaderProps) {
  return (
    <header className="lovable-header">
      <div className="lovable-container lovable-header__inner">
        <Link className="lovable-brand" to="/">
          <BrandLogo />
        </Link>
        <nav className="lovable-nav" aria-label="Navigation publique">
          <a href="/#Boutiques">Boutiques</a>
          <a href="/#nouveautes">Nouveautés</a>
          <a href="/#avis">Avis</a>
          <Link to="/suggestions">Suggestions</Link>
          <a href="/#fonctionnement">Comment ça marche</a>
        </nav>
        <div className="lovable-header__actions">
          {canAccessBackOffice ? (
            <Link className="lovable-button lovable-button--sm" to="/admin">Back office</Link>
          ) : isAuthenticated && onSignOut ? (
            <button className="lovable-button lovable-button--secondary lovable-button--sm" type="button" onClick={() => { void onSignOut(); }}>Déconnexion</button>
          ) : (
            <>
              <Link className="lovable-link-button" to="/auth/login">Connexion</Link>
            </>
          )}
        </div>
      </div>
    </header>
  );
}
