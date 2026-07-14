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
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

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
            $this->assertAccessible($entity);
            $this->em->remove($entity);
            $this->em->flush();

            return null;
        }

        if (isset($uriVariables['id'])) {
            $entity = $this->findEntity((string) $uriVariables['id']);
            $this->assertAccessible($entity);
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
            if (!$this->boutiqueContext->canAccessBoutique($boutique)) {
                throw new AccessDeniedHttpException('Access denied');
            }
            if (!$this->boutiqueContext->isSuperAdmin() && 'ROLE_CAISSIER' !== ($data->role ?? 'ROLE_CAISSIER')) {
                throw new AccessDeniedHttpException('Only super admins can assign boutique-admin access.');
            }
            if (!$this->boutiqueContext->isSuperAdmin() && in_array('ROLE_SUPER_ADMIN', $user->getRoles(), true)) {
                throw new AccessDeniedHttpException('This user cannot be assigned to a boutique.');
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
            if (!$this->boutiqueContext->isSuperAdmin() && 'ROLE_CAISSIER' !== $input->role) {
                throw new AccessDeniedHttpException('Only super admins can assign boutique-admin access.');
            }
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

    private function assertAccessible(UserShop $entity): void
    {
        if (!$this->boutiqueContext->canAccessBoutique($entity->getBoutique())) {
            throw new NotFoundHttpException('UserShop not found');
        }
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
