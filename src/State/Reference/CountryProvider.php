<?php

namespace App\State\Reference;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Reference\CountryResource;
use App\Entity\Country;
use App\Repository\CountryRepository;
use App\Service\Security\PublicApiRateLimiter;

/** @implements ProviderInterface<list<CountryResource>> */
final readonly class CountryProvider implements ProviderInterface
{
    public function __construct(
        private CountryRepository $countries,
        private PublicApiRateLimiter $rateLimiter,
    ) {
    }

    /** @return list<CountryResource> */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        unset($operation, $uriVariables, $context);

        $this->rateLimiter->consumeReference('countries');

        return array_map($this->toOutput(...), $this->countries->findActive());
    }

    private function toOutput(Country $country): CountryResource
    {
        $output = new CountryResource();
        $output->id = (string) $country->getId();
        $output->name = $country->getName();
        $output->code = $country->getCode();
        $output->phoneCode = $country->getPhoneCode();

        return $output;
    }
}
