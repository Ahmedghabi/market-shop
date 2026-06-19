<?php

namespace App\Service\Billing;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final readonly class InvoiceCacheService
{
    private const int TTL = 21600;

    public function __construct(private CacheInterface $cache)
    {
    }

    public function getInvoice(string $invoiceId, callable $loader): mixed
    {
        return $this->cache->get("invoice:{$invoiceId}", function (ItemInterface $item) use ($loader): mixed {
            $item->expiresAfter(self::TTL);

            return $loader();
        });
    }

    public function getShopInvoices(string $shopId, callable $loader): mixed
    {
        return $this->cache->get("shop:{$shopId}:invoices", function (ItemInterface $item) use ($loader): mixed {
            $item->expiresAfter(self::TTL);

            return $loader();
        });
    }

    public function invalidateInvoice(string $invoiceId): void
    {
        $this->cache->delete("invoice:{$invoiceId}");
    }

    public function invalidateShopInvoices(string $shopId): void
    {
        $this->cache->delete("shop:{$shopId}:invoices");
    }
}
