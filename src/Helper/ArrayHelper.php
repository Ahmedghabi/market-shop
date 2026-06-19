<?php

namespace App\Helper;

final class ArrayHelper
{
    /** @param array<string, mixed> $data */
    public static function stringValue(array $data, string $key, string $default = ''): string
    {
        $value = $data[$key] ?? $default;

        return is_scalar($value) ? (string) $value : $default;
    }
}
