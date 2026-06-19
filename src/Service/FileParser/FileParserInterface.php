<?php

namespace App\Service\FileParser;

interface FileParserInterface
{
    /** @return list<array<string, string>> */
    public function parse(string $path): array;
}
