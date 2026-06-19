<?php

namespace App\Dto\Common;

final readonly class PaginationOutput
{
    public function __construct(
        public int $page,
        public int $itemsPerPage,
        public int $totalItems,
    ) {
    }
}
