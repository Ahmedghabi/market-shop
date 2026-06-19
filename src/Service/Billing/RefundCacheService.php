<?php

namespace App\Service\Billing;

use App\Factory\RedisFactory;

final readonly class RefundCacheService
{
    private ?\Redis $redis;

    public function __construct(
        private RedisFactory $redisFactory,
    ) {
        $this->redis = $this->redisFactory->create();
    }

    public function get(string $refundId): ?array
    {
        if (!$this->redis) {
            return null;
        }

        $data = $this->redis->get("refund:{$refundId}");

        return null !== $data ? json_decode($data, true) : null;
    }

    public function set(string $refundId, array $data, int $ttl = 21600): void
    {
        if (!$this->redis) {
            return;
        }

        $this->redis->setex("refund:{$refundId}", $ttl, json_encode($data));
    }

    public function invalidate(string $refundId): void
    {
        if (!$this->redis) {
            return;
        }

        $this->redis->del("refund:{$refundId}");
    }

    public function invalidateShop(string $boutiqueId): void
    {
        if (!$this->redis) {
            return;
        }

        $keys = $this->redis->keys("shop:{$boutiqueId}:refunds:*");
        if (!empty($keys)) {
            $this->redis->del(...$keys);
        }
        $this->redis->del("shop:{$boutiqueId}:refunds");
    }
}
