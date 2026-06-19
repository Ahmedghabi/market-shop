<?php

namespace App\Service\Delivery;

use App\Factory\RedisFactory;

final readonly class DeliveryCacheService
{
    private ?\Redis $redis;

    public function __construct(
        private RedisFactory $redisFactory,
    ) {
        $this->redis = $this->redisFactory->create();
    }

    public function getShopRules(string $boutiqueId): ?array
    {
        if (!$this->redis) {
            return null;
        }

        $data = $this->redis->get("shop:{$boutiqueId}:delivery_rules");

        return null !== $data ? json_decode($data, true) : null;
    }

    public function setShopRules(string $boutiqueId, array $data, int $ttl = 21600): void
    {
        if (!$this->redis) {
            return;
        }

        $this->redis->setex("shop:{$boutiqueId}:delivery_rules", $ttl, json_encode($data));
    }

    public function invalidateShop(string $boutiqueId): void
    {
        if (!$this->redis) {
            return;
        }

        $keys = $this->redis->keys("shop:{$boutiqueId}:delivery*");
        if (!empty($keys)) {
            $this->redis->del(...$keys);
        }
    }
}
