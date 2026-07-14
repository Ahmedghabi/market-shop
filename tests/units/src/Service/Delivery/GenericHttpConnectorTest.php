<?php

namespace App\Tests\Service\Delivery;

use App\Entity\DeliveryCompany;
use App\Entity\DeliveryEndpoint;
use App\Enum\DeliveryAuthType;
use App\Enum\DeliveryEndpointType;
use App\Enum\DeliveryHttpMethod;
use App\Enum\DeliveryResponseType;
use App\Service\Delivery\Connector\DeliveryConnectorContext;
use App\Service\Delivery\Connector\GenericHttpConnector;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class GenericHttpConnectorTest extends TestCase
{
    public function testAuthenticationTokenIsReturnedForAValidLogin(): void
    {
        $company = $this->company();
        $requests = [];
        $client = new MockHttpClient(function (string $method, string $url, array $options) use (&$requests): MockResponse {
            $requests[] = [$method, $url, $options];

            return new MockResponse('{"data":{"token":"token-123"}}', ['http_code' => 200]);
        });

        $result = (new GenericHttpConnector($client))->testConnection($this->context($company));

        self::assertTrue($result->success);
        self::assertSame('token-123', $result->accessToken);
        $authBody = json_decode((string) ($requests[0][2]['body'] ?? ''), true);
        self::assertSame('fixture-login', $authBody['login'] ?? null);
        self::assertSame('fixture-password', $authBody['password'] ?? null);
    }

    public function testCreateShipmentUsesValidatedToken(): void
    {
        $company = $this->company();
        $requests = [];
        $client = new MockHttpClient(function (string $method, string $url, array $options) use (&$requests): MockResponse {
            $requests[] = [$method, $url, $options];

            return 2 === count($requests)
                ? new MockResponse('{"tracking_number":"TRK-123"}', ['http_code' => 201])
                : new MockResponse('{"data":{"token":"token-123"}}', ['http_code' => 200]);
        });

        $result = (new GenericHttpConnector($client))->createShipment($this->context($company, ['reference' => 'ORD-1']));

        self::assertTrue($result->success);
        self::assertSame('TRK-123', $result->trackingNumber);
        self::assertCount(2, $requests);
        $headers = $requests[1][2]['normalized_headers'] ?? [];
        self::assertSame('Authorization: Bearer token-123', $headers['authorization'][0] ?? null);
        self::assertSame(['reference' => 'ORD-1'], json_decode((string) ($requests[1][2]['body'] ?? ''), true));
    }

    public function testCreateShipmentStopsWhenAuthenticationDoesNotReturnAToken(): void
    {
        $company = $this->company();
        $requestCount = 0;
        $client = new MockHttpClient(function () use (&$requestCount): MockResponse {
            ++$requestCount;

            return new MockResponse('{"success":true}', ['http_code' => 200]);
        });

        $result = (new GenericHttpConnector($client))->createShipment($this->context($company, ['reference' => 'ORD-1']));

        self::assertFalse($result->success);
        self::assertStringContainsString('aucun token valide', strtolower((string) $result->errorMessage));
        self::assertSame(1, $requestCount);
    }

    private function company(): DeliveryCompany
    {
        $company = new DeliveryCompany(
            name: 'Test Delivery',
            slug: 'test-delivery',
            baseUrl: 'https://delivery.test',
            provider: 'generic_http',
            authType: DeliveryAuthType::Basic,
            authConfig: ['tokenPath' => 'data.token'],
        );
        $company->addEndpoint(new DeliveryEndpoint(
            company: $company,
            type: DeliveryEndpointType::Auth,
            name: 'Auth',
            url: '/auth/token',
            httpMethod: DeliveryHttpMethod::Post,
            responseType: DeliveryResponseType::Json,
        ));
        $company->addEndpoint(new DeliveryEndpoint(
            company: $company,
            type: DeliveryEndpointType::CreateShipment,
            name: 'Create shipment',
            url: '/shipments',
            httpMethod: DeliveryHttpMethod::Post,
            responseType: DeliveryResponseType::Json,
        ));

        return $company;
    }

    /** @param array<string, mixed> $mappedBody */
    private function context(DeliveryCompany $company, array $mappedBody = []): DeliveryConnectorContext
    {
        return new DeliveryConnectorContext(
            company: $company,
            credential: null,
            decryptedCredentials: ['login' => 'fixture-login', 'password' => 'fixture-password'],
            mappedBody: $mappedBody,
        );
    }
}
