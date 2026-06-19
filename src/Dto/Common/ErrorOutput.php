<?php

namespace App\Dto\Common;

final readonly class ErrorOutput
{
    public function __construct(
        public string $message,
        /** @var array<string, mixed> */
        public array $details = [],
    ) {
    }
}
