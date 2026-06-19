<?php

namespace App\Service\FileParser;

final readonly class FileParserFactory
{
    public function __construct(private CsvParser $csvParser)
    {
    }

    public function create(string $extension): FileParserInterface
    {
        return match (strtolower($extension)) {
            'csv' => $this->csvParser,
            default => throw new \InvalidArgumentException(sprintf('Unsupported file extension "%s".', $extension)),
        };
    }
}
