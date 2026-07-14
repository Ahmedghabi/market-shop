<?php

namespace App\Service\Delivery\Connector;

use App\Entity\BoutiqueDeliveryAccount;
use App\Entity\DeliveryCompany;
use App\Entity\Order;

/**
 * Everything a connector needs to perform a call: the company configuration,
 * decrypted boutique credentials, and the already mapping-resolved request
 * body (for createShipment) or extra params (for other operations).
 */
final class DeliveryConnectorContext
{
    public function __construct(
        public readonly DeliveryCompany $company,
        public readonly ?BoutiqueDeliveryAccount $credential,
        /** @var array<string, string|null> decrypted: login, password, apiKey, token, secret, customBaseUrl */
        public readonly array $decryptedCredentials = [],
        /** @var array<string, mixed> mapping-resolved request body for createShipment */
        public readonly array $mappedBody = [],
        public readonly ?Order $order = null,
        /** @var array<string, mixed> extra call-specific parameters (e.g. weight/city for calculateCost) */
        public readonly array $params = [],
    ) {
    }

    public function credentialValue(string $key): ?string
    {
        return $this->decryptedCredentials[$key] ?? null;
    }

    public function withCredentialValue(string $key, ?string $value): self
    {
        $credentials = $this->decryptedCredentials;
        $credentials[$key] = $value;

        return new self(
            company: $this->company,
            credential: $this->credential,
            decryptedCredentials: $credentials,
            mappedBody: $this->mappedBody,
            order: $this->order,
            params: $this->params,
        );
    }

    public function withoutCredentialValue(string $key): self
    {
        $credentials = $this->decryptedCredentials;
        unset($credentials[$key]);

        return new self(
            company: $this->company,
            credential: $this->credential,
            decryptedCredentials: $credentials,
            mappedBody: $this->mappedBody,
            order: $this->order,
            params: $this->params,
        );
    }
}
