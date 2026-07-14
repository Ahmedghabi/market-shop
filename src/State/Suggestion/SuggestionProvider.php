<?php

namespace App\State\Suggestion;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Suggestion;
use App\Enum\SuggestionStatus;
use App\Service\Suggestion\SuggestionAccessService;
use App\Service\Suggestion\SuggestionOutputMapper;
use App\Repository\SuggestionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/** @implements ProviderInterface<object|array|null> */
final readonly class SuggestionProvider implements ProviderInterface
{
    public function __construct(private SuggestionRepository $suggestions, private SuggestionAccessService $access, private SuggestionOutputMapper $mapper)
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $public = str_starts_with($operation->getUriTemplate() ?? '', '/public/');
        $request = $context['request'] ?? null;
        $boutique = $this->access->resolveBoutique($request instanceof Request ? $request : null, false);
        if ($public && !$this->hasExplicitBoutiqueContext($request instanceof Request ? $request : null)) {
            // The standalone public board is global, even when a super admin is authenticated.
            $boutique = null;
        }
        $filters = $this->filters($request instanceof Request ? $request : null);
        if ($public && !$boutique) {
            $id = $uriVariables['id'] ?? null;
            if (null !== $id) {
                $suggestion = $this->suggestions->findOnePublicAny((string) $id);

                return $suggestion instanceof Suggestion ? $this->mapper->suggestion($suggestion, true, true) : null;
            }

            return array_map(
                fn (Suggestion $item) => $this->mapper->suggestion($item, true),
                $this->suggestions->findPublicAll($filters, $filters['limit'], $filters['offset']),
            );
        }
        if (!$boutique && !$public && $this->access->isSuperAdmin()) {
            if (isset($uriVariables['id'])) {
                $suggestion = $this->suggestions->find($uriVariables['id']);

                return $suggestion instanceof Suggestion ? $this->mapper->suggestion($suggestion, false, true) : null;
            }

            return array_map(fn (Suggestion $item) => $this->mapper->suggestion($item), $this->suggestions->findForSuperAdmin($filters, $filters['limit'], $filters['offset']));
        }
        if (!$boutique) {
            return $operation->getCollection() ? [] : null;
        }

        $id = $uriVariables['id'] ?? null;
        if (null !== $id) {
            $suggestion = $public ? $this->suggestions->findOnePublic((string) $id, $boutique) : $this->suggestions->findOneForBoutique((string) $id, $boutique);
            if (!$suggestion instanceof Suggestion) {
                return null;
            }
            if ($public) {
                $this->access->assertCanRead($suggestion, true);
            } else {
                $this->access->assertCanRead($suggestion);
            }

            return $this->mapper->suggestion($suggestion, $public, true);
        }

        if ($public) {
            return array_map(fn (Suggestion $item) => $this->mapper->suggestion($item, true), $this->suggestions->findPublic($boutique, $filters, $filters['limit'], $filters['offset']));
        }

        $user = $this->access->requireUser();
        $items = $this->access->isAdmin($boutique)
            ? $this->suggestions->findForBoutique($boutique, $filters, $filters['limit'], $filters['offset'])
            : $this->suggestions->findForUser($boutique, $user, $filters, $filters['limit'], $filters['offset']);

        return array_map(fn (Suggestion $item) => $this->mapper->suggestion($item), $items);
    }

    /** @return array<string, mixed> */
    private function filters(?Request $request): array
    {
        $query = $request?->query;
        $limit = (int) ($query?->get('limit', 30) ?? 30);
        $page = max(1, (int) ($query?->get('page', 1) ?? 1));
        $status = $query?->all('status') ?? $query?->get('status');
        if (null !== $status) {
            $status = is_array($status) ? $status : [$status];
            try {
                $status = array_map(static fn (string $value): SuggestionStatus => SuggestionStatus::from(strtolower($value)), $status);
            } catch (\ValueError) {
                throw new BadRequestHttpException('Unknown suggestion status filter.');
            }
        }

        return [
            'search' => $query?->get('search'), 'category' => $query?->get('category'), 'status' => $status,
            'from' => $this->date($query?->get('from')), 'to' => $this->date($query?->get('to')),
            'sort' => $query?->get('sort', 'newest'), 'limit' => min(100, max(1, $limit)), 'offset' => ($page - 1) * min(100, max(1, $limit)),
        ];
    }

    private function date(?string $date): ?\DateTimeImmutable
    {
        if (null === $date || '' === $date) {
            return null;
        }
        try {
            return new \DateTimeImmutable($date);
        } catch (\Exception) {
            return null;
        }
    }

    private function hasExplicitBoutiqueContext(?Request $request): bool
    {
        if (!$request) {
            return false;
        }

        return null !== $request->attributes->get('_boutique')
            || null !== $request->query->get('boutiqueId')
            || null !== $request->query->get('boutiqueSlug');
    }
}
