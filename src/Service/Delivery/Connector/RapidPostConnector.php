<?php

namespace App\Service\Delivery\Connector;

/**
 * Example dedicated connector for RapidPost-style carriers: injects a
 * carrier-specific API key header and reuses the generic HTTP plumbing.
 */
final class RapidPostConnector implements DeliveryProviderInterface
{
    public function __construct(
        private readonly GenericHttpConnector $generic,
    ) {
    }

    public function supports(string $providerCode): bool
    {
        return 'rapid_post' === $providerCode;
    }

    public function createShipment(DeliveryConnectorContext $context): DeliveryResult
    {
        return $this->generic->createShipment($this->withApiKeyHeader($context));
    }

    public function cancelShipment(DeliveryConnectorContext $context, string $trackingNumber): DeliveryResult
    {
        return $this->generic->cancelShipment($this->withApiKeyHeader($context), $trackingNumber);
    }

    public function trackShipment(DeliveryConnectorContext $context, string $trackingNumber): DeliveryResult
    {
        return $this->generic->trackShipment($this->withApiKeyHeader($context), $trackingNumber);
    }

    public function getLabel(DeliveryConnectorContext $context, string $trackingNumber): DeliveryResult
    {
        return $this->generic->getLabel($this->withApiKeyHeader($context), $trackingNumber);
    }

    public function calculateCost(DeliveryConnectorContext $context): DeliveryResult
    {
        return $this->generic->calculateCost($this->withApiKeyHeader($context));
    }

    public function getCities(DeliveryConnectorContext $context): DeliveryResult
    {
        return $this->generic->getCities($this->withApiKeyHeader($context));
    }

    public function testConnection(DeliveryConnectorContext $context): DeliveryResult
    {
        return $this->generic->testConnection($this->withApiKeyHeader($context));
    }

    private function withApiKeyHeader(DeliveryConnectorContext $context): DeliveryConnectorContext
    {
        $apiKey = $context->credentialValue('apiKey') ?? '';
        $timestamp = (string) time();
        $signature = base64_encode($apiKey.':'.$timestamp);

        return new DeliveryConnectorContext(
            company: $context->company,
            credential: $context->credential,
            decryptedCredentials: $context->decryptedCredentials + [
                'rapidPostSignature' => $signature,
                'rapidPostTimestamp' => $timestamp,
            ],
            mappedBody: $context->mappedBody,
            order: $context->order,
            params: $context->params,
        );
    }
}
