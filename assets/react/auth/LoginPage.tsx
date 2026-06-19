import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { useState, type FormEvent } from 'react';
import { Link } from 'react-router-dom';
import type { RegisterPayload } from './AuthProvider';
import { appIcons } from '../icons/fontAwesome';

type LoginPageProps = {
  onSignIn: (email: string, password: string) => Promise<void>;
  onSignUp: (payload: RegisterPayload) => Promise<void>;
  initialMode?: 'login' | 'register';
};

export function LoginPage({ onSignIn, onSignUp, initialMode = 'login' }: LoginPageProps) {
  const [mode, setMode] = useState<'login' | 'register'>(initialMode);
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [displayName, setDisplayName] = useState('');
  const [boutiqueName, setBoutiqueName] = useState('');
  const [boutiqueSlug, setBoutiqueSlug] = useState('');
  const [rememberMe, setRememberMe] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);

  async function submit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setError(null);
    setIsSubmitting(true);

    try {
      if ('login' === mode) {
        await onSignIn(email, password);
      } else {
        await onSignUp({ email, password, displayName, boutiqueName, boutiqueSlug });
      }
    } catch (exception) {
      setError(exception instanceof Error ? exception.message : 'Action impossible.');
    } finally {
      setIsSubmitting(false);
    }
  }

  const isRegister = mode === 'register';

  return (
    <main className="lovable-auth">
      <div className="lovable-auth__shell">
        <section className="lovable-auth__panel" aria-label="Présentation Hanooty">
          <Link className="lovable-brand" to="/">
            <span className="material-symbols-outlined" aria-hidden="true">storefront</span>
            <span>Hanooty</span>
          </Link>
          <span className="lovable-pill">Marketplace B2B</span>
          <h1>{isRegister ? 'Lancez votre boutique professionnelle en quelques minutes.' : 'Reconnectez-vous à votre espace boutique.'}</h1>
          <p>Gérez vos produits, vos commandes, vos transporteurs et votre présence marketplace depuis une interface pensée pour les indépendants.</p>
          <div className="lovable-auth__proof-grid">
            <div><strong>2,500+</strong><span>Boutiques actives</span></div>
            <div><strong>98%</strong><span>Satisfaction client</span></div>
          </div>
          <div className="lovable-auth__feature-card">
            <span className="lovable-auth__icon"><FontAwesomeIcon icon={appIcons.dashboard} /></span>
            <div>
              <strong>Console complète</strong>
              <span>Inventaire, commandes, livraison et statistiques en temps réel.</span>
            </div>
          </div>
        </section>

        <section className="lovable-auth__form-panel" aria-label={isRegister ? 'Inscription' : 'Connexion'}>
          <div className="lovable-auth__form-card">
            <header>
              <Link className="lovable-auth__mobile-brand" to="/">
                <span className="material-symbols-outlined" aria-hidden="true">storefront</span>
                <span>Hanooty</span>
              </Link>
              <p className="lovable-auth__eyebrow">{isRegister ? 'Inscription boutique' : 'Connexion sécurisée'}</p>
              <h2>{isRegister ? 'Créer votre compte' : 'Bienvenue'}</h2>
              <p>{isRegister ? 'Renseignez les informations de base pour demander votre espace boutique.' : 'Connectez-vous pour accéder au back-office Hanooty.'}</p>
            </header>

            <div className="lovable-auth__tabs" role="tablist" aria-label="Mode authentification">
              <button type="button" className={!isRegister ? 'is-active' : ''} onClick={() => setMode('login')}>Connexion</button>
              <button type="button" className={isRegister ? 'is-active' : ''} onClick={() => setMode('register')}>Inscription</button>
            </div>

            <form className="lovable-auth__form" onSubmit={submit}>
              <label>
                <span>Email professionnel</span>
                <div className="lovable-auth__field">
                    <input
                      id="email"
                      type="email"
                      value={email}
                      onChange={(event) => setEmail(event.target.value)}
                      required
                      placeholder="vous@entreprise.com"
                    />
                    <FontAwesomeIcon icon={appIcons.mail} />
                </div>
              </label>

              <label>
                <span>Mot de passe</span>
                <div className="lovable-auth__field lovable-auth__field--password">
                  <FontAwesomeIcon icon={appIcons.lock} />
                    <input
                      id="password"
                      type={showPassword ? 'text' : 'password'}
                      value={password}
                      onChange={(event) => setPassword(event.target.value)}
                      required
                      placeholder="••••••••"
                    />
                    <button
                      type="button"
                      onClick={() => setShowPassword((current) => !current)}
                      aria-label={showPassword ? 'Masquer le mot de passe' : 'Afficher le mot de passe'}
                    >
                      <FontAwesomeIcon icon={showPassword ? appIcons.eyeSlash : appIcons.eye} />
                    </button>
                </div>
              </label>

              {isRegister && (
                <div className="lovable-auth__register-fields">
                  <label>
                    <span>Nom complet</span>
                    <input value={displayName} onChange={(event) => setDisplayName(event.target.value)} placeholder="Admin Boutique" />
                  </label>
                  <label>
                    <span>Nom de la boutique</span>
                    <input value={boutiqueName} onChange={(event) => setBoutiqueName(event.target.value)} required placeholder="Luxe Paris" />
                  </label>
                  <label>
                    <span>Slug boutique</span>
                    <input value={boutiqueSlug} onChange={(event) => setBoutiqueSlug(event.target.value)} required pattern="[a-z0-9-]+" placeholder="luxe-paris" />
                  </label>
                  </div>
                )}

              <div className="lovable-auth__meta-row">
                <label className="lovable-auth__checkbox">
                  <input type="checkbox" checked={rememberMe} onChange={(event) => setRememberMe(event.target.checked)} />
                  <span>Se souvenir de moi</span>
                </label>
                <a href="#">Mot de passe oublié ?</a>
              </div>

              {error && <div className="lovable-auth__error">{error}</div>}

              <button type="submit" disabled={isSubmitting} className="lovable-button lovable-auth__submit">
                <FontAwesomeIcon icon={isRegister ? appIcons.plus : appIcons.login} />
                {isSubmitting ? 'Traitement...' : isRegister ? 'Créer mon espace boutique' : 'Se connecter'}
              </button>
            </form>

            <footer className="lovable-auth__footer">
              <button type="button" onClick={() => setMode(isRegister ? 'login' : 'register')}>
                {isRegister ? 'J’ai déjà un compte' : 'Créer une boutique'}
              </button>
              <div>
                <a href="#">Aide</a>
                <a href="#">Confidentialité</a>
              </div>
            </footer>
          </div>
        </section>
      </div>
    </main>
  );
}
