<?php

namespace App\Service\Delivery\Connector;

/**
 * Uniform result returned by every DeliveryProviderInterface method.
 */
final class DeliveryResult
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $trackingNumber = null,
        public readonly ?string $labelUrl = null,
        public readonly ?string $status = null,
        public readonly ?int $costCents = null,
        /** @var list<array<string, mixed>>|null */
        public readonly ?array $cities = null,
        public readonly mixed $rawResponse = null,
        public readonly ?string $errorMessage = null,
        public readonly ?int $httpStatus = null,
        public readonly ?string $requestUrl = null,
        public readonly ?string $requestMethod = null,
        public readonly mixed $requestBody = null,
        public readonly ?int $durationMs = null,
    ) {
    }

    public static function ok(array $overrides = []): self
    {
        return new self(
            success: true,
            trackingNumber: $overrides['trackingNumber'] ?? null,
            labelUrl: $overrides['labelUrl'] ?? null,
            status: $overrides['status'] ?? null,
            costCents: $overrides['costCents'] ?? null,
            cities: $overrides['cities'] ?? null,
            rawResponse: $overrides['rawResponse'] ?? null,
            httpStatus: $overrides['httpStatus'] ?? null,
            requestUrl: $overrides['requestUrl'] ?? null,
            requestMethod: $overrides['requestMethod'] ?? null,
            requestBody: $overrides['requestBody'] ?? null,
            durationMs: $overrides['durationMs'] ?? null,
        );
    }

    public static function fail(string $message, mixed $rawResponse = null, ?int $httpStatus = null, array $overrides = []): self
    {
        return new self(
            success: false,
            rawResponse: $rawResponse,
            errorMessage: $message,
            httpStatus: $httpStatus,
            requestUrl: $overrides['requestUrl'] ?? null,
            requestMethod: $overrides['requestMethod'] ?? null,
            requestBody: $overrides['requestBody'] ?? null,
            durationMs: $overrides['durationMs'] ?? null,
        );
    }
}
