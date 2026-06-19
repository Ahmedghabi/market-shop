<?php

namespace App\State\Promotion;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Promotion\PromotionOutput;
use App\Entity\Promotion;
use App\Repository\BoutiqueRepository;
use App\Repository\PromotionRepository;
use App\Security\BoutiqueContext;
use App\Service\Marketing\MarketingCacheService;
use Symfony\Bundle\SecurityBundle\Security;

/** @implements ProviderInterface<PromotionOutput> */
final readonly class PromotionProvider implements ProviderInterface
{
    public function __construct(
        private PromotionRepository $promotions,
        private BoutiqueRepository $boutiques,
        private BoutiqueContext $context,
        private Security $security,
        private MarketingCacheService $cache,
    ) {
    }

    /** @return list<PromotionOutput>|PromotionOutput|null */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|PromotionOutput|null
    {
        unset($context);

        $boutique = $this->boutiques->findBySlugOrId((string) ($uriVariables['boutiqueId'] ?? ''));
        if (!$boutique) {
            return $operation instanceof Get ? null : [];
        }

        $canManage = null !== $this->security->getUser() && $this->context->canAccessBoutique($boutique);

        if ($operation instanceof Get) {
            $promotion = $this->promotions->find((string) ($uriVariables['id'] ?? ''));

            return $promotion instanceof Promotion && (string) $promotion->getBoutique()->getId() === (string) $boutique->getId() && ($canManage || $promotion->isCurrentlyActive())
                ? $this->toOutput($promotion)
                : null;
        }

        if ($canManage) {
            return array_map([$this, 'toOutput'], $this->promotions->findByBoutique($boutique));
        }

        return array_map([$this, 'fromCachedArray'], $this->cache->getPromotions((string) $boutique->getId()));
    }

    private function toOutput(Promotion $promotion): PromotionOutput
    {
        $output = new PromotionOutput();
        $output->id = (string) $promotion->getId();
        $output->boutiqueId = (string) $promotion->getBoutique()->getId();
        $output->name = $promotion->getName();
        $output->description = $promotion->getDescription();
        $output->scope = $promotion->getScope()->value;
        $output->type = $promotion->getType()->value;
        $output->value = $promotion->getValue();
        $output->priority = $promotion->getPriority();
        $output->categoryIds = array_map(fn ($pc) => (string) $pc->getCategory()->getId(), $promotion->getCategories()->toArray());
        $output->productIds = array_map(fn ($pp) => (string) $pp->getProduct()->getId(), $promotion->getProducts()->toArray());
        $output->startsAt = $promotion->getStartsAt()->format('c');
        $output->endsAt = $promotion->getEndsAt()?->format('c');
        $output->active = $promotion->isActive();
        $output->currentlyActive = $promotion->isCurrentlyActive();
        $output->createdAt = $promotion->getCreatedAt();
        $output->updatedAt = $promotion->getUpdatedAt();

        return $output;
    }

    /** @param array<string, mixed> $promotion */
    private function fromCachedArray(array $promotion): PromotionOutput
    {
        $output = new PromotionOutput();
        $output->id = (string) $promotion['id'];
        $output->boutiqueId = (string) $promotion['boutiqueId'];
        $output->name = (string) $promotion['name'];
        $output->description = $promotion['description'] ? (string) $promotion['description'] : null;
        $output->scope = (string) $promotion['scope'];
        $output->type = (string) $promotion['type'];
        $output->value = (int) $promotion['value'];
        $output->priority = (int) $promotion['priority'];
        $output->categoryIds = array_map('strval', $promotion['categoryIds'] ?? []);
        $output->productIds = array_map('strval', $promotion['productIds'] ?? []);
        $output->startsAt = (string) $promotion['startsAt'];
        $output->endsAt = isset($promotion['endsAt']) ? (string) $promotion['endsAt'] : null;
        $output->active = (bool) $promotion['active'];
        $output->currentlyActive = (bool) $promotion['currentlyActive'];
        $output->createdAt = new \DateTimeImmutable();
        $output->updatedAt = null;

        return $output;
    }
}
