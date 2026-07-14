<?php

namespace App\Service\Delivery\Connector;

/**
 * Example of a dedicated carrier connector: it only overrides what is truly
 * specific to this carrier (here, a signed-header authentication scheme
 * built from the boutique's API key + secret) and otherwise reuses the same
 * generic HTTP plumbing (endpoint config, headers, response parsing) as
 * GenericHttpConnector so that adding a real carrier stays a small amount of
 * carrier-specific code, not a full reimplementation.
 */
final class AramexConnector implements DeliveryProviderInterface
{
    public function __construct(
        private readonly GenericHttpConnector $generic,
    ) {
    }

    public function supports(string $providerCode): bool
    {
        return 'aramex' === $providerCode;
    }

    public function createShipment(DeliveryConnectorContext $context): DeliveryResult
    {
        return $this->generic->createShipment($this->withSignature($context));
    }

    public function cancelShipment(DeliveryConnectorContext $context, string $trackingNumber): DeliveryResult
    {
        return $this->generic->cancelShipment($this->withSignature($context), $trackingNumber);
    }

    public function trackShipment(DeliveryConnectorContext $context, string $trackingNumber): DeliveryResult
    {
        return $this->generic->trackShipment($this->withSignature($context), $trackingNumber);
    }

    public function getLabel(DeliveryConnectorContext $context, string $trackingNumber): DeliveryResult
    {
        return $this->generic->getLabel($this->withSignature($context), $trackingNumber);
    }

    public function calculateCost(DeliveryConnectorContext $context): DeliveryResult
    {
        return $this->generic->calculateCost($this->withSignature($context));
    }

    public function getCities(DeliveryConnectorContext $context): DeliveryResult
    {
        return $this->generic->getCities($this->withSignature($context));
    }

    public function testConnection(DeliveryConnectorContext $context): DeliveryResult
    {
        return $this->generic->testConnection($this->withSignature($context));
    }

    /**
     * Aramex-style signature: HMAC-SHA256 over apiKey + secret injected as a
     * custom-auth credential so the generic connector's Custom auth headers
     * mechanism picks it up transparently.
     */
    private function withSignature(DeliveryConnectorContext $context): DeliveryConnectorContext
    {
        $apiKey = $context->credentialValue('apiKey') ?? '';
        $secret = $context->credentialValue('secret') ?? '';
        $timestamp = (string) time();
        $signature = hash_hmac('sha256', $apiKey.$timestamp, $secret);

        return new DeliveryConnectorContext(
            company: $context->company,
            credential: $context->credential,
            decryptedCredentials: $context->decryptedCredentials + [
                'signature' => $signature,
                'timestamp' => $timestamp,
            ],
            mappedBody: $context->mappedBody,
            order: $context->order,
            params: $context->params,
        );
    }
}
