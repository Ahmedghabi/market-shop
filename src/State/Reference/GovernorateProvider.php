<?php

namespace App\State\Reference;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Reference\GovernorateResource;
use App\Entity\Governorate;
use App\Repository\CountryRepository;
use App\Repository\GovernorateRepository;
use App\Service\Security\PublicApiRateLimiter;
use Symfony\Component\HttpFoundation\RequestStack;

/** @implements ProviderInterface<list<GovernorateResource>> */
final readonly class GovernorateProvider implements ProviderInterface
{
    public function __construct(
        private GovernorateRepository $governorates,
        private CountryRepository $countries,
        private RequestStack $requestStack,
        private PublicApiRateLimiter $rateLimiter,
    ) {
    }

    /** @return list<GovernorateResource> */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        unset($operation, $uriVariables, $context);

        $this->rateLimiter->consumeReference('governorates');

        $request = $this->requestStack->getCurrentRequest();
        $countryId = trim((string) $request?->query->get('countryId', ''));
        $countryCode = strtoupper(trim((string) $request?->query->get('countryCode', '')));

        $country = '' !== $countryId
            ? $this->countries->find($countryId)
            : ('' !== $countryCode ? $this->countries->findOneByCode($countryCode) : null);

        if (('' !== $countryId || '' !== $countryCode) && null === $country) {
            return [];
        }

        return array_map($this->toOutput(...), $this->governorates->findActiveByCountry($country));
    }

    private function toOutput(Governorate $governorate): GovernorateResource
    {
        $output = new GovernorateResource();
        $output->id = (string) $governorate->getId();
        $output->countryId = (string) $governorate->getCountry()->getId();
        $output->name = $governorate->getName();
        $output->code = $governorate->getCode();

        return $output;
    }
}
