<?php

namespace App\State\Delivery;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Delivery\DeliveryApiLogOutput;
use App\Entity\DeliveryApiLog;
use App\Repository\DeliveryApiLogRepository;
use App\Repository\DeliveryCompanyRepository;

final class DeliveryApiLogProvider implements ProviderInterface
{
    public function __construct(
        private readonly DeliveryApiLogRepository $repository,
        private readonly DeliveryCompanyRepository $companies,
    ) {
    }

    /** @return list<DeliveryApiLogOutput> */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $request = $context['request'] ?? null;
        $companyId = $request?->query->get('companyId');
        $company = is_string($companyId) ? $this->companies->find($companyId) : null;

        return array_map($this->toOutput(...), $this->repository->findRecent(200, $company));
    }

    private function toOutput(DeliveryApiLog $entity): DeliveryApiLogOutput
    {
        $output = new DeliveryApiLogOutput();
        $output->id = (string) $entity->getId();
        $output->deliveryCompanyId = (string) $entity->getDeliveryCompany()->getId();
        $output->deliveryCompanyName = $entity->getDeliveryCompany()->getName();
        $output->boutiqueId = $entity->getBoutique() ? (string) $entity->getBoutique()->getId() : null;
        $output->endpointType = $entity->getEndpointType()?->value;
        $output->requestMethod = $entity->getRequestMethod();
        $output->requestUrl = $entity->getRequestUrl();
        $output->requestBody = $entity->getRequestBody();
        $output->responseStatus = $entity->getResponseStatus();
        $output->responseBody = $entity->getResponseBody();
        $output->success = $entity->isSuccess();
        $output->errorMessage = $entity->getErrorMessage();
        $output->durationMs = $entity->getDurationMs();
        $output->createdAt = $entity->getCreatedAt()->format('c');

        return $output;
    }
}
