<?php

namespace App\Service;

use App\Entity\Boutique;

final class SeoService
{
    public function defaultMetaTitle(string $name, string $shopName): string
    {
        return $name.' | '.$shopName;
    }

    public function defaultMetaDescription(?string $description, string $fallback): string
    {
        $source = trim((string) ($description ?? ''));
        if ('' === $source) {
            $source = $fallback;
        }

        $source = preg_replace('/\s+/', ' ', $source) ?? $source;

        return mb_substr(trim($source), 0, 160);
    }

    public function defaultOgImage(?string ...$candidates): ?string
    {
        foreach ($candidates as $candidate) {
            if (null !== $candidate && '' !== trim($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    public function canonicalBaseUrl(Boutique $boutique): string
    {
        $domain = trim((string) $boutique->getSettings()?->getDomain());
        if ('' !== $domain) {
            if (!str_starts_with($domain, 'http://') && !str_starts_with($domain, 'https://')) {
                $domain = 'https://'.$domain;
            }

            return rtrim($domain, '/');
        }

        return '/boutiques/'.trim($boutique->getSlug(), '/');
    }

    public function canonicalUrl(Boutique $boutique, string $path = ''): string
    {
        $baseUrl = $this->canonicalBaseUrl($boutique);
        $normalizedPath = trim($path, '/');

        return '' === $normalizedPath ? $baseUrl : $baseUrl.'/'.$normalizedPath;
    }

    /** @return array<string, mixed> */
    public function buildShopSeo(Boutique $boutique): array
    {
        $settings = $boutique->getSettings();
        $seoConfig = $settings?->getSeoConfig() ?? [];
        $shopName = $boutique->getName();
        $description = $settings?->getDescription() ?? $settings?->getSlogan() ?? $shopName;

        $defaults = [
            'meta_title' => $shopName,
            'meta_description' => $this->defaultMetaDescription($description, $shopName),
            'meta_keywords' => $shopName,
            'og_title' => $shopName,
            'og_description' => $this->defaultMetaDescription($description, $shopName),
            'og_image' => $this->defaultOgImage($settings?->getCoverImage(), $settings?->getLogoUrl()),
            'canonical_url' => $this->canonicalUrl($boutique),
            'robots_txt' => $this->defaultRobotsTxt($boutique),
        ];

        foreach ($defaults as $key => $value) {
            if (!array_key_exists($key, $seoConfig) || null === $seoConfig[$key] || '' === trim((string) $seoConfig[$key])) {
                $seoConfig[$key] = $value;
            }
        }

        return $seoConfig;
    }

    public function defaultRobotsTxt(Boutique $boutique): string
    {
        return "User-agent: *\nAllow: /\n\nSitemap: ".$this->publicSeoRoute($boutique, 'sitemap.xml');
    }

    public function publicSeoRoute(Boutique $boutique, string $suffix = ''): string
    {
        $path = '/api/boutiques/'.$boutique->getSlug().'/seo';
        if ('' !== $suffix) {
            $path .= '/'.ltrim($suffix, '/');
        }

        return $path;
    }
}
