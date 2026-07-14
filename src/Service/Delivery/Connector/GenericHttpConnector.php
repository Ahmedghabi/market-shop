<?php

namespace App\Service\Delivery\Connector;

use App\Enum\DeliveryAuthType;
use App\Enum\DeliveryEndpointType;
use App\Enum\DeliveryResponseType;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Data-driven fallback connector. It builds requests purely from the
 * DeliveryCompany + DeliveryEndpoint configuration (baseUrl, endpoint URLs,
 * HTTP methods, headers, auth type) with no carrier-specific code, which is
 * exactly what lets a SUPER_ADMIN plug in a brand new "generic REST" carrier
 * without touching PHP code.
 */
final class GenericHttpConnector implements DeliveryProviderInterface
{
    public function __construct(
        private readonly HttpClientInterface $client,
    ) {
    }

    public function supports(string $providerCode): bool
    {
        return 'generic_http' === $providerCode;
    }

    public function createShipment(DeliveryConnectorContext $context): DeliveryResult
    {
        $endpoint = $context->company->getEndpoint(DeliveryEndpointType::CreateShipment);
        if (null === $endpoint) {
            return DeliveryResult::fail("Aucun endpoint 'create_shipment' configuré pour ce transporteur.");
        }

        $response = $this->call($context, $endpoint->getUrl(), $endpoint->getHttpMethod()->value, $endpoint->getHeaders(), $endpoint->getResponseType(), $context->mappedBody);

        if (!$response['success']) {
            return DeliveryResult::fail($response['error'], $response['body'], $response['status'], ['requestUrl' => $response['url'], 'requestMethod' => $endpoint->getHttpMethod()->value, 'requestBody' => $context->mappedBody]);
        }

        $data = is_array($response['body']) ? $response['body'] : [];

        return DeliveryResult::ok([
            'trackingNumber' => $this->extractFirst($data, ['tracking', 'tracking_number', 'trackingNumber', 'id', 'awb', 'reference']),
            'labelUrl' => $this->extractFirst($data, ['label_url', 'labelUrl', 'label']),
            'status' => $this->extractFirst($data, ['status']),
            'rawResponse' => $response['body'],
            'httpStatus' => $response['status'],
            'requestUrl' => $response['url'],
            'requestMethod' => $endpoint->getHttpMethod()->value,
            'requestBody' => $context->mappedBody,
            'durationMs' => $response['durationMs'] ?? null,
        ]);
    }

    public function cancelShipment(DeliveryConnectorContext $context, string $trackingNumber): DeliveryResult
    {
        return $this->simpleTrackingCall($context, DeliveryEndpointType::CancelShipment, $trackingNumber, "Aucun endpoint 'cancel_shipment' configuré pour ce transporteur.");
    }

    public function trackShipment(DeliveryConnectorContext $context, string $trackingNumber): DeliveryResult
    {
        $endpoint = $context->company->getEndpoint(DeliveryEndpointType::TrackShipment);
        if (null === $endpoint) {
            return DeliveryResult::fail("Aucun endpoint 'track_shipment' configuré pour ce transporteur.");
        }

        $url = str_replace('{tracking}', $trackingNumber, $endpoint->getUrl());
        $response = $this->call($context, $url, $endpoint->getHttpMethod()->value, $endpoint->getHeaders(), $endpoint->getResponseType(), null);

        if (!$response['success']) {
            return DeliveryResult::fail($response['error'], $response['body'], $response['status']);
        }

        $data = is_array($response['body']) ? $response['body'] : [];

        return DeliveryResult::ok([
            'status' => $this->extractFirst($data, ['status', 'state']),
            'rawResponse' => $response['body'],
            'httpStatus' => $response['status'],
            'requestUrl' => $response['url'],
        ]);
    }

