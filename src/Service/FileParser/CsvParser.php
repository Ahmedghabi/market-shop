<?php

namespace App\Service\FileParser;

final class CsvParser implements FileParserInterface
{
    public function parse(string $path): array
    {
        if (!is_readable($path)) {
            throw new \RuntimeException(sprintf('File "%s" is not readable.', $path));
        }

        $rows = [];
        $handle = fopen($path, 'rb');

        if (false === $handle) {
            return $rows;
        }

        while (false !== ($row = fgetcsv($handle))) {
            $rows[] = array_map('strval', $row);
        }

        fclose($handle);

        return $rows;
    }
}
