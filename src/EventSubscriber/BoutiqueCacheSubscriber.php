<?php

namespace App\EventSubscriber;

use App\Entity\Boutique;
use App\Factory\RedisFactory;
use App\Service\Boutique\ShopContext;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(event: Events::postUpdate)]
#[AsDoctrineListener(event: Events::preRemove)]
final readonly class BoutiqueCacheSubscriber
{
    public function __construct(
        private RedisFactory $redisFactory,
    ) {
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof Boutique) {
            return;
        }

        $this->clearCacheForSlug($entity->getSlug());
    }

    public function preRemove(PreRemoveEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof Boutique) {
            return;
        }

        $this->clearCacheForSlug($entity->getSlug());
    }

    private function clearCacheForSlug(string $slug): void
    {
        $redis = $this->redisFactory->create();
        if (null === $redis) {
            return;
        }

        $redis->del(ShopContext::CACHE_KEY_PREFIX.'.'.$slug);
    }
}
