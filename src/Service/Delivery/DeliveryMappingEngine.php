<?php

namespace App\Service\Delivery;

/**
 * Dynamic mapping engine: replaces "{{variable}}" placeholders inside a
 * mapping definition (nested arrays of strings) with real values from a
 * resolved context, applying simple transformation filters when requested.
 *
 * Placeholder syntax: {{variable}} or {{variable|filter}} or {{variable|filter:arg}}.
 * Several placeholders can be concatenated in a single template string, e.g.
 * "{{customer.first_name}} {{customer.last_name}}".
 */
final class DeliveryMappingEngine
{
    private const PLACEHOLDER_PATTERN = '/\{\{\s*([a-zA-Z0-9_.]+)((?:\|[a-zA-Z0-9_]+(?::[^|}]*)?)*)\s*\}\}/';

    /**
     * Recursively resolve a mapping definition (scalars, lists, or nested maps)
     * against the given context.
     *
     * @param array<string, mixed> $mapping
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    public function resolveMapping(array $mapping, array $context): array
    {
        $result = [];
        foreach ($mapping as $key => $value) {
            $result[$key] = $this->resolveValue($value, $context);
        }

        return $result;
    }

    private function resolveValue(mixed $value, array $context): mixed
    {
        if (is_array($value)) {
            $resolved = [];
            foreach ($value as $k => $v) {
                $resolved[$k] = $this->resolveValue($v, $context);
            }

            return $resolved;
        }

        if (!is_string($value)) {
            return $value;
        }

        return $this->render($value, $context);
    }

    /**
     * Render a single template string. If the template is exactly one
     * placeholder (e.g. "{{order.total}}"), the resolved native value is
     * returned (int/float/array/etc.) instead of a string, which matters for
     * numeric API fields. Otherwise the placeholders are interpolated inside
     * the surrounding text and the result is always a string.
     *
     * @param array<string, mixed> $context
     */
    public function render(string $template, array $context): mixed
    {
        if (1 === preg_match('/^\{\{.*\}\}$/', trim($template))) {
            $matches = [];
            if (1 === preg_match(self::PLACEHOLDER_PATTERN, $template, $matches) && $matches[0] === trim($template)) {
                return $this->resolvePlaceholder($matches[1], $matches[2] ?? '', $context);
            }
        }

        return preg_replace_callback(
            self::PLACEHOLDER_PATTERN,
            function (array $matches) use ($context): string {
                $resolved = $this->resolvePlaceholder($matches[1], $matches[2] ?? '', $context);

                return is_array($resolved) ? implode(', ', array_map('strval', $resolved)) : (string) $resolved;
            },
            $template
        );
    }

    private function resolvePlaceholder(string $variable, string $filterChain, array $context): mixed
    {
        $value = $context[$variable] ?? null;

        foreach ($this->parseFilters($filterChain) as [$filter, $arg]) {
            $value = $this->applyFilter($value, $filter, $arg);
        }

        return $value;
    }

    /** @return list<array{0: string, 1: ?string}> */
    private function parseFilters(string $filterChain): array
    {
        if ('' === $filterChain) {
            return [];
        }

        $filters = [];
        foreach (explode('|', ltrim($filterChain, '|')) as $part) {
            if ('' === $part) {
                continue;
            }
            [$name, $arg] = array_pad(explode(':', $part, 2), 2, null);
            $filters[] = [$name, $arg];
        }

        return $filters;
    }

    private function applyFilter(mixed $value, string $filter, ?string $arg): mixed
    {
        return match ($filter) {
            'default' => (null === $value || '' === $value) ? $this->stripQuotes($arg ?? '') : $value,
            'upper' => is_string($value) ? mb_strtoupper($value) : $value,
            'lower' => is_string($value) ? mb_strtolower($value) : $value,
            'phone' => $this->formatPhone((string) $value, $arg),
            'date' => $this->formatDate($value, $arg ?? 'Y-m-d'),
            'amount' => $this->convertAmount($value, $arg),
            'concat' => $value.$this->stripQuotes($arg ?? ''),
            'round' => is_numeric($value) ? round((float) $value, (int) ($arg ?? '2')) : $value,
            default => $value,
        };
    }

    private function stripQuotes(string $value): string
    {
        return trim($value, "\"' ");
    }

    private function formatPhone(string $value, ?string $arg): string
    {
        $digits = preg_replace('/[^0-9+]/', '', $value) ?? '';
        $countryCode = $arg ? $this->stripQuotes($arg) : '216';

        if (str_starts_with($digits, '+')) {
            return $digits;
        }

        if (str_starts_with($digits, '00')) {
            return '+'.substr($digits, 2);
        }

        return '+'.$countryCode.ltrim($digits, '0');
    }

    private function formatDate(mixed $value, string $format): string
    {
        try {
            $date = $value instanceof \DateTimeInterface ? $value : new \DateTimeImmutable((string) $value);

            return $date->format($format);
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    private function convertAmount(mixed $value, ?string $arg): float|int
    {
        $numeric = is_numeric($value) ? (float) $value : 0.0;
        $operation = $arg ? $this->stripQuotes($arg) : 'identity';

        return match ($operation) {
            'to_millimes', 'to_cents' => (int) round($numeric * 1000),
            'from_millimes', 'from_cents' => round($numeric / 1000, 3),
            default => $numeric,
        };
    }
}
