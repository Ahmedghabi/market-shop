<?php

namespace App\State\SocialProvider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\SocialProvider\SocialProviderInput;
use App\Dto\SocialProvider\SocialProviderOutput;
use App\Entity\SocialProvider;
use App\Repository\SocialProviderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class SocialProviderProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly SocialProviderRepository $repository,
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?SocialProviderOutput
    {
        if (isset($uriVariables['id'])) {
            $entity = $this->findEntity((string) $uriVariables['id']);
            $this->applyInput($entity, $data);
        } else {
            $entity = new SocialProvider(
                code: $data->code,
                name: $data->name,
                isActive: $data->isActive,
            );
            $this->em->persist($entity);
        }

        $this->em->flush();

        return $this->toOutput($entity);
    }

    private function applyInput(SocialProvider $entity, SocialProviderInput $input): void
    {
        $entity->setActive($input->isActive);
    }

    private function findEntity(string $id): SocialProvider
    {
        $entity = $this->repository->find($id);
        if (!$entity) {
            throw new NotFoundHttpException('SocialProvider not found');
        }

        return $entity;
    }

    private function toOutput(SocialProvider $entity): SocialProviderOutput
    {
        $output = new SocialProviderOutput();
        $output->id = (string) $entity->getId();
        $output->code = $entity->getCode();
        $output->name = $entity->getName();
        $output->isActive = $entity->isActive();

        return $output;
    }
}
