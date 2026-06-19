<?php

namespace App\Service\Media;

use App\Entity\Boutique;
use App\Enum\MediaType;
use App\Repository\MediaRepository;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final readonly class MediaCacheService
{
    private const int TTL = 21600; // 6h
    private const string PREFIX = 'media.';

    public function __construct(
        private CacheInterface $cache,
        private MediaRepository $media,
    ) {
    }

    /** @return array<int, array{id: string, type: string, url: string, thumbnailUrl: ?string, altText: ?string}> */
    public function getMediaList(Boutique $boutique, ?MediaType $type = null): array
    {
        $key = self::PREFIX."list.{$boutique->getId()}";
        if (null !== $type) {
            $key .= ".{$type->value}";
        }

        return $this->cache->get($key, function (ItemInterface $item) use ($boutique, $type): array {
            $item->expiresAfter(self::TTL);

            $items = null !== $type
                ? $this->media->findByBoutiqueAndType($boutique, $type)
                : $this->media->findByBoutique($boutique);

            return array_map(fn ($m) => [
                'id' => (string) $m->getId(),
                'type' => $m->getType()->value,
                'url' => $m->getUrl(),
                'thumbnailUrl' => $m->getThumbnailUrl(),
                'altText' => $m->getAltText(),
            ], $items);
        });
    }

    public function getMediaUrl(string $mediaId): ?string
    {
        $key = self::PREFIX."url.{$mediaId}";

        return $this->cache->get($key, function (ItemInterface $item) use ($mediaId): ?string {
            $media = $this->media->find($mediaId);
            if (null === $media) {
                return null;
            }
            $item->expiresAfter(self::TTL);

            return $media->getUrl();
        });
    }

    public function invalidate(Boutique $boutique): void
    {
        $this->cache->delete(self::PREFIX."list.{$boutique->getId()}");
        $this->cache->delete(self::PREFIX."list.{$boutique->getId()}.".MediaType::Image->value);
        $this->cache->delete(self::PREFIX."list.{$boutique->getId()}.".MediaType::Video->value);
        $this->cache->delete(self::PREFIX."list.{$boutique->getId()}.".MediaType::Document->value);
        $this->cache->delete(self::PREFIX."list.{$boutique->getId()}.".MediaType::Audio->value);
    }

    public function invalidateUrl(string $mediaId): void
    {
        $this->cache->delete(self::PREFIX."url.{$mediaId}");
    }
}
