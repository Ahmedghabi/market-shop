<?php

namespace App\Tests\Service;

use App\Service\ExampleService;
use PHPUnit\Framework\TestCase;

final class ExampleServiceTest extends TestCase
{
    public function testCreateReturnsExampleOutput(): void
    {
        $output = (new ExampleService())->create('Demo');

        self::assertSame('Demo', $output->name);
        self::assertSame('draft', $output->status);
        self::assertNotEmpty($output->id);
    }
}
