export type Suggestion = {
  id: string;
  title: string;
  description: string;
  categoryId?: string | null;
  categoryName?: string | null;
  boutiqueId?: string | null;
  boutiqueName?: string | null;
  authorId?: string | null;
  authorName?: string | null;
  status: string;
  visibility: string;
  isPublished: boolean;
  officialResponse?: string | null;
  officialResponseBy?: string | null;
  reactionCounts: Record<string, number>;
  reactionCount: number;
  commentCount: number;
  currentUserReaction?: string | null;
  showAuthorPublic: boolean;
  showBoutiquePublic: boolean;
  createdAt: string;
  updatedAt?: string | null;
  publishedAt?: string | null;
  closedAt?: string | null;
  history?: SuggestionHistoryItem[];
  comments?: SuggestionComment[];
};

export type SuggestionCategory = {
  id: string;
  name: string;
  slug: string;
  description?: string | null;
  isActive: boolean;
  position: number;
};

export type SuggestionComment = {
  id: string;
  suggestionId: string;
  parentId?: string | null;
  content: string;
  visibility: string;
  userId?: string | null;
  authorName?: string | null;
  createdAt: string;
  updatedAt?: string | null;
};

export type SuggestionHistoryItem = {
  id: string;
  oldStatus?: string | null;
  newStatus: string;
  changedBy?: string | null;
  comment?: string | null;
  createdAt: string;
};

export type ReactionCounts = Record<string, number>;

export const SUGGESTION_STATUS_OPTIONS = [
  { value: 'submitted', label: 'Soumise' },
  { value: 'analyzing', label: 'En analyse' },
  { value: 'accepted', label: 'Acceptée' },
  { value: 'planned', label: 'Planifiée' },
  { value: 'in_development', label: 'En développement' },
  { value: 'implemented', label: 'Implémentée' },
  { value: 'rejected', label: 'Rejetée' },
  { value: 'archived', label: 'Archivée' },
];

export const SUGGESTION_REACTION_OPTIONS = [
  { value: 'like', label: 'J’aime', emoji: '👍' },
  { value: 'support', label: 'Je soutiens', emoji: '🙌' },
  { value: 'priority', label: 'Prioritaire', emoji: '🔥' },
  { value: 'interesting', label: 'Intéressante', emoji: '💡' },
  { value: 'not_useful', label: 'Pas utile', emoji: '👎' },
];

export function suggestionStatusLabel(status: string): string {
  return SUGGESTION_STATUS_OPTIONS.find((option) => option.value === status)?.label ?? status;
}
