<?php

namespace App\Service\Marketing;

use App\Factory\RedisFactory;

final readonly class CouponCacheService
{
    private ?\Redis $redis;

    public function __construct(
        private RedisFactory $redisFactory,
    ) {
        $this->redis = $this->redisFactory->create();
    }

    public function getShopCoupons(string $boutiqueId): ?array
    {
        if (!$this->redis) {
            return null;
        }

        $data = $this->redis->get("shop:{$boutiqueId}:coupons");

        return null !== $data ? json_decode($data, true) : null;
    }

    public function setShopCoupons(string $boutiqueId, array $data, int $ttl = 21600): void
    {
        if (!$this->redis) {
            return;
        }

        $this->redis->setex("shop:{$boutiqueId}:coupons", $ttl, json_encode($data));
    }

    public function invalidateShop(string $boutiqueId): void
    {
        if (!$this->redis) {
            return;
        }

        $keys = $this->redis->keys("shop:{$boutiqueId}:coupon*");
        if (!empty($keys)) {
            $this->redis->del(...$keys);
        }
    }
}
