<?php

namespace App\State\Boutique;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Boutique\BoutiqueOutput;
use App\Repository\BoutiqueRepository;
use App\Security\BoutiqueContext;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class BoutiqueProvider implements ProviderInterface
{
    public function __construct(
        private readonly BoutiqueRepository $repository,
        private readonly BoutiqueContext $context,
        private readonly TokenStorageInterface $tokenStorage,
    ) {
    }

    /** @return array<BoutiqueOutput>|BoutiqueOutput|null */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|BoutiqueOutput|null
    {
        $identifier = $uriVariables['id'] ?? $uriVariables['slug'] ?? null;
        if (null !== $identifier) {
            $entity = $this->repository->findBySlugOrId($identifier);
            if (!$entity) {
                return null;
            }

            $token = $this->tokenStorage->getToken();
            $isAuthenticated = null !== $token && $token->getUser();

            if ($isAuthenticated) {
                if (!$this->context->canAccessBoutique($entity)) {
                    return null;
                }
            } elseif (!$entity->isVisiblePublicly()) {
                return null;
            }

            return $this->toOutput($entity);
        }

        $token = $this->tokenStorage->getToken();

        if (null === $token || !$token->getUser()) {
            $entities = $this->repository->findPublishedForPublic();
        } else {
            $entities = $this->repository->findVisibleTo($this->context->getBoutiqueIds(), $this->context->isSuperAdmin());
        }

        return array_map([$this, 'toOutput'], $entities);
    }

    private function toOutput(object $entity): BoutiqueOutput
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
        $output->createdAt = $entity->getCreatedAt();
        $output->updatedAt = $entity->getUpdatedAt();
        $output->usersCount = $entity->getUsers()->count();
        $output->productsCount = $entity->getProducts()->count();
        $output->ordersCount = $entity->getOrdersCount();
        $output->totalRevenue = $entity->getTotalRevenue();
        $output->hasActiveSubscription = $entity->hasActiveSubscription();
        $output->isVisiblePublicly = $entity->isVisiblePublicly();

        $settings = $entity->getSettings();
        if (null !== $settings) {
            $output->logoUrl = $settings->getLogoUrl();
            $output->primaryColor = $settings->getPrimaryColor() ?? '#3525cd';
            $output->secondaryColor = $settings->getSecondaryColor() ?? '#505f76';
            $output->domain = $settings->getDomain();
            $output->contactEmail = $settings->getContactEmail();
            $output->contactPhone = $settings->getContactPhone();
            $output->address = $settings->getAddress();
            $output->socialLinks = $settings->getSocialLinks();
            $output->colorPalette = $settings->getColorPalette();
            $output->theme = $settings->getTheme();
            $output->fontFamily = $settings->getFontFamily();
            $output->fontSize = $settings->getFontSize();
            $output->borderRadius = $settings->getBorderRadius();
            $output->iconSet = $settings->getIconSet();
            $output->headerConfig = $settings->getHeaderConfig();
            $output->footerConfig = $settings->getFooterConfig();
            $output->navigationItems = $settings->getNavigationItems();
            $output->frontOfficePages = $settings->getFrontOfficePages();
            $output->featuredCategories = $settings->getFeaturedCategories();
        } else {
            $output->primaryColor = '#3525cd';
            $output->secondaryColor = '#505f76';
            $output->domain = null;
            $output->logoUrl = null;
            $output->contactEmail = null;
            $output->contactPhone = null;
            $output->address = null;
            $output->socialLinks = [];
        }

        return $output;
    }
}
