<?php

namespace App\State\User;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\UserShop\UserShopResource;
use App\Dto\UserShop\UserShopOutput;
use App\Entity\Boutique;
use App\Entity\User;
use App\Entity\UserShop;
use App\Enum\UserStatus;
use App\Repository\BoutiqueRepository;
use App\Repository\UserRepository;
use App\Repository\UserShopRepository;
use App\Security\BoutiqueContext;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class UserShopProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly UserShopRepository $repository,
        private readonly UserRepository $users,
        private readonly BoutiqueRepository $boutiques,
        private readonly EntityManagerInterface $em,
        private readonly Security $security,
        private readonly BoutiqueContext $boutiqueContext,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?UserShopOutput
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
            /** @var UserShopResource $data */
            $user = $this->users->find($data->userId);
            if (!$user instanceof User) {
                throw new NotFoundHttpException('User not found');
            }

            $boutique = $this->boutiques->find($data->boutiqueId);
            if (!$boutique instanceof Boutique) {
                throw new NotFoundHttpException('Boutique not found');
            }

            $entity = new UserShop(
                user: $user,
                boutique: $boutique,
                role: $data->role ?? 'ROLE_CAISSIER',
                status: UserStatus::Active,
            );
            $this->em->persist($entity);
        }

        $this->em->flush();

        return $this->toOutput($entity);
    }

    private function applyInput(UserShop $entity, UserShopResource $input): void
    {
        if (null !== $input->role) {
            $entity->setRole($input->role);
        }
        if (null !== $input->status) {
            $entity->setStatus(UserStatus::from($input->status));
        }
    }

    private function findEntity(string $id): UserShop
    {
        $entity = $this->repository->find($id);
        if (!$entity) {
            throw new NotFoundHttpException('UserShop not found');
        }

        return $entity;
    }

    private function toOutput(UserShop $entity): UserShopOutput
    {
        $output = new UserShopOutput();
        $output->id = (string) $entity->getId();
        $output->userId = (string) $entity->getUser()->getId();
        $output->boutiqueId = (string) $entity->getBoutique()->getId();
        $output->boutiqueName = $entity->getBoutique()->getName();
        $output->role = $entity->getRole();
        $output->status = $entity->getStatus()->value;
        $output->createdAt = $entity->getCreatedAt();

        return $output;
    }
}
