<?php

namespace App\State\Reference;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Reference\LocalityResource;
use App\Entity\Locality;
use App\Repository\GovernorateRepository;
use App\Repository\LocalityRepository;
use App\Service\Security\PublicApiRateLimiter;
use Symfony\Component\HttpFoundation\RequestStack;

/** @implements ProviderInterface<list<LocalityResource>> */
final readonly class LocalityProvider implements ProviderInterface
{
    public function __construct(
        private LocalityRepository $localities,
        private GovernorateRepository $governorates,
        private RequestStack $requestStack,
        private PublicApiRateLimiter $rateLimiter,
    ) {
    }

    /** @return list<LocalityResource> */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        unset($operation, $uriVariables, $context);

        $this->rateLimiter->consumeReference('localities');

        $request = $this->requestStack->getCurrentRequest();
        $governorateId = trim((string) $request?->query->get('governorateId', ''));
        $query = trim((string) $request?->query->get('q', ''));

        $governorate = '' !== $governorateId ? $this->governorates->find($governorateId) : null;

        if ('' !== $governorateId && null === $governorate) {
            return [];
        }

        return array_map($this->toOutput(...), $this->localities->findActiveByGovernorate($governorate, $query));
    }

    private function toOutput(Locality $locality): LocalityResource
    {
        $output = new LocalityResource();
        $output->id = (string) $locality->getId();
        $output->countryId = (string) $locality->getGovernorate()->getCountry()->getId();
        $output->governorateId = (string) $locality->getGovernorate()->getId();
        $output->name = $locality->getName();
        $output->postalCode = $locality->getPostalCode();

        return $output;
    }
}
