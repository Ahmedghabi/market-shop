export function LoadingState({ message = 'Chargement...' }: { message?: string }) {
  return (
    <div className="bo-loading">
      <div className="bo-loading-spinner" />
      <span>{message}</span>
    </div>
  );
}

export function EmptyState({
  title = 'Aucune donnée',
  message = 'Aucun élément trouvé.',
  action,
}: {
  title?: string;
  message?: string;
  action?: { label: string; onClick: () => void };
}) {
  return (
    <div className="bo-empty">
      <svg className="bo-empty-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
        <path strokeLinecap="round" strokeLinejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m6 4.125l2.25 2.25m0 0l2.25 2.25M12 11.625l2.25-2.25M12 11.625l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
      </svg>
      <h3>{title}</h3>
      <p>{message}</p>
      {action && (
        <button className="bo-btn bo-btn-primary" onClick={action.onClick}>
          {action.label}
        </button>
      )}
    </div>
  );
}

export function ErrorState({
  message = 'Une erreur est survenue.',
  onRetry,
}: {
  message?: string;
  onRetry?: () => void;
}) {
  return (
    <div className="bo-error">
      <svg className="bo-error-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
        <path strokeLinecap="round" strokeLinejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
      </svg>
      <h3>Erreur</h3>
      <p>{message}</p>
      {onRetry && <button className="bo-btn bo-btn-secondary" onClick={onRetry}>Réessayer</button>}
    </div>
  );
}
