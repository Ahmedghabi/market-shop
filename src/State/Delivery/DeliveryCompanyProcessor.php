<?php

namespace App\State\Delivery;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Delivery\DeliveryCompanyInput;
use App\Dto\Delivery\DeliveryCompanyOutput;
use App\Entity\DeliveryCompany;
use App\Repository\DeliveryCompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class DeliveryCompanyProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly DeliveryCompanyRepository $repository,
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?DeliveryCompanyOutput
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
            $entity = new DeliveryCompany(
                name: $data->name,
                slug: $data->slug,
                baseUrl: $data->baseUrl,
                authEndpoint: $data->authEndpoint,
                submitOrderEndpoint: $data->submitOrderEndpoint,
                trackEndpoint: $data->trackEndpoint,
                description: $data->description,
                isActive: $data->isActive,
            );
            $this->em->persist($entity);
        }

        $this->em->flush();

        return $this->toOutput($entity);
    }

    private function applyInput(DeliveryCompany $entity, DeliveryCompanyInput $input): void
    {
        $entity->setName($input->name);
        $entity->setSlug($input->slug);
        $entity->setBaseUrl($input->baseUrl);
        $entity->setAuthEndpoint($input->authEndpoint);
        $entity->setSubmitOrderEndpoint($input->submitOrderEndpoint);
        $entity->setTrackEndpoint($input->trackEndpoint);
        $entity->setDescription($input->description);
        $entity->setIsActive($input->isActive);
    }

    private function findEntity(string $id): DeliveryCompany
    {
        $entity = $this->repository->find($id);
        if (!$entity) {
            throw new NotFoundHttpException('Delivery company not found');
        }

        return $entity;
    }

    private function toOutput(DeliveryCompany $entity): DeliveryCompanyOutput
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
