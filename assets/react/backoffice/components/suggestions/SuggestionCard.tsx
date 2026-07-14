import { Button } from '../Button';
import { Card } from '../Card';
import { SuggestionStatusBadge } from './SuggestionStatusBadge';
import type { Suggestion } from './SuggestionTypes';

export function SuggestionCard({
  suggestion,
  onOpen,
  publicView = false,
}: {
  suggestion: Suggestion;
  onOpen: (suggestion: Suggestion) => void;
  publicView?: boolean;
}) {
  return (
    <Card className="suggestion-card">
      <div className="suggestion-card-topline">
        <SuggestionStatusBadge status={suggestion.status} />
        {suggestion.isPublished && <span className="suggestion-published">Publié</span>}
      </div>
      <h3>{suggestion.title}</h3>
      <p className="suggestion-card-description">{suggestion.description}</p>
      <div className="suggestion-card-meta">
        {suggestion.categoryName && <span>{suggestion.categoryName}</span>}
        {!publicView && suggestion.boutiqueName && <span>{suggestion.boutiqueName}</span>}
        <time dateTime={suggestion.createdAt}>{new Date(suggestion.createdAt).toLocaleDateString('fr-FR')}</time>
      </div>
      <div className="suggestion-card-footer">
        <span>{suggestion.reactionCount} réaction{suggestion.reactionCount > 1 ? 's' : ''}</span>
        <span>{suggestion.commentCount} commentaire{suggestion.commentCount > 1 ? 's' : ''}</span>
        <Button type="button" size="sm" variant="secondary" onClick={() => onOpen(suggestion)}>Détails</Button>
      </div>
    </Card>
  );
}
