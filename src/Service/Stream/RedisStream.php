<?php

namespace App\Service\Stream;

final class RedisStream
{
    /** @param array<string, string> $payload */
    public function publish(string $stream, array $payload): void
    {
        unset($stream, $payload);
    }
}
