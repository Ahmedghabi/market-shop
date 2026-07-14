import { useState } from 'react';
import { Button } from '../Button';
import { Textarea } from '../FormField';
import { EmptyState } from '../States';
import type { SuggestionComment } from './SuggestionTypes';

export function SuggestionCommentList({ comments = [] }: { comments?: SuggestionComment[] }) {
  if (comments.length === 0) {
    return <EmptyState title="Aucun commentaire" message="Les échanges apparaîtront ici." />;
  }

  return (
    <div className="suggestion-comment-list" aria-label="Commentaires">
      {comments.map((comment) => (
        <article className="suggestion-comment" key={comment.id}>
          <div className="suggestion-comment-meta">
            <strong>{comment.authorName || 'Utilisateur'}</strong>
            <time dateTime={comment.createdAt}>{new Date(comment.createdAt).toLocaleString('fr-FR')}</time>
          </div>
          <p>{comment.content}</p>
        </article>
      ))}
    </div>
  );
}

export function SuggestionCommentForm({
  onSubmit,
  isLoading = false,
  placeholder = 'Ajouter un commentaire...',
}: {
  onSubmit: (content: string) => Promise<void>;
  isLoading?: boolean;
  placeholder?: string;
}) {
  const [content, setContent] = useState('');

  async function submit() {
    const value = content.trim();
    if (!value || isLoading) return;
    await onSubmit(value);
    setContent('');
  }

  return (
    <div className="suggestion-comment-form">
      <Textarea
        aria-label="Nouveau commentaire"
        value={content}
        onChange={(event) => setContent(event.target.value)}
        placeholder={placeholder}
        rows={3}
        disabled={isLoading}
      />
      <Button type="button" size="sm" disabled={!content.trim() || isLoading} onClick={() => { void submit(); }}>
        {isLoading ? 'Envoi...' : 'Commenter'}
      </Button>
    </div>
  );
}
