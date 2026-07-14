<?php

namespace App\Tests\Security;

use App\Security\LocalTokenManager;
use PHPUnit\Framework\TestCase;

final class LocalTokenManagerTest extends TestCase
{
    public function testInvalidSignatureIsRejected(): void
    {
        /** @var LocalTokenManager $manager */
        $manager = (new \ReflectionClass(LocalTokenManager::class))->newInstanceWithoutConstructor();
        $secret = new \ReflectionProperty(LocalTokenManager::class, 'appSecret');
        $secret->setValue($manager, 'test-secret');

        $this->expectException(\Symfony\Component\Security\Core\Exception\BadCredentialsException::class);
        $manager->validate('header.payload.invalid-signature');
    }
}
