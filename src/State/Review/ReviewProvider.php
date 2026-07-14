<?php

namespace App\State\Review;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Review\ReviewOutput;
use App\Entity\Review;
use App\Repository\ProductRepository;
use App\Repository\ReviewRepository;
use App\Security\BoutiqueContext;
use App\Service\Module\ModuleAccessService;
use App\State\Common\BoutiqueAwareProviderTrait;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/** @implements ProviderInterface<ReviewOutput> */
final readonly class ReviewProvider implements ProviderInterface
{
    use BoutiqueAwareProviderTrait;

    public function __construct(
        private ReviewRepository $reviews,
        private ProductRepository $products,
        private BoutiqueContext $context,
        private TokenStorageInterface $tokenStorage,
        private Security $security,
        private ModuleAccessService $moduleAccess,
    ) {
    }

    /** @return list<ReviewOutput>|ReviewOutput|null */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|ReviewOutput|null
    {
        $reviewId = $uriVariables['id'] ?? null;

        if (null !== $reviewId) {
            $review = $this->reviews->find($reviewId);
            if (!$review) {
                return null;
            }

            $boutique = $review->getBoutique() ?? $review->getProduct()?->getBoutique();
            if ($boutique && !$this->context->canAccessBoutique($boutique)) {
                return null;
            }

            return $this->toOutput($review);
        }

        $boutiqueId = $uriVariables['boutiqueId'] ?? null;
        $productId = $uriVariables['productId'] ?? null;

        if (null === $boutiqueId && 'platform_reviews' === $operation->getName()) {
            $entities = $this->security->isGranted('ROLE_SUPER_ADMIN')
                ? $this->reviews->findPlatformReviewsForAdmin()
                : $this->reviews->findApprovedPlatformReviews();

            return array_map([$this, 'toOutput'], $entities);
        }

        $boutique = $this->resolveBoutiqueFromRequest($context, $uriVariables);

        if (!$boutique) {
            return $operation instanceof GetCollection ? [] : null;
        }

        $token = $this->tokenStorage->getToken();
        $isAuthenticated = null !== $token && $token->getUser();
        $isAdmin = $isAuthenticated && $this->context->canAccessBoutique($boutique);

        if (!$isAdmin && !$this->moduleAccess->isModuleEnabled('reviews', $boutique)) {
            return [];
        }

        if (null !== $productId) {
            $product = $this->products->findBySlugOrId($productId, $boutique);
            if (!$product) {
                return [];
            }

            $entities = $isAdmin
                ? $this->reviews->findBy(['product' => $product], ['createdAt' => 'DESC'])
                : $this->reviews->findApprovedByProduct($product);
        } else {
            $entities = $isAdmin
                ? $this->reviews->findByBoutiqueForAdmin($boutique)
                : $this->reviews->findApprovedByBoutique($boutique);
        }

        return array_map([$this, 'toOutput'], $entities);
    }

    private function toOutput(Review $entity): ReviewOutput
    {
        $output = new ReviewOutput();
        $output->id = (string) $entity->getId();
        $output->boutiqueId = null !== $entity->getBoutique() ? (string) $entity->getBoutique()->getId() : null;
        $output->productId = null !== $entity->getProduct() ? (string) $entity->getProduct()->getId() : null;
        $output->userId = null !== $entity->getUser() ? (string) $entity->getUser()->getId() : null;
        $output->authorName = $entity->getAuthorName();
        $output->authorEmail = $entity->getAuthorEmail();
        $output->authorPhone = $entity->getAuthorPhone();
        $output->rating = $entity->getRating();
        $output->title = $entity->getTitle();
        $output->comment = $entity->getComment();
        $output->images = $entity->getImages();
        $output->isVerifiedPurchase = $entity->isVerifiedPurchase();
        $output->status = $entity->getStatus()->value;
        $output->createdAt = $entity->getCreatedAt();

        return $output;
    }
}
