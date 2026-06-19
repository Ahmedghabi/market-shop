<?php

namespace App\Service;

use App\Entity\Boutique;
use App\Repository\AnnouncementRepository;
use App\Repository\BoutiqueRepository;
use App\Repository\CategoryRepository;
use App\Repository\CmsPageRepository;
use App\Repository\MenuRepository;
use App\Repository\ShopPaymentMethodRepository;
use App\Repository\ProductRepository;
use App\Repository\ThemeRepository;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final readonly class FrontOfficeCacheService
{
    private const int TTL = 21600; // 6h

    public function __construct(
        private CacheInterface $cache,
        private BoutiqueRepository $boutiques,
        private MenuRepository $menus,
        private ThemeRepository $themes,
        private AnnouncementRepository $announcements,
        private CategoryRepository $categories,
        private ProductRepository $products,
        private CmsPageRepository $pages,
        private ShopPaymentMethodRepository $shopPaymentMethods,
        private SeoService $seo,
    ) {
    }

    public function getSettings(string $boutiqueId): ?array
    {
        return $this->cache->get("shop.{$boutiqueId}.settings", function (ItemInterface $item) use ($boutiqueId): ?array {
            $boutique = $this->boutiques->find($boutiqueId);
            if (!$boutique || !$boutique->getSettings()) {
                return null;
            }
            $item->expiresAfter(self::TTL);

            return $this->serializeSettings($boutique);
        });
    }

    /** @return list<array> */
    public function getMenus(string $boutiqueId): array
    {
        return $this->cache->get("shop.{$boutiqueId}.menus", function (ItemInterface $item) use ($boutiqueId): array {
            $item->expiresAfter(self::TTL);
            $boutique = $this->boutiques->find($boutiqueId);
            if (!$boutique) {
                return [];
            }
            $menus = $this->menus->findActiveByBoutique($boutique);

            return array_map(fn ($m) => [
                'id' => (string) $m->getId(),
                'name' => $m->getName(),
                'position' => $m->getPosition(),
                'items' => array_map(fn ($i) => [
                    'id' => (string) $i->getId(),
                    'title' => $i->getTitle(),
                    'type' => $i->getType(),
                    'target' => $i->getTarget(),
                    'parentId' => $i->getParent()?->getId()?->toRfc4122(),
                    'position' => $i->getPosition(),
                    'isActive' => $i->isActive(),
                ], $m->getItems()->toArray()),
            ], $menus);
        });
    }

    public function getTheme(string $boutiqueId): ?array
    {
        return $this->cache->get("shop.{$boutiqueId}.theme", function (ItemInterface $item) use ($boutiqueId): ?array {
            $item->expiresAfter(self::TTL);
            $boutique = $this->boutiques->find($boutiqueId);
            $themeCode = $boutique?->getSettings()?->getTheme();

            $theme = $themeCode ? $this->themes->findOneByCode($themeCode) : null;
            if (null === $theme || !$theme->isActive()) {
                $theme = $this->themes->findDefault();
            }
            if (null === $theme || !$theme->isActive()) {
                return null;
            }

            return [
                'id' => (string) $theme->getId(),
                'name' => $theme->getName(),
                'code' => $theme->getCode(),
                'previewImage' => $theme->getPreviewImage(),
                'isActive' => $theme->isActive(),
                'isDefault' => $theme->isDefault(),
            ];
        });
    }

    public function getHomepage(string $boutiqueId): array
    {
        return $this->cache->get("shop.{$boutiqueId}.homepage", function (ItemInterface $item) use ($boutiqueId): array {
            $item->expiresAfter(self::TTL);
            $boutique = $this->boutiques->find($boutiqueId);
            $settings = $boutique?->getSettings();
            if (null === $settings) {
                return [];
            }

            return [
                'sections' => $settings->getHomepageSections(),
                'banners' => $settings->getBanners(),
                'featuredCategories' => $settings->getFeaturedCategories(),
            ];
        });
    }

    /** @return list<array<string, mixed>> */
    public function getAnnouncements(string $boutiqueId): array
    {
        return $this->cache->get("shop.{$boutiqueId}.announcements", function (ItemInterface $item) use ($boutiqueId): array {
            $item->expiresAfter(self::TTL);

            return array_map(fn ($announcement) => [
                'id' => (string) $announcement->getId(),
                'boutiqueId' => $announcement->getBoutique()?->getId()?->toRfc4122(),
                'content' => $announcement->getContent(),
                'description' => $announcement->getContent(),
                'displayType' => $announcement->getDisplayType(),
                'type' => $announcement->getDisplayType(),
                'title' => $announcement->getTitle(),
                'subtitle' => $announcement->getSubtitle(),
                'backgroundColor' => $announcement->getBackgroundColor(),
                'textColor' => $announcement->getTextColor(),
                'borderColor' => $announcement->getBorderColor(),
                'buttonColor' => $announcement->getButtonColor(),
                'icon' => $announcement->getIcon(),
                'imageId' => $announcement->getImage()?->getId()?->toRfc4122(),
                'buttonText' => $announcement->getButtonText(),
                'linkUrl' => $announcement->getLinkUrl(),
                'buttonUrl' => $announcement->getLinkUrl(),
                'priority' => $announcement->getPriority(),
                'isDismissible' => $announcement->isDismissible(),
                'displayMode' => $announcement->getDisplayMode(),
                'position' => $announcement->getPosition(),
                'displayPages' => $announcement->getDisplayPages(),
                'categoryIds' => $announcement->getTargetCategoryIds(),
                'productIds' => $announcement->getTargetProductIds(),
                'settings' => $announcement->getSettings(),
                'active' => $announcement->isActive(),
                'isGlobal' => $announcement->isGlobal(),
                'visible' => $announcement->isVisible(),
                'viewsCount' => $announcement->getViewsCount(),
                'clicksCount' => $announcement->getClicksCount(),
                'conversionCount' => $announcement->getConversionCount(),
                'startsAt' => $announcement->getStartsAt()?->format('c'),
                'endsAt' => $announcement->getEndsAt()?->format('c'),
                'createdAt' => $announcement->getCreatedAt()->format('c'),
                'updatedAt' => $announcement->getUpdatedAt()?->format('c'),
            ], $this->announcements->findVisibleForStorefront($boutiqueId));
        });
    }

    /** @return array<string, mixed>|null */
    public function getSeo(string $boutiqueId): ?array
    {
        return $this->cache->get("shop.{$boutiqueId}.seo", function (ItemInterface $item) use ($boutiqueId): ?array {
            $item->expiresAfter(self::TTL);
            $boutique = $this->boutiques->find($boutiqueId);
            if (!$boutique) {
                return null;
            }

            $seo = $this->seo->buildShopSeo($boutique);

            return [
                ...$seo,
                'sitemap_url' => $this->seo->publicSeoRoute($boutique, 'sitemap.xml'),
                'robots_url' => $this->seo->publicSeoRoute($boutique, 'robots.txt'),
                'analytics' => [
                    'indexedProducts' => $this->products->countSeoIndexedByBoutique($boutique),
                    'indexedCategories' => $this->categories->countSeoIndexedByBoutique($boutique),
                    'indexedPages' => $this->pages->countPublishedByBoutique($boutique),
                    'topPages' => array_slice(array_values(array_filter([
                        ['type' => 'shop', 'title' => $boutique->getName(), 'url' => $this->seo->canonicalUrl($boutique)],
                        ...array_map(fn ($category) => [
                            'type' => 'category',
                            'title' => $category->getMetaTitle() ?: $category->getName(),
                            'url' => $this->seo->canonicalUrl($boutique, $category->getSlug()),
                        ], $this->categories->findSeoIndexedByBoutique($boutique)),
                        ...array_map(fn ($product) => [
                            'type' => 'product',
                            'title' => $product->getMetaTitle() ?: $product->getName(),
                            'url' => $this->seo->canonicalUrl($boutique, $product->getSlug()),
                        ], array_slice($this->products->findSeoIndexedByBoutique($boutique), 0, 5)),
                        ...array_map(fn ($page) => [
                            'type' => 'cms',
                            'title' => $page->getMetaTitle() ?: $page->getTitle(),
                            'url' => $this->seo->canonicalUrl($boutique, $page->getSlug()),
                        ], array_slice($this->pages->findPublishedByBoutique($boutique), 0, 5)),
                    ])), 0, 10),
                ],
            ];
        });
    }

    /** @return list<array<string, mixed>> */
    public function getPaymentMethods(string $boutiqueId): array
    {
        return $this->cache->get("shop.{$boutiqueId}.payment_methods", function (ItemInterface $item) use ($boutiqueId): array {
            $item->expiresAfter(self::TTL);
            $boutique = $this->boutiques->find($boutiqueId);
            if (!$boutique) {
                return [];
            }

            return array_map(fn ($shopMethod) => [
                'id' => (string) $shopMethod->getId(),
                'paymentMethodId' => (string) $shopMethod->getPaymentMethod()->getId(),
                'name' => $shopMethod->getPaymentMethod()->getName(),
                'code' => $shopMethod->getPaymentMethod()->getCode(),
                'description' => $shopMethod->getPaymentMethod()->getDescription(),
                'logo' => $shopMethod->getPaymentMethod()->getLogo(),
                'type' => $shopMethod->getPaymentMethod()->getType()->value,
                'displayOrder' => $shopMethod->getDisplayOrder(),
                'minimumAmountCents' => $shopMethod->getMinimumAmountCents(),
                'maximumAmountCents' => $shopMethod->getMaximumAmountCents(),
                'isSandbox' => $shopMethod->isSandbox(),
                'gatewayConfig' => $shopMethod->getGatewayConfig(),
            ], $this->shopPaymentMethods->findStorefrontForBoutique($boutique));
        });
    }

    public function invalidateSettings(string $boutiqueId): void
    {
        $this->cache->delete("shop.{$boutiqueId}.settings");
    }

    public function invalidateTheme(string $boutiqueId): void
    {
        $this->cache->delete("shop.{$boutiqueId}.theme");
    }

    public function invalidateHomepage(string $boutiqueId): void
    {
        $this->cache->delete("shop.{$boutiqueId}.homepage");
    }

    public function invalidateMenus(string $boutiqueId): void
    {
        $this->cache->delete("shop.{$boutiqueId}.menus");
    }

    public function invalidateAnnouncements(string $boutiqueId): void
    {
        $this->cache->delete("shop.{$boutiqueId}.announcements");
    }

    public function invalidatePaymentMethods(string $boutiqueId): void
    {
        $this->cache->delete("shop.{$boutiqueId}.payment_methods");
    }

    public function invalidateSeo(string $boutiqueId): void
    {
        $this->cache->delete("shop.{$boutiqueId}.seo");
    }

    public function invalidateAll(string $boutiqueId): void
    {
        $this->invalidateSettings($boutiqueId);
        $this->invalidateTheme($boutiqueId);
        $this->invalidateHomepage($boutiqueId);
        $this->invalidateMenus($boutiqueId);
        $this->invalidateAnnouncements($boutiqueId);
        $this->invalidatePaymentMethods($boutiqueId);
        $this->invalidateSeo($boutiqueId);
    }

    private function serializeSettings(Boutique $boutique): array
    {
        $s = $boutique->getSettings();
        if (!$s) {
            return [];
        }

        return [
            'id' => (string) $s->getId(),
            'shopName' => $boutique->getName(),
            'logoUrl' => $s->getLogoUrl(),
            'slogan' => $s->getSlogan(),
            'favicon' => $s->getFavicon(),
            'coverImage' => $s->getCoverImage(),
            'description' => $s->getDescription(),
            'theme' => $s->getTheme(),
            'primaryColor' => $s->getPrimaryColor(),
            'secondaryColor' => $s->getSecondaryColor(),
            'accentColor' => $s->getColorPalette()['accent'] ?? null,
            'backgroundColor' => $s->getColorPalette()['background'] ?? null,
            'textColor' => $s->getColorPalette()['text'] ?? null,
            'colorPalette' => $s->getColorPalette(),
            'iconSet' => $s->getIconSet(),
            'fontFamily' => $s->getFontFamily(),
            'fontSize' => $s->getFontSize(),
            'borderRadius' => $s->getBorderRadius(),
            'orderMode' => $s->getOrderMode()->value,
            'maintenanceMode' => $s->isMaintenanceMode(),
            'maintenanceMessage' => $s->getMaintenanceMessage(),
            'socialLinks' => $s->getSocialLinks(),
            'facebookUrl' => $s->getSocialLinks()['facebook'] ?? null,
            'instagramUrl' => $s->getSocialLinks()['instagram'] ?? null,
            'tiktokUrl' => $s->getSocialLinks()['tiktok'] ?? null,
            'youtubeUrl' => $s->getSocialLinks()['youtube'] ?? null,
            'linkedinUrl' => $s->getSocialLinks()['linkedin'] ?? null,
            'xTwitterUrl' => $s->getSocialLinks()['x_twitter'] ?? null,
            'whatsappNumber' => $s->getSocialLinks()['whatsapp'] ?? null,
            'contactEmail' => $s->getContactEmail(),
            'contactPhone' => $s->getContactPhone(),
            'contactDetails' => $s->getContactDetails(),
            'seoConfig' => $this->seo->buildShopSeo($boutique),
            'headerConfig' => $s->getHeaderConfig(),
            'footerConfig' => $s->getFooterConfig(),
            'homepageSections' => $s->getHomepageSections(),
            'banners' => $s->getBanners(),
            'featuredCategories' => $s->getFeaturedCategories(),
            'frontOfficePages' => $s->getFrontOfficePages(),
            'navigationItems' => $s->getNavigationItems(),
            'catalogConfig' => $s->getCatalogConfig(),
            'moduleConfig' => $s->getModuleConfig(),
            'languageConfig' => $s->getLanguageConfig(),
        ];
    }
}
