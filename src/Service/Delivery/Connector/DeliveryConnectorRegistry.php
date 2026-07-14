<?php

namespace App\Service\Delivery\Connector;

use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

/**
 * Configuration Manager entry point for connectors: resolves the
 * DeliveryProviderInterface implementation for a given DeliveryCompany
 * provider code, falling back to the generic data-driven connector when no
 * dedicated connector is registered for that code. This is what lets
 * SUPER_ADMINs plug a brand new carrier purely through configuration.
 */
final class DeliveryConnectorRegistry
{
    /** @var iterable<DeliveryProviderInterface> */
    private readonly iterable $connectors;

    public function __construct(
        #[AutowireIterator('app.delivery.connector')]
        iterable $connectors,
        private readonly GenericHttpConnector $fallback,
    ) {
        $this->connectors = $connectors;
    }

    public function resolve(string $providerCode): DeliveryProviderInterface
    {
        foreach ($this->connectors as $connector) {
            if ($connector->supports($providerCode)) {
                return $connector;
            }
        }

        return $this->fallback;
    }

    /** @return list<string> provider codes with a dedicated (non-generic) connector */
    public function dedicatedProviderCodes(): array
    {
        $codes = [];
        foreach ($this->connectors as $connector) {
            if ($connector instanceof GenericHttpConnector) {
                continue;
            }
            $ref = new \ReflectionClass($connector);
            $codes[] = lcfirst(str_replace('Connector', '', $ref->getShortName()));
        }

        return $codes;
    }
}
