<?php

namespace App\State\Extension;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Extension\ExtensionInput;
use App\Dto\Extension\ExtensionOutput;
use App\Entity\Extension;
use App\Enum\ExtensionType;
use App\Repository\BoutiqueExtensionRepository;
use App\Repository\BoutiqueRepository;
use App\Repository\ExtensionRepository;
use App\Repository\ExtensionRequestRepository;
use App\Security\BoutiqueContext;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ExtensionProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly ExtensionRepository $repository,
        private readonly BoutiqueExtensionRepository $boutiqueExtensions,
        private readonly ExtensionRequestRepository $extensionRequests,
        private readonly BoutiqueRepository $boutiques,
        private readonly BoutiqueContext $context,
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?ExtensionOutput
    {
        if ($operation instanceof Delete) {
            $entity = $this->findEntity((string) ($uriVariables['id'] ?? ''));
            $this->em->remove($entity);
            $this->em->flush();

            return null;
        }

        if (isset($uriVariables['id'])) {
            return $this->update((string) $uriVariables['id'], $data);
        }

        return $this->create($data);
    }

    private function create(mixed $data): ExtensionOutput
    {
        if (!$data instanceof ExtensionInput) {
            throw new \InvalidArgumentException('Expected ExtensionInput');
        }

        $entity = new Extension(
            code: $data->code,
            name: $data->name,
            description: $data->description,
            type: ExtensionType::from($data->type),
            targetCode: $data->targetCode,
            value: $data->value,
            priceTnd: $data->priceTnd,
            durationMonths: $data->durationMonths,
            requiresValidation: $data->requiresValidation,
            isActive: $data->isActive,
            icon: $data->icon,
        );
        $this->em->persist($entity);
        $this->em->flush();

        return $this->toProvider()->toOutput($entity);
    }

    private function update(string $id, mixed $data): ExtensionOutput
    {
        $entity = $this->findEntity($id);

        if (!$data instanceof ExtensionInput) {
            throw new \InvalidArgumentException('Expected ExtensionInput');
        }

        $entity->setCode($data->code);
        $entity->setName($data->name);
        $entity->setDescription($data->description);
        $entity->setType(ExtensionType::from($data->type));
        $entity->setTargetCode($data->targetCode);
        $entity->setValue($data->value);
        $entity->setPriceTnd($data->priceTnd);
        $entity->setDurationMonths($data->durationMonths);
        $entity->setRequiresValidation($data->requiresValidation);
        $entity->setIsActive($data->isActive);
        $entity->setIcon($data->icon);
        $this->em->flush();

        return $this->toProvider()->toOutput($entity);
    }

    private function findEntity(string $id): Extension
    {
        $entity = $this->repository->find($id);
        if (!$entity) {
            throw new NotFoundHttpException('Extension not found');
        }

        return $entity;
    }

    private function toProvider(): ExtensionProvider
    {
        return new ExtensionProvider(
            $this->repository,
            $this->boutiqueExtensions,
            $this->extensionRequests,
            $this->boutiques,
            $this->context,
        );
    }
}
