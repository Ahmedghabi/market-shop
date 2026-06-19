<?php

namespace App\State\Boutique;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Boutique\BoutiqueInput;
use App\Dto\Boutique\BoutiqueOutput;
use App\Entity\Boutique;
use App\Entity\BoutiqueSettings;
use App\Enum\BoutiqueStatus;
use App\Repository\BoutiqueRepository;
use App\Security\BoutiqueContext;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class BoutiqueProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly BoutiqueRepository $repository,
        private readonly EntityManagerInterface $em,
        private readonly BoutiqueContext $context,
        private readonly NotificationService $notifications,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): BoutiqueOutput
    {
        $isSuperAdmin = $this->context->isSuperAdmin();
        $operationName = $operation->getName() ?? '';

        if (isset($uriVariables['id']) && in_array($operationName, ['approve_boutique', 'reject_boutique', 'suspend_boutique', 'activate_boutique', 'archive_boutique'], true)) {
            $entity = $this->findBoutique((string) $uriVariables['id']);

            match ($operationName) {
                'approve_boutique' => $this->approveBoutique($entity),
                'reject_boutique' => $this->rejectBoutique($entity),
                'suspend_boutique' => $this->suspendBoutique($entity),
                'activate_boutique' => $this->activateBoutique($entity),
                'archive_boutique' => $this->archiveBoutique($entity),
                default => throw new \InvalidArgumentException('Unknown operation: '.$operationName),
            };

            $this->em->flush();

            return $this->toOutput($entity);
        }

        if (!$data instanceof BoutiqueInput) {
            throw new \InvalidArgumentException('Expected BoutiqueInput');
        }

        if (isset($uriVariables['id'])) {
            $entity = $this->findBoutique((string) $uriVariables['id']);

            if (!$this->context->canAccessBoutique($entity)) {
                throw new AccessDeniedHttpException('Access denied');
            }

            $this->applyInput($entity, $data);
        } else {
            $entity = new Boutique($data->name, $data->slug);
            $this->applyInput($entity, $data);
            $entity->setStatus($isSuperAdmin ? BoutiqueStatus::Active : BoutiqueStatus::Pending);
            $this->em->persist($entity);
            $this->notifications->notify(null, 'boutique_created', 'Nouvelle boutique', sprintf('La boutique "%s" a été créée avec le statut %s.', $entity->getName(), $entity->getStatus()->value), $entity);
        }

        $this->em->flush();

        return $this->toOutput($entity);
    }

    private function approveBoutique(Boutique $entity): void
    {
        $entity->approve($this->context->getUserIdentifier());

        foreach ($entity->getUserShops() as $userShop) {
            $userShop->setStatus(\App\Enum\UserStatus::Active);
        }

        $this->notifications->notify(null, 'boutique_approved', 'Boutique approuvée', sprintf('La boutique "%s" a été approuvée.', $entity->getName()), $entity);
    }

    private function rejectBoutique(Boutique $entity): void
    {
        $entity->reject();

        foreach ($entity->getUserShops() as $userShop) {
            $userShop->setStatus(\App\Enum\UserStatus::Rejected);
        }

        $this->notifications->notify(null, 'boutique_rejected', 'Boutique rejetée', sprintf('La boutique "%s" a été rejetée.', $entity->getName()), $entity);
    }

    private function suspendBoutique(Boutique $entity): void
    {
        $entity->suspend();

        $this->notifications->notify(null, 'boutique_suspended', 'Boutique suspendue', sprintf('La boutique "%s" a été suspendue.', $entity->getName()), $entity);
    }

    private function activateBoutique(Boutique $entity): void
    {
        $entity->reactivate();

        $this->notifications->notify(null, 'boutique_activated', 'Boutique réactivée', sprintf('La boutique "%s" a été réactivée.', $entity->getName()), $entity);
    }

    private function archiveBoutique(Boutique $entity): void
    {
        $entity->archive();

        $this->notifications->notify(null, 'boutique_archived', 'Boutique archivée', sprintf('La boutique "%s" a été archivée.', $entity->getName()), $entity);
    }

    private function applyInput(Boutique $entity, BoutiqueInput $input): void
    {
        $entity->setName($input->name);
        $entity->setSlug($input->slug);
        $entity->setDescription($input->description);
        $entity->setCoverImage($input->coverImage);
        $entity->setEmail($input->email);
        $entity->setPhone($input->phone);
        $entity->setWebsite($input->website);
        $entity->setCustomDomain($input->customDomain);

        if ($this->context->isSuperAdmin()) {
            $entity->setStatus(BoutiqueStatus::from($input->status));
            $entity->setIsVerified($input->isVerified);
            $entity->setIsFeatured($input->isFeatured);
        }

        $settings = $entity->getSettings() ?? new BoutiqueSettings($entity);
        $settings->updateContact(
            $input->logoUrl,
            $input->primaryColor,
            $input->secondaryColor,
            $input->domain,
            $input->contactEmail,
            $input->contactPhone,
            $input->address,
            $input->socialLinks,
        );
        $settings->setMetaPixelId($input->metaPixelId);
        $this->em->persist($settings);
    }

    private function toOutput(Boutique $entity): BoutiqueOutput
    {
        $output = new BoutiqueOutput();
        $output->id = (string) $entity->getId();
        $output->name = $entity->getName();
        $output->slug = $entity->getSlug();
        $output->status = $entity->getStatus()->value;
        $output->ownerId = $entity->getOwner() ? (string) $entity->getOwner()->getId() : null;
        $output->description = $entity->getDescription();
        $output->coverImage = $entity->getCoverImage();
        $output->email = $entity->getEmail();
        $output->phone = $entity->getPhone();
        $output->website = $entity->getWebsite();
        $output->customDomain = $entity->getCustomDomain();
        $output->isVerified = $entity->isVerified();
        $output->isFeatured = $entity->isFeatured();
        $output->approvedAt = $entity->getApprovedAt()?->format('c');
        $output->approvedBy = $entity->getApprovedBy();
        $output->rejectionReason = $entity->getRejectionReason();
        $settings = $entity->getSettings();
        $output->primaryColor = $settings?->getPrimaryColor() ?? '#3525cd';
        $output->secondaryColor = $settings?->getSecondaryColor() ?? '#505f76';
        $output->domain = $settings?->getDomain();
        $output->logoUrl = $settings?->getLogoUrl();
        $output->contactEmail = $settings?->getContactEmail();
        $output->contactPhone = $settings?->getContactPhone();
        $output->address = $settings?->getAddress();
        $output->socialLinks = $settings?->getSocialLinks() ?? [];
        $output->metaPixelId = $settings?->getMetaPixelId();
        $output->createdAt = $entity->getCreatedAt();
        $output->updatedAt = $entity->getUpdatedAt();
        $output->usersCount = $entity->getUsers()->count();
        $output->productsCount = $entity->getProducts()->count();
        $output->ordersCount = $entity->getOrdersCount();
        $output->totalRevenue = $entity->getTotalRevenue();
        $output->hasActiveSubscription = $entity->hasActiveSubscription();
        $output->isVisiblePublicly = $entity->isVisiblePublicly();

        return $output;
    }

    private function findBoutique(string $id): Boutique
    {
        $entity = $this->repository->find($id);
        if (!$entity) {
            throw new NotFoundHttpException('Boutique not found');
        }

        return $entity;
    }
}