    public function getLabel(DeliveryConnectorContext $context, string $trackingNumber): DeliveryResult
    {
        $endpoint = $context->company->getEndpoint(DeliveryEndpointType::GetLabel);
        if (null === $endpoint) {
            return DeliveryResult::fail("Aucun endpoint 'get_label' configuré pour ce transporteur.");
        }

        $url = str_replace('{tracking}', $trackingNumber, $endpoint->getUrl());
        $response = $this->call($context, $url, $endpoint->getHttpMethod()->value, $endpoint->getHeaders(), $endpoint->getResponseType(), null);

        if (!$response['success']) {
            return DeliveryResult::fail($response['error'], $response['body'], $response['status']);
        }

        $data = is_array($response['body']) ? $response['body'] : [];

        return DeliveryResult::ok([
            'labelUrl' => $this->extractFirst($data, ['label_url', 'labelUrl', 'label', 'url']),
            'rawResponse' => $response['body'],
            'httpStatus' => $response['status'],
        ]);
    }

    public function calculateCost(DeliveryConnectorContext $context): DeliveryResult
    {
        $endpoint = $context->company->getEndpoint(DeliveryEndpointType::CalculateCost);
        if (null === $endpoint) {
            return DeliveryResult::fail("Aucun endpoint 'calculate_cost' configuré pour ce transporteur.");
        }

        $response = $this->call($context, $endpoint->getUrl(), $endpoint->getHttpMethod()->value, $endpoint->getHeaders(), $endpoint->getResponseType(), $context->params);

        if (!$response['success']) {
            return DeliveryResult::fail($response['error'], $response['body'], $response['status']);
        }

        $data = is_array($response['body']) ? $response['body'] : [];
        $costCents = $this->extractFirst($data, ['cost_cents', 'price_cents', 'amount_cents']);
        if (null === $costCents) {
            $cost = $this->extractFirst($data, ['cost', 'price', 'amount']);
            $costCents = null !== $cost ? (string) (int) round(((float) $cost) * 100) : null;
        }

        return DeliveryResult::ok([
            'costCents' => null !== $costCents ? (int) $costCents : null,
            'rawResponse' => $response['body'],
            'httpStatus' => $response['status'],
        ]);
    }

    public function getCities(DeliveryConnectorContext $context): DeliveryResult
    {
        $endpoint = $context->company->getEndpoint(DeliveryEndpointType::GetCities);
        if (null === $endpoint) {
            return DeliveryResult::fail("Aucun endpoint 'get_cities' configuré pour ce transporteur.");
        }

        $response = $this->call($context, $endpoint->getUrl(), $endpoint->getHttpMethod()->value, $endpoint->getHeaders(), $endpoint->getResponseType(), null);

        if (!$response['success']) {
            return DeliveryResult::fail($response['error'], $response['body'], $response['status']);
        }

        $data = is_array($response['body']) ? $response['body'] : [];
        $cities = $data['cities'] ?? $data['data'] ?? (array_is_list($data) ? $data : []);

        return DeliveryResult::ok([
            'cities' => is_array($cities) ? $cities : [],
            'rawResponse' => $response['body'],
            'httpStatus' => $response['status'],
        ]);
    }

    public function testConnection(DeliveryConnectorContext $context): DeliveryResult
    {
        $authEndpoint = $context->company->getEndpoint(DeliveryEndpointType::Auth);
        if (null === $authEndpoint) {
            return DeliveryResult::ok(['status' => 'no_auth_check']);
        }

        $response = $this->call($context, $authEndpoint->getUrl(), $authEndpoint->getHttpMethod()->value, $authEndpoint->getHeaders(), $authEndpoint->getResponseType(), []);

        return $response['success']
            ? DeliveryResult::ok(['rawResponse' => $response['body'], 'httpStatus' => $response['status']])
            : DeliveryResult::fail($response['error'], $response['body'], $response['status']);
    }

    private function simpleTrackingCall(DeliveryConnectorContext $context, DeliveryEndpointType $type, string $trackingNumber, string $missingMessage): DeliveryResult
    {
        $endpoint = $context->company->getEndpoint($type);
        if (null === $endpoint) {
            return DeliveryResult::fail($missingMessage);
        }

        $url = str_replace('{tracking}', $trackingNumber, $endpoint->getUrl());
        $response = $this->call($context, $url, $endpoint->getHttpMethod()->value, $endpoint->getHeaders(), $endpoint->getResponseType(), ['tracking' => $trackingNumber]);

        if (!$response['success']) {
            return DeliveryResult::fail($response['error'], $response['body'], $response['status']);
        }

        return DeliveryResult::ok(['rawResponse' => $response['body'], 'httpStatus' => $response['status']]);
    }

