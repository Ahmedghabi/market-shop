<?php

namespace App\ApiResource\Routing;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\State\Routing\RoutesProvider;

#[ApiResource(
    shortName: 'Routes',
    operations: [
        new Get(uriTemplate: '/routes', provider: RoutesProvider::class),
    ],
)]
final class RoutesResource
{
    /** @var array<int, array<string, mixed>> */
    public array $publicRoutes = [];

    /** @var array<int, array<string, mixed>> */
    public array $adminRoutes = [];

    /**
     * @param array<int, array<string, mixed>> $publicRoutes
     * @param array<int, array<string, mixed>> $adminRoutes
     */
    public function __construct(array $publicRoutes, array $adminRoutes)
    {
        $this->publicRoutes = $publicRoutes;
        $this->adminRoutes = $adminRoutes;
    }
}
