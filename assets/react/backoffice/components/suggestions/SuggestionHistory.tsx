import { suggestionStatusLabel, type SuggestionHistoryItem } from './SuggestionTypes';

export function SuggestionHistory({ history = [] }: { history?: SuggestionHistoryItem[] }) {
  if (history.length === 0) {
    return <p className="suggestion-muted">Aucun changement de statut enregistré.</p>;
  }

  return (
    <ol className="suggestion-history" aria-label="Historique des statuts">
      {history.map((item) => (
        <li key={item.id}>
          <div>
            <strong>{suggestionStatusLabel(item.newStatus)}</strong>
            {item.oldStatus && <span> depuis {suggestionStatusLabel(item.oldStatus)}</span>}
          </div>
          <time dateTime={item.createdAt}>{new Date(item.createdAt).toLocaleString('fr-FR')}</time>
          {item.changedBy && <small>par {item.changedBy}</small>}
          {item.comment && <p>{item.comment}</p>}
        </li>
      ))}
    </ol>
  );
}
