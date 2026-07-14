import { Badge, statusBadge } from '../Badge';
import { suggestionStatusLabel } from './SuggestionTypes';

export function SuggestionStatusBadge({ status }: { status: string }) {
  const style = statusBadge(status);
  return <Badge tone={style.tone}>{suggestionStatusLabel(status)}</Badge>;
}
