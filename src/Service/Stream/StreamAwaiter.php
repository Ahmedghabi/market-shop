<?php

namespace App\Service\Stream;

final class StreamAwaiter
{
    /** @return array<string, string>|null */
    public function await(string $stream, int $timeoutMilliseconds = 1000): ?array
    {
        unset($stream, $timeoutMilliseconds);

        return null;
    }
}
