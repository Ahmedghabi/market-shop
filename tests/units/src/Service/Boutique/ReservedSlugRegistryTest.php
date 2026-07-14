<?php

namespace App\Tests\Service\Boutique;

use App\Service\Boutique\ReservedSlugRegistry;
use PHPUnit\Framework\TestCase;

final class ReservedSlugRegistryTest extends TestCase
{
    public function testDefaultReservedSlugsAreCaseInsensitiveAndTrimmed(): void
    {
        $registry = new ReservedSlugRegistry();

        self::assertTrue($registry->isReserved(' ADMIN '));
        self::assertTrue($registry->isReserved('Api'));
        self::assertFalse($registry->isReserved('my-boutique'));
    }

    public function testAdditionalReservedSlugsAreIncludedOnce(): void
    {
        $registry = new ReservedSlugRegistry(['internal', 'INTERNAL']);

        self::assertTrue($registry->isReserved('internal'));
        self::assertSame(1, count(array_filter(
            $registry->getAll(),
            static fn (string $slug): bool => 'internal' === strtolower($slug),
        )));
    }
}
