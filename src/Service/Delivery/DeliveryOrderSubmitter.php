<?php

namespace App\Service\Delivery;

use App\Entity\Order;

final class DeliveryOrderSubmitter
{
    public function __construct(
        private readonly EncryptionService $encryption,
        private readonly DeliveryApiClient $apiClient,
    ) {
    }

    /**
     * Build the submission payload for a delivery company.
     *
     * Format spécifique: boutique, client, adresse livraison, produits, montant.
     *
     * @return array<string, mixed>
     */
    public function buildPayload(Order $order): array
    {
        $boutique = $order->getBoutique();
        $items = [];

        foreach ($order->getItems() as $item) {
            $items[] = [
                'sku' => $item->getSku(),
                'product' => $item->getProductName(),
                'quantity' => $item->getQuantity(),
                'unit_price_cents' => $item->getUnitPriceCents(),
            ];
        }

        return [
            'order' => [
                'id' => (string) $order->getId(),
                'reference' => (string) $order->getId(),
                'created_at' => $order->getCreatedAt()->format('c'),
            ],
            'boutique' => [
                'id' => (string) $boutique->getId(),
                'name' => $boutique->getName(),
            ],
            'customer' => [
                'name' => $order->getCustomerName(),
                'email' => $order->getCustomerEmail(),
                'phone' => $order->getCustomerPhone(),
            ],
            'shipping' => [
                'address' => $order->getShippingAddress(),
                'city' => $order->getShippingCity(),
            ],
            'items' => $items,
            'amount' => [
                'total_cents' => $order->getTotalCents(),
                'currency' => $order->getCurrency(),
            ],
        ];
    }

    /**
     * Submit an order to the delivery company using the boutique's account.
     *
     * @return array{success: bool, tracking?: string, error?: string}
     */
    public function submit(Order $order): array
    {
        $boutique = $order->getBoutique();
        $settings = $boutique->getSettings();

        if (null === $settings || !$settings->useDeliveryApi()) {
            $order->markAsShipped(sprintf('FAKE-%s', strtoupper(bin2hex(random_bytes(4)))));

            return ['success' => true, 'tracking' => $order->getDeliveryTracking() ?? ''];
        }

        $accounts = $boutique->getDeliveryAccounts()->filter(
            fn ($a) => $a->isActive() && $a->isVerified()
        );

        if ($accounts->isEmpty()) {
            $order->markDeliveryError('Aucun compte livraison vérifié');

            return ['success' => false, 'error' => 'Aucun compte livraison actif et vérifié pour cette boutique.'];
        }

        $account = $accounts->first();
        $company = $account->getDeliveryCompany();

        try {
            $login = $this->encryption->decrypt($account->getEncryptedLogin());
            $password = $this->encryption->decrypt($account->getEncryptedPassword());
        } catch (\RuntimeException $e) {
            $account->markAsUnverified('Erreur déchiffrement: '.$e->getMessage());

            return ['success' => false, 'error' => 'Erreur de déchiffrement des identifiants.'];
        }

        $payload = $this->buildPayload($order);

        $result = $this->apiClient->submitOrder($company, $login, $password, $payload);

        if ($result['success']) {
            $order->markAsShipped($result['tracking'] ?? '');
            $order->markDeliverySubmitted();

            return ['success' => true, 'tracking' => $order->getDeliveryTracking() ?? ''];
        }

        $order->markDeliveryError($result['error'] ?? 'Erreur inconnue');

        return $result;
    }
}
