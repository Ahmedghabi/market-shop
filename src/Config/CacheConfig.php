<?php

namespace App\Config;

final readonly class CacheConfig
{
    private array $cacheTtl;

    public function __construct(array $cacheTtl)
    {
        $this->cacheTtl = $cacheTtl;
    }

    public function getTtl(string $key, int $default = 600): int
    {
        return $this->cacheTtl[$key] ?? $default;
    }

    public function moduleAccess(): int
    {
        return $this->getTtl('module_access');
    }

    public function settings(): int
    {
        return $this->getTtl('settings');
    }

    public function menu(): int
    {
        return $this->getTtl('menu');
    }

    public function cms(): int
    {
        return $this->getTtl('cms');
    }

    public function notifications(): int
    {
        return $this->getTtl('notifications');
    }

    public function media(): int
    {
        return $this->getTtl('media');
    }

    public function dashboard(): int
    {
        return $this->getTtl('dashboard');
    }

    public function marketing(): int
    {
        return $this->getTtl('marketing');
    }

    public function coupons(): int
    {
        return $this->getTtl('coupons');
    }

    public function deliveryRules(): int
    {
        return $this->getTtl('delivery_rules');
    }

    public function refunds(): int
    {
        return $this->getTtl('refunds');
    }

    public function invoices(): int
    {
        return $this->getTtl('invoices');
    }

    public function favorites(): int
    {
        return $this->getTtl('favorites');
    }

    public function session(): int
    {
        return $this->getTtl('session');
    }

    public function chatbotConfig(): int
    {
        return $this->getTtl('chatbot_config');
    }
}
