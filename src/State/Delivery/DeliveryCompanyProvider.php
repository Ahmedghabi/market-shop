<?php

namespace App\State\Delivery;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Delivery\DeliveryCompanyOutput;
use App\Service\AppConfigService;
use App\Repository\DeliveryCompanyRepository;

final class DeliveryCompanyProvider implements ProviderInterface
{
    public function __construct(
        private readonly DeliveryCompanyRepository $repository,
        private readonly AppConfigService $appConfig,
    ) {
    }

    /** @return array<DeliveryCompanyOutput>|DeliveryCompanyOutput|null */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|DeliveryCompanyOutput|null
    {
        if (isset($uriVariables['id'])) {
            $entity = $this->repository->find($uriVariables['id']);

            return $entity ? $this->toOutput($entity) : null;
        }

        if (!$this->appConfig->isModuleEnabled('livraison')) {
            return [];
        }

        $visibleCompanies = $this->appConfig->section('delivery')['visible_companies'] ?? [];
        $visibleCompanies = is_array($visibleCompanies) ? array_map('strval', $visibleCompanies) : [];

        $companies = $this->repository->findActive();
        if ([] !== $visibleCompanies) {
            $companies = array_values(array_filter($companies, fn ($company) => in_array($company->getSlug(), $visibleCompanies, true)));
        }

        return array_map([$this, 'toOutput'], $companies);
    }

    private function toOutput(object $entity): DeliveryCompanyOutput
    {
        $output = new DeliveryCompanyOutput();
        $output->id = (string) $entity->getId();
        $output->name = $entity->getName();
        $output->slug = $entity->getSlug();
        $output->baseUrl = $entity->getBaseUrl();
        $output->authEndpoint = $entity->getAuthEndpoint();
        $output->submitOrderEndpoint = $entity->getSubmitOrderEndpoint();
        $output->trackEndpoint = $entity->getTrackEndpoint();
        $output->description = $entity->getDescription();
        $output->isActive = $entity->isActive();

        return $output;
    }
}
