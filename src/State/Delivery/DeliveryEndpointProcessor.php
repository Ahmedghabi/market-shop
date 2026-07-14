<?php

namespace App\State\Delivery;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Delivery\DeliveryEndpointInput;
use App\Dto\Delivery\DeliveryEndpointOutput;
use App\Entity\DeliveryEndpoint;
use App\Enum\DeliveryEndpointType;
use App\Enum\DeliveryHttpMethod;
use App\Enum\DeliveryResponseType;
use App\Repository\DeliveryCompanyRepository;
use App\Repository\DeliveryEndpointRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class DeliveryEndpointProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly DeliveryEndpointRepository $repository,
        private readonly DeliveryCompanyRepository $companies,
        private readonly EntityManagerInterface $em,
        private readonly DeliveryEndpointProvider $provider,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?DeliveryEndpointOutput
    {
        if ($operation instanceof Delete) {
            $entity = $this->findEntity((string) $uriVariables['id']);
            $this->em->remove($entity);
            $this->em->flush();

            return null;
        }

        if (isset($uriVariables['id'])) {
            $entity = $this->findEntity((string) $uriVariables['id']);
            $this->applyInput($entity, $data);
        } else {
            $company = $this->companies->find($uriVariables['companyId'] ?? ($data->companyId ?? ''));
            if (!$company) {
                throw new NotFoundHttpException('Delivery company not found');
            }

            $entity = new DeliveryEndpoint(
                company: $company,
                type: DeliveryEndpointType::from($data->type),
                name: $data->name,
                url: $data->url,
                httpMethod: DeliveryHttpMethod::from(strtoupper($data->httpMethod)),
                headers: $data->headers,
                responseType: DeliveryResponseType::from($data->responseType),
                isActive: $data->isActive,
            );
            $company->addEndpoint($entity);
            $this->em->persist($entity);
        }

        $this->em->flush();

        return $this->provider->toOutput($entity);
    }

    private function applyInput(DeliveryEndpoint $entity, DeliveryEndpointInput $input): void
    {
        $entity->setType(DeliveryEndpointType::from($input->type));
        $entity->setName($input->name);
        $entity->setUrl($input->url);
        $entity->setHttpMethod(DeliveryHttpMethod::from(strtoupper($input->httpMethod)));
        $entity->setHeaders($input->headers);
        $entity->setResponseType(DeliveryResponseType::from($input->responseType));
        $entity->setActive($input->isActive);
    }

    private function findEntity(string $id): DeliveryEndpoint
    {
        $entity = $this->repository->find($id);
        if (!$entity) {
            throw new NotFoundHttpException('Delivery endpoint not found');
        }

        return $entity;
    }
}
