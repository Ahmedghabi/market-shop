<?php

namespace App\State\Review;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Review\ReviewInput;
use App\Dto\Review\ReviewOutput;
use App\Entity\Review;
use App\Entity\User;
use App\Repository\BoutiqueRepository;
use App\Repository\OrderItemRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final readonly class ReviewProcessor implements ProcessorInterface
{
    public function __construct(
        private ReviewRepository $reviews,
        private BoutiqueRepository $boutiques,
        private ProductRepository $products,
        private OrderRepository $orders,
        private OrderItemRepository $orderItems,
        private EntityManagerInterface $em,
        private Security $security,
        private RequestStack $requestStack,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?ReviewOutput
    {
        $reviewId = $uriVariables['id'] ?? null;

        if (null !== $reviewId) {
            $review = $this->reviews->find($reviewId);
            if (!$review) {
                return null;
            }

            if ('approve_review' === $operation->getName()) {
                $review->approve();
            } elseif ('reject_review' === $operation->getName()) {
                $review->reject();
            }

            $this->em->flush();

            return $this->toOutput($review);
        }

        if (!$data instanceof ReviewInput) {
            return null;
        }

        $boutique = $this->boutiques->findBySlugOrId($uriVariables['boutiqueId'] ?? $data->boutiqueId ?? '');
        if (!$boutique && $data->boutiqueId) {
            $boutique = $this->boutiques->findBySlugOrId($data->boutiqueId);
        }

        $product = null;
        if ($data->productId || isset($uriVariables['productId'])) {
            $productId = $uriVariables['productId'] ?? $data->productId;
            $product = $this->products->findBySlugOrId($productId, $boutique);
        }

        $user = $this->security->getUser();
        $user = $user instanceof User ? $user : null;
        $authorName = trim((string) ($data->authorName ?? ''));
        if ('' === $authorName) {
            $authorName = trim(sprintf('%s %s', (string) $user?->getFirstname(), (string) $user?->getLastname()));
            $authorName = '' === $authorName ? ($user?->getDisplayName() ?: 'Guest') : $authorName;
        }

        $ipHash = $this->ipHash();
        if (null !== $ipHash) {
            if ($this->reviews->countRecentByIpHash($ipHash) >= 3) {
                throw new BadRequestHttpException('Too many reviews from this IP. Please try later.');
            }
            if ($product && $this->reviews->existsForIpAndProduct($ipHash, $product)) {
                throw new BadRequestHttpException('A review for this product was already submitted from this IP.');
            }
            if (!$product && $boutique && $this->reviews->existsForIpAndBoutique($ipHash, $boutique)) {
                throw new BadRequestHttpException('A review for this shop was already submitted from this IP.');
            }
        }
        if (!$user && $data->authorEmail) {
            if ($product && $this->reviews->existsRecentGuestEmailForProduct($data->authorEmail, $product)) {
                throw new BadRequestHttpException('A review was already submitted recently with this email for this product.');
            }
            if (!$product && $boutique && $this->reviews->existsRecentGuestEmailForBoutique($data->authorEmail, $boutique)) {
                throw new BadRequestHttpException('A review was already submitted recently with this email for this shop.');
            }
        }

        $review = new Review(
            boutique: $boutique,
            product: $product,
            authorName: $authorName,
            rating: $data->rating,
            comment: $data->comment,
        );

        $review->setUser($user);
        $review->setTitle($data->title);
        $review->setAuthorPhone($data->authorPhone);
        $review->setImages($data->images);
        $review->setIpHash($ipHash);
        $review->setVerifiedPurchase($this->isVerifiedPurchase($user, $boutique, $product));

        if ($data->authorEmail) {
            $review->setAuthorEmail($data->authorEmail);
        }

        $this->em->persist($review);
        $this->em->flush();

        return $this->toOutput($review);
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

    private function isVerifiedPurchase(?User $user, ?\App\Entity\Boutique $boutique, ?\App\Entity\Product $product): bool
    {
        if (!$user) {
            return false;
        }
        if ($product) {
            return $this->orderItems->hasPurchasedProduct($user, $product);
        }
        if ($boutique) {
            return $this->orders->hasOrdersByUserForBoutique($user, $boutique);
        }

        return false;
    }

    private function ipHash(): ?string
    {
        $ip = $this->requestStack->getCurrentRequest()?->getClientIp();

        return $ip ? hash('sha256', $ip) : null;
    }
}
