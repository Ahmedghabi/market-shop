<?php

namespace App\State\Promotion;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Promotion\PromotionInput;
use App\Dto\Promotion\PromotionOutput;
use App\Entity\Promotion;
use App\Entity\PromotionCategory;
use App\Entity\PromotionProduct;
use App\Enum\PromotionScope;
use App\Enum\PromotionType;
use App\Repository\BoutiqueRepository;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\PromotionRepository;
use App\Security\BoutiqueContext;
use App\Service\Marketing\MarketingCacheService;
use App\State\Common\BoutiqueWriteResolverTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProcessorInterface<PromotionOutput|null> */
final readonly class PromotionProcessor implements ProcessorInterface
{
    use BoutiqueWriteResolverTrait;

    public function __construct(
        private BoutiqueRepository $boutiques,
        private PromotionRepository $promotions,
        private CategoryRepository $categories,
        private ProductRepository $products,
        private EntityManagerInterface $em,
        private BoutiqueContext $context,
        private PromotionProvider $provider,
        private MarketingCacheService $cache,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?PromotionOutput
    {
        $boutique = $this->resolveBoutiqueForWrite($data, $uriVariables, $context);

        if ($operation instanceof Delete) {
            $promotion = $this->findPromotion($boutique, (string) ($uriVariables['id'] ?? ''));
            $this->em->remove($promotion);
            $this->em->flush();
            $this->cache->invalidatePromotions((string) $boutique->getId());

            return null;
        }

        assert($data instanceof PromotionInput);
        $promotion = isset($uriVariables['id'])
            ? $this->findPromotion($boutique, (string) $uriVariables['id'])
            : new Promotion(
                boutique: $boutique,
                name: $data->name,
                description: $data->description,
                scope: PromotionScope::tryFrom($data->scope) ?? PromotionScope::Global,
                type: PromotionType::tryFrom($data->type) ?? PromotionType::Percentage,
                value: $data->value,
                priority: $data->priority,
                startsAt: $data->startsAt ? new \DateTimeImmutable($data->startsAt) : new \DateTimeImmutable(),
                endsAt: $data->endsAt ? new \DateTimeImmutable($data->endsAt) : null,
                active: $data->active,
            );

        if (!isset($uriVariables['id'])) {
            $this->em->persist($promotion);
        }

        $promotion->setName($data->name);
        $promotion->setDescription($data->description);
        $promotion->setScope(PromotionScope::tryFrom($data->scope) ?? PromotionScope::Global);
        $promotion->setType(PromotionType::tryFrom($data->type) ?? PromotionType::Percentage);
        $promotion->setValue($data->value);
        $promotion->setPriority($data->priority);
        $promotion->setStartsAt($data->startsAt ? new \DateTimeImmutable($data->startsAt) : $promotion->getStartsAt());
        $promotion->setEndsAt($data->endsAt ? new \DateTimeImmutable($data->endsAt) : null);
        $promotion->setActive($data->active);

        $this->syncTargets($promotion, $boutique, $data);

        $this->em->flush();
        $this->cache->invalidatePromotions((string) $boutique->getId());

        return $this->provider->provide(new Get(), ['boutiqueId' => (string) $boutique->getId(), 'id' => (string) $promotion->getId()]);
    }

    private function syncTargets(Promotion $promotion, \App\Entity\Boutique $boutique, PromotionInput $input): void
    {
        foreach ($promotion->getCategories()->toArray() as $promotionCategory) {
            $promotion->removeCategory($promotionCategory);
            $this->em->remove($promotionCategory);
        }
        foreach ($promotion->getProducts()->toArray() as $promotionProduct) {
            $promotion->removeProduct($promotionProduct);
            $this->em->remove($promotionProduct);
        }

        $scope = PromotionScope::tryFrom($input->scope) ?? PromotionScope::Global;
        if (PromotionScope::Global === $scope) {
            return;
        }

        if (PromotionScope::Category === $scope && [] === $input->categoryIds) {
            throw new BadRequestHttpException('Sélectionnez une catégorie pour cette promotion.');
        }

        if (PromotionScope::Product === $scope && [] === $input->productIds) {
            throw new BadRequestHttpException('Sélectionnez un produit pour cette promotion.');
        }

        if (PromotionScope::Category === $scope && [] !== $input->productIds) {
            throw new BadRequestHttpException('Une promotion par catégorie ne peut pas cibler un produit.');
        }

        if (PromotionScope::Product === $scope && [] !== $input->categoryIds) {
            throw new BadRequestHttpException('Une promotion par produit ne peut pas cibler une catégorie.');
        }

        foreach ($input->categoryIds as $categoryId) {
            $category = $this->categories->find($categoryId);
            if (!$category || (string) $category->getBoutique()->getId() !== (string) $boutique->getId()) {
                throw new BadRequestHttpException('La catégorie sélectionnée n’appartient pas à cette boutique.');
            }
            $promotionCategory = new PromotionCategory($promotion, $category);
            $promotion->addCategory($promotionCategory);
            $this->em->persist($promotionCategory);
        }

        foreach ($input->productIds as $productId) {
            $product = $this->products->find($productId);
            if (!$product || (string) $product->getBoutique()->getId() !== (string) $boutique->getId()) {
                throw new BadRequestHttpException('Le produit sélectionné n’appartient pas à cette boutique.');
            }
            $promotionProduct = new PromotionProduct($promotion, $product);
            $promotion->addProduct($promotionProduct);
            $this->em->persist($promotionProduct);
        }
    }

    private function findPromotion(\App\Entity\Boutique $boutique, string $id): Promotion
    {
        $promotion = $this->promotions->find($id);
        if (!$promotion instanceof Promotion || (string) $promotion->getBoutique()->getId() !== (string) $boutique->getId()) {
            throw new NotFoundHttpException('Promotion not found');
        }

        return $promotion;
    }
}
