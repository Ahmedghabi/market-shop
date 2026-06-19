<?php

namespace App\Service\Delivery;

use App\Entity\DeliveryCompany;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class DeliveryApiClient
{
    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly EncryptionService $encryption,
    ) {
    }

    /**
     * Verify credentials against the delivery company's auth endpoint.
     *
     * @return array{success: bool, message?: string, token?: string}
     */
    public function verifyCredentials(DeliveryCompany $company, string $login, string $password): array
    {
        $endpoint = $company->getAuthEndpoint();

        if (null === $endpoint) {
            return ['success' => true, 'message' => 'Aucune vérification requise.'];
        }

        $url = rtrim($company->getBaseUrl(), '/').'/'.ltrim($endpoint, '/');

        try {
            $response = $this->client->request('POST', $url, [
                'json' => [
                    'login' => $login,
                    'password' => $password,
                ],
                'timeout' => 10,
            ]);

            if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
                $data = $response->toArray();

                return [
                    'success' => true,
                    'message' => 'Compte vérifié avec succès.',
                    'token' => $data['token'] ?? null,
                ];
            }

            return [
                'success' => false,
                'message' => sprintf('Erreur HTTP %d', $response->getStatusCode()),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Submit an order to the delivery company.
     *
     * @param array<string, mixed> $orderPayload
     *
     * @return array{success: bool, tracking?: string, error?: string}
     */
    public function submitOrder(DeliveryCompany $company, string $login, string $password, array $orderPayload): array
    {
        $endpoint = $company->getSubmitOrderEndpoint();
        $url = rtrim($company->getBaseUrl(), '/').'/'.ltrim($endpoint, '/');

        try {
            $response = $this->client->request('POST', $url, [
                'json' => $orderPayload,
                'auth_basic' => [$login, $password],
                'timeout' => 15,
            ]);

            if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
                $data = $response->toArray();

                return [
                    'success' => true,
                    'tracking' => $data['tracking'] ?? $data['tracking_number'] ?? $data['id'] ?? null,
                ];
            }

            return [
                'success' => false,
                'error' => sprintf('Erreur HTTP %d', $response->getStatusCode()),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Track an order with the delivery company.
     *
     * @return array{success: bool, status?: string, location?: string, error?: string}
     */
    public function trackOrder(DeliveryCompany $company, string $tracking): array
    {
        $endpoint = $company->getTrackEndpoint();
        if (null === $endpoint) {
            return ['success' => false, 'error' => 'Suivi non disponible pour ce transporteur.'];
        }

        $url = rtrim($company->getBaseUrl(), '/').'/'.ltrim($endpoint, '/');
        $url = str_replace('{tracking}', $tracking, $url);

        try {
            $response = $this->client->request('GET', $url, [
                'timeout' => 10,
            ]);

            if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
                $data = $response->toArray();

                return [
                    'success' => true,
                    'status' => $data['status'] ?? 'unknown',
                    'location' => $data['location'] ?? null,
                ];
            }

            return ['success' => false, 'error' => sprintf('Erreur HTTP %d', $response->getStatusCode())];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