    /**
     * @param array<string, string> $extraHeaders
     *
     * @return array{success: bool, status: ?int, body: mixed, error: ?string, url: string}
     */
    private function call(DeliveryConnectorContext $context, string $path, string $method, array $extraHeaders, DeliveryResponseType $responseType, ?array $body): array
    {
        $baseUrl = $context->credentialValue('customBaseUrl') ?: $context->company->getBaseUrl();
        $url = str_starts_with($path, 'http') ? $path : rtrim($baseUrl, '/').'/'.ltrim($path, '/');

        $headers = $this->buildAuthHeaders($context) + $extraHeaders;

        $options = ['headers' => $headers, 'timeout' => (float) ($context->company->getParametersConfig()['timeout'] ?? 15)];

        if (DeliveryAuthType::Basic === $context->company->getAuthType() && !isset($headers['Authorization'])) {
            $options['auth_basic'] = [$context->credentialValue('login') ?? '', $context->credentialValue('password') ?? ''];
        }

        if (null !== $body) {
            $options['json'] = $body;
        }

        try {
            $startedAt = microtime(true);
            $response = $this->client->request($method, $url, $options);
            $status = $response->getStatusCode();
            $parsed = $this->parseResponse($response->getContent(false), $responseType);
            $durationMs = (int) round((microtime(true) - $startedAt) * 1000);

            if ($status >= 200 && $status < 300) {
                return ['success' => true, 'status' => $status, 'body' => $parsed, 'error' => null, 'url' => $url, 'durationMs' => $durationMs];
            }

            return ['success' => false, 'status' => $status, 'body' => $parsed, 'error' => sprintf('Erreur HTTP %d', $status), 'url' => $url, 'durationMs' => $durationMs];
        } catch (\Throwable $e) {
            return ['success' => false, 'status' => null, 'body' => null, 'error' => $e->getMessage(), 'url' => $url, 'durationMs' => null];
        }
    }

    /** @return array<string, string> */
    private function buildAuthHeaders(DeliveryConnectorContext $context): array
    {
        $authConfig = $context->company->getAuthConfig();

        return match ($context->company->getAuthType()) {
            DeliveryAuthType::Bearer => ['Authorization' => 'Bearer '.($context->credentialValue('token') ?? '')],
            DeliveryAuthType::ApiKey => [(string) ($authConfig['headerName'] ?? 'X-Api-Key') => $context->credentialValue('apiKey') ?? ''],
            DeliveryAuthType::Custom => $this->buildCustomHeaders($authConfig, $context),
            default => [],
        };
    }

    /** @return array<string, string> */
    private function buildCustomHeaders(array $authConfig, DeliveryConnectorContext $context): array
    {
        $headers = [];
        foreach ((array) ($authConfig['headers'] ?? []) as $name => $credentialKey) {
            if (is_string($name) && is_string($credentialKey)) {
                $headers[$name] = $context->credentialValue($credentialKey) ?? '';
            }
        }

        return $headers;
    }

    private function parseResponse(string $raw, DeliveryResponseType $type): mixed
    {
        if ('' === trim($raw)) {
            return null;
        }

        if (DeliveryResponseType::Json === $type) {
            $decoded = json_decode($raw, true);

            return JSON_ERROR_NONE === json_last_error() ? $decoded : ['raw' => $raw];
        }

        if (DeliveryResponseType::Xml === $type) {
            $xml = @simplexml_load_string($raw);

            return false !== $xml ? json_decode(json_encode($xml) ?: '{}', true) : ['raw' => $raw];
        }

        return ['raw' => $raw];
    }

    private function extractFirst(array $data, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (isset($data[$key]) && (is_string($data[$key]) || is_numeric($data[$key]))) {
                return (string) $data[$key];
            }
        }

        return null;
    }
}
