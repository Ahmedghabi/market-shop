<?php

namespace App\State\Suggestion;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Suggestion;
use App\Entity\SuggestionReaction;
use App\Repository\SuggestionReactionRepository;
use App\Repository\SuggestionRepository;
use App\Service\Suggestion\SuggestionAccessService;
use App\Service\Suggestion\SuggestionOutputMapper;

final readonly class SuggestionReactionProvider implements ProviderInterface
{
    public function __construct(private SuggestionRepository $suggestions, private SuggestionReactionRepository $reactions, private SuggestionAccessService $access, private SuggestionOutputMapper $mapper)
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $suggestion = $this->findSuggestion($uriVariables['suggestionId'] ?? '');
        $this->access->assertCanRead($suggestion);
        if (isset($uriVariables['id'])) {
            $reaction = $this->reactions->find($uriVariables['id']);

            return $reaction instanceof SuggestionReaction && $reaction->getSuggestion() === $suggestion ? $this->mapper->reaction($reaction) : null;
        }

        return array_map(fn (SuggestionReaction $reaction) => $this->mapper->reaction($reaction), $this->reactions->findBySuggestion($suggestion));
    }

    private function findSuggestion(string $id): Suggestion
    {
        $suggestion = $this->suggestions->find($id);
        if (!$suggestion instanceof Suggestion) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Suggestion not found.');
        }

        return $suggestion;
    }
}
