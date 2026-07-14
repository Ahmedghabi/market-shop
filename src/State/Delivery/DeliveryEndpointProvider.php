<?php

namespace App\State\Delivery;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Delivery\DeliveryEndpointOutput;
use App\Entity\DeliveryEndpoint;
use App\Repository\DeliveryCompanyRepository;
use App\Repository\DeliveryEndpointRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class DeliveryEndpointProvider implements ProviderInterface
{
    public function __construct(
        private readonly DeliveryEndpointRepository $repository,
        private readonly DeliveryCompanyRepository $companies,
    ) {
    }

    /** @return array<DeliveryEndpointOutput>|DeliveryEndpointOutput|null */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|DeliveryEndpointOutput|null
    {
        if (isset($uriVariables['id'])) {
            $entity = $this->repository->find($uriVariables['id']);

            return $entity ? $this->toOutput($entity) : null;
        }

        $company = $this->companies->find($uriVariables['companyId'] ?? '');
        if (!$company) {
            throw new NotFoundHttpException('Delivery company not found');
        }

        return array_map($this->toOutput(...), $this->repository->findByCompany($company));
    }

    public function toOutput(DeliveryEndpoint $entity): DeliveryEndpointOutput
    {
        $output = new DeliveryEndpointOutput();
        $output->id = (string) $entity->getId();
        $output->companyId = (string) $entity->getCompany()->getId();
        $output->type = $entity->getType()->value;
        $output->name = $entity->getName();
        $output->url = $entity->getUrl();
        $output->httpMethod = $entity->getHttpMethod()->value;
        $output->headers = $entity->getHeaders();
        $output->responseType = $entity->getResponseType()->value;
        $output->isActive = $entity->isActive();
        $output->createdAt = $entity->getCreatedAt()->format('c');
        $output->updatedAt = $entity->getUpdatedAt()?->format('c');

        return $output;
    }
}
