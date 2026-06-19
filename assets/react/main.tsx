import { createRoot } from 'react-dom/client';
import '../bootstrap';
import '../styles/admin.css';
import '../styles/landing.css';
import '../styles/stitch-design-system.css';
import '../styles/backoffice.css';
import '../styles/shop.css';
import { App } from './App';
import { AuthProvider } from './auth/AuthProvider';
import './icons/fontAwesome';

const rootElement = document.getElementById('react-root');

if (rootElement) {
  createRoot(rootElement).render(
    <AuthProvider>
      <App />
    </AuthProvider>,
  );
}
