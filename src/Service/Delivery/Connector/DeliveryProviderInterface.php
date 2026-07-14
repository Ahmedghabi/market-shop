<?php

namespace App\Service\Delivery\Connector;

/**
 * Common interface implemented by every delivery carrier connector.
 *
 * A connector only handles carrier-specific concerns: authentication,
 * request signature, mandatory format, error interpretation, and response
 * parsing. Everything else (configuration, credentials, field mapping,
 * order management) is handled upstream by the DeliveryEngine.
 */
interface DeliveryProviderInterface
{
    /**
     * Whether this connector should be used for the given DeliveryCompany
     * provider code (App\Entity\DeliveryCompany::getProvider()).
     */
    public function supports(string $providerCode): bool;

    public function createShipment(DeliveryConnectorContext $context): DeliveryResult;

    public function cancelShipment(DeliveryConnectorContext $context, string $trackingNumber): DeliveryResult;

    public function trackShipment(DeliveryConnectorContext $context, string $trackingNumber): DeliveryResult;

    public function getLabel(DeliveryConnectorContext $context, string $trackingNumber): DeliveryResult;

    public function calculateCost(DeliveryConnectorContext $context): DeliveryResult;

    public function getCities(DeliveryConnectorContext $context): DeliveryResult;

    /**
     * Lightweight credential check (e.g. call the auth endpoint or a
     * cheap read-only endpoint). Used by "test connection" in the backoffice.
     */
    public function testConnection(DeliveryConnectorContext $context): DeliveryResult;
}
