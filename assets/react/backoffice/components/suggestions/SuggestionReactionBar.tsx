import { Button } from '../Button';
import { SUGGESTION_REACTION_OPTIONS, type ReactionCounts } from './SuggestionTypes';

export function SuggestionReactionBar({
  counts,
  currentReaction,
  onReact,
  readOnly = false,
  isLoading = false,
}: {
  counts: ReactionCounts;
  currentReaction?: string | null;
  onReact?: (type: string) => void;
  readOnly?: boolean;
  isLoading?: boolean;
}) {
  return (
    <div className="suggestion-reaction-bar" aria-label="Réactions">
      {SUGGESTION_REACTION_OPTIONS.map((reaction) => {
        const selected = currentReaction === reaction.value;
        return (
          <Button
            key={reaction.value}
            type="button"
            size="sm"
            variant={selected ? 'primary' : 'secondary'}
            className="suggestion-reaction-button"
            disabled={readOnly || isLoading || !onReact}
            aria-pressed={selected}
            aria-label={`${reaction.label}: ${counts[reaction.value] ?? 0}`}
            onClick={() => onReact?.(reaction.value)}
          >
            <span aria-hidden="true">{reaction.emoji}</span> {counts[reaction.value] ?? 0}
          </Button>
        );
      })}
    </div>
  );
}
