<?php

namespace App\State\Boutique;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Boutique\AnnouncementInput;
use App\Dto\Boutique\AnnouncementOutput;
use App\Entity\Announcement;
use App\Entity\Boutique;
use App\Entity\Media;
use App\Repository\AnnouncementRepository;
use App\Repository\BoutiqueRepository;
use App\Repository\MediaRepository;
use App\Security\BoutiqueContext;
use App\Service\FrontOfficeCacheService;
use App\State\Common\BoutiqueAwareProviderTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/** @implements ProcessorInterface<AnnouncementOutput|null> */
final readonly class AnnouncementProcessor implements ProcessorInterface
{
    use BoutiqueAwareProviderTrait;

    public function __construct(
        private BoutiqueRepository $boutiques,
        private AnnouncementRepository $announcements,
        private MediaRepository $media,
        private EntityManagerInterface $em,
        private BoutiqueContext $context,
        private FrontOfficeCacheService $cache,
        private AnnouncementProvider $provider,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?AnnouncementOutput
    {
        if ($operation instanceof Delete) {
            $a = $this->announcements->find($uriVariables['id'] ?? '');
            if ($a instanceof Announcement) {
                $this->denyUnlessCanManage($a);
                $this->invalidateAffectedBoutiques($a->getBoutique(), $a->isGlobal(), $a->getPosition());
                $this->em->remove($a);
                $this->em->flush();
            }

            return null;
        }

        assert($data instanceof AnnouncementInput);

        $aId = $uriVariables['id'] ?? null;
        $a = $aId ? $this->announcements->find($aId) : null;
        $boutique = $this->resolveTargetBoutique($a, $data, $context);
        $previousPosition = $a?->getPosition();
        $image = $this->resolveImage($boutique, $data->imageId, $data->isGlobal);
        $displayType = $data->type ?? $data->displayType;
        $description = $data->description ?? $data->content;
        $buttonUrl = $data->buttonUrl ?? $data->linkUrl;

        if (!$a instanceof Announcement) {
            $a = new Announcement(
                boutique: $boutique,
                content: $description,
                displayType: $displayType,
                title: $data->title,
                subtitle: $data->subtitle,
                backgroundColor: $data->backgroundColor,
                textColor: $data->textColor,
                borderColor: $data->borderColor,
                buttonColor: $data->buttonColor,
                icon: $data->icon,
                image: $image,
                buttonText: $data->buttonText,
                linkUrl: $buttonUrl,
                priority: $data->priority,
                isDismissible: $data->isDismissible,
                displayMode: $data->displayMode,
                position: $data->position,
                displayPages: $data->displayPages,
                targetCategoryIds: $data->categoryIds,
                targetProductIds: $data->productIds,
                settings: $data->settings,
                active: $data->active,
                isGlobal: $data->isGlobal,
                startsAt: $data->startsAt ? new \DateTimeImmutable($data->startsAt) : null,
                endsAt: $data->endsAt ? new \DateTimeImmutable($data->endsAt) : null,
            );
            $this->em->persist($a);
        } else {
            $a->setContent($description);
            $a->setDisplayType($displayType);
            $a->setTitle($data->title);
            $a->setSubtitle($data->subtitle);
            $a->setBackgroundColor($data->backgroundColor);
            $a->setTextColor($data->textColor);
            $a->setBorderColor($data->borderColor);
            $a->setButtonColor($data->buttonColor);
            $a->setIcon($data->icon);
            $a->setImage($image);
            $a->setButtonText($data->buttonText);
            $a->setLinkUrl($buttonUrl);
            $a->setPriority($data->priority);
            $a->setIsDismissible($data->isDismissible);
            $a->setDisplayMode($data->displayMode);
            $a->setPosition($data->position);
            $a->setDisplayPages($data->displayPages);
            $a->setTargetCategoryIds($data->categoryIds);
            $a->setTargetProductIds($data->productIds);
            $a->setSettings($data->settings);
            $a->setActive($data->active);
            $a->setIsGlobal($data->isGlobal);
            $a->setStartsAt($data->startsAt ? new \DateTimeImmutable($data->startsAt) : null);
            $a->setEndsAt($data->endsAt ? new \DateTimeImmutable($data->endsAt) : null);
        }

        $this->em->flush();

        if (null !== $previousPosition && $previousPosition !== $a->getPosition()) {
            $this->invalidateAffectedBoutiques($a->getBoutique(), $a->isGlobal(), $previousPosition);
        }
        $this->invalidateAffectedBoutiques($a->getBoutique(), $a->isGlobal(), $a->getPosition());

        return $this->provider->toOutput($a);
    }

    /** @param array<string, mixed> $context */
    private function resolveTargetBoutique(?Announcement $announcement, AnnouncementInput $data, array $context): ?Boutique
    {
        if ($announcement instanceof Announcement) {
            $this->denyUnlessCanManage($announcement);
            if ($announcement->isGlobal() !== $data->isGlobal) {
                throw new AccessDeniedHttpException('Announcement scope cannot be changed');
            }
        }

        if ($data->isGlobal) {
            if (!$this->context->isSuperAdmin()) {
                throw new AccessDeniedHttpException('Only super admins can manage global announcements');
            }

            return null;
        }

        if ($announcement instanceof Announcement && $announcement->getBoutique() instanceof Boutique) {
            $requestBoutique = $this->resolveBoutiqueFromRequest($context);
            if ($requestBoutique instanceof Boutique && (string) $requestBoutique->getId() !== (string) $announcement->getBoutique()->getId()) {
                throw new AccessDeniedHttpException('Access denied');
            }

            return $announcement->getBoutique();
        }

        $boutique = $this->resolveBoutiqueFromRequest($context);
        if (!$boutique instanceof Boutique) {
            throw new AccessDeniedHttpException('Boutique context is required');
        }
        if (!$this->context->canAccessBoutique($boutique)) {
            throw new AccessDeniedHttpException('Access denied');
        }

        return $boutique;
    }

    private function denyUnlessCanManage(Announcement $announcement): void
    {
        if ($announcement->isGlobal()) {
            if (!$this->context->isSuperAdmin()) {
                throw new AccessDeniedHttpException('Access denied');
            }

            return;
        }

        $boutique = $announcement->getBoutique();
        if (!$boutique instanceof Boutique || !$this->context->canAccessBoutique($boutique)) {
            throw new AccessDeniedHttpException('Access denied');
        }
    }

    private function invalidateAffectedBoutiques(?Boutique $boutique, bool $isGlobal, string $position): void
    {
        $invalidateHomepage = in_array($position, [Announcement::POSITION_HOME_TOP, Announcement::POSITION_HOME_MIDDLE, Announcement::POSITION_HOME_BOTTOM], true);

        if ($isGlobal) {
            foreach ($this->boutiques->findAll() as $shop) {
                $shopId = (string) $shop->getId();
                $this->cache->invalidateAnnouncements($shopId);
                if ($invalidateHomepage) {
                    $this->cache->invalidateHomepage($shopId);
                }
            }

            return;
        }

        if (null === $boutique) {
            return;
        }

        $shopId = (string) $boutique->getId();
        $this->cache->invalidateAnnouncements($shopId);
        if ($invalidateHomepage) {
            $this->cache->invalidateHomepage($shopId);
        }
    }

    private function resolveImage(?Boutique $boutique, ?string $imageId, bool $isGlobal): ?Media
    {
        if (null === $imageId || '' === $imageId) {
            return null;
        }

        $image = $this->media->find($imageId);
        if (!$image instanceof Media) {
            return null;
        }

        if ($isGlobal) {
            return $image;
        }

        if (null !== $boutique && (string) $image->getBoutique()->getId() === (string) $boutique->getId()) {
            return $image;
        }

        return null;
    }
}
