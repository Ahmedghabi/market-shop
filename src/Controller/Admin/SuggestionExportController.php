<?php

namespace App\Controller\Admin;

use App\Entity\Suggestion;
use App\Enum\SuggestionStatus;
use App\Repository\SuggestionCommentRepository;
use App\Repository\SuggestionReactionRepository;
use App\Repository\SuggestionRepository;
use App\Service\Audit\AuditLogService;
use App\Service\Suggestion\SuggestionAccessService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final readonly class SuggestionExportController
{
    public function __construct(private SuggestionRepository $suggestions, private SuggestionReactionRepository $reactions, private SuggestionCommentRepository $comments, private SuggestionAccessService $access, private AuditLogService $audit)
    {
    }

    #[Route('/api/admin/suggestions/export', name: 'admin_suggestions_export', methods: ['GET'])]
    public function __invoke(Request $request): Response
    {
        $boutique = $this->access->resolveBoutique($request, false);
        if (!$this->access->isSuperAdmin() && null === $boutique) {
            $this->access->resolveBoutique($request);
        }
        $boutique ??= $this->access->resolveBoutique($request, false);
        $this->access->assertPermission('suggestion.export', $boutique);

        $status = $request->query->all('status') ?: $request->query->get('status');
        $statusValues = is_array($status) ? $status : (null === $status || '' === $status ? [] : [$status]);
        foreach ($statusValues as $value) {
            try {
                SuggestionStatus::from(strtolower((string) $value));
            } catch (\ValueError) {
                return new Response('Unknown suggestion status filter.', Response::HTTP_BAD_REQUEST);
            }
        }
        $from = $this->date($request->query->get('from'));
        $to = $this->date($request->query->get('to'));
        if (null !== $request->query->get('from') && null === $from || null !== $request->query->get('to') && null === $to) {
            return new Response('Invalid date filter.', Response::HTTP_BAD_REQUEST);
        }
        $filters = [
            'search' => $request->query->get('search'),
            'category' => $request->query->get('category'),
            'status' => $status,
            'from' => $from,
            'to' => $to,
        ];
        $rows = $this->suggestions->findForExport($boutique, $filters);
        $handle = fopen('php://temp', 'w+');
        fputcsv($handle, ['id', 'title', 'description', 'category', 'boutique', 'author', 'status', 'visibility', 'published', 'reaction_count', 'comment_count', 'created_at']);
        foreach ($rows as $suggestion) {
            if (!$suggestion instanceof Suggestion) {
                continue;
            }
            fputcsv($handle, [
                $this->csv((string) $suggestion->getId()), $this->csv($suggestion->getTitle()), $this->csv($suggestion->getDescription()),
                $this->csv($suggestion->getCategory()?->getName()), $this->csv($suggestion->getBoutique()->getName()),
                $this->csv($suggestion->getCreatedBy()->getUserIdentifier()), $suggestion->getStatus()->value, $suggestion->getVisibility()->value,
                $suggestion->isPublished() ? 'true' : 'false', $this->reactions->countBySuggestion($suggestion), $this->comments->countBySuggestion($suggestion), $suggestion->getCreatedAt()->format('c'),
            ]);
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);
        $this->audit->log(
            actorEmail: $this->access->requireUser()->getUserIdentifier(),
            actorRole: $this->access->isSuperAdmin() ? 'ROLE_SUPER_ADMIN' : 'ROLE_BOUTIQUE_ADMIN',
            action: 'suggestion.export',
            resourceType: 'Suggestion',
            details: ['count' => count($rows), 'filters' => $filters],
            ipAddress: $request->getClientIp(),
            boutiqueId: $boutique?->getId() ? (string) $boutique->getId() : null,
        );

        return new Response($csv ?: '', Response::HTTP_OK, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="suggestions.csv"',
        ]);
    }

    private function csv(?string $value): string
    {
        $value ??= '';

        return preg_match('/^[=+\-@]/', $value) ? "'".$value : $value;
    }

    private function date(?string $value): ?\DateTimeImmutable
    {
        if (null === $value || '' === $value) {
            return null;
        }
        try {
            return new \DateTimeImmutable($value);
        } catch (\Exception) {
            return null;
        }
    }
}
