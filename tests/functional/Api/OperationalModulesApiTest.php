<?php

namespace App\Tests\Functional\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Repository\DeliveryRuleRepository;
use App\Repository\WebhookRepository;

final class OperationalModulesApiTest extends ApiTestCase
{
    protected static ?bool $alwaysBootKernel = true;

    public function testSuperAdminCanCreateAndDeleteWebhook(): void
    {
        $client = static::createClient();
        $headers = $this->superAdminHeaders($client);
        $url = 'https://example.test/webhooks/'.bin2hex(random_bytes(4));

        $response = $client->request('POST', 'http://localhost/api/admin/webhooks', [
            'headers' => $headers,
            'json' => [
                'url' => $url,
                'events' => ['order.created'],
                'secret' => 'test-secret',
                'status' => 'ACTIVE',
            ],
        ]);
        self::assertResponseStatusCodeSame(201);

        $webhook = static::getContainer()->get(WebhookRepository::class)->findOneBy(['url' => $url]);
        self::assertNotNull($webhook);

        $client->request('DELETE', 'http://localhost/api/admin/webhooks/'.$webhook->getId(), [
            'headers' => $headers,
        ]);
        self::assertResponseStatusCodeSame(204);
    }

    public function testBoutiqueAdminCanCreateAndUpdateDeliveryRule(): void
    {
        $client = static::createClient();
        $headers = $this->boutiqueAdminHeaders($client);
        $ruleName = 'API Delivery '.bin2hex(random_bytes(4));
        $ruleId = null;

        try {
            $response = $client->request('POST', 'http://demo-hanooti.localhost/api/delivery-rules', [
                'headers' => $headers,
                'json' => [
                    'name' => $ruleName,
                    'type' => 'FIXED_PRICE',
                    'priceCents' => 700,
                    'isActive' => true,
                ],
            ]);
            self::assertResponseStatusCodeSame(201);
            $rule = static::getContainer()->get(DeliveryRuleRepository::class)->findOneBy(['name' => $ruleName]);
            self::assertNotNull($rule);
            $ruleId = (string) $rule->getId();

            $client->request('PUT', 'http://demo-hanooti.localhost/api/delivery-rules/'.$ruleId, [
                'headers' => $headers,
                'json' => [
                    'name' => 'API Delivery updated',
                    'type' => 'FIXED_PRICE',
                    'priceCents' => 900,
                    'isActive' => true,
                ],
            ]);
            self::assertResponseIsSuccessful();
        } finally {
            if (is_string($ruleId)) {
                static::getContainer()->get('doctrine')->getManager()->getConnection()->executeStatement(
                    'DELETE FROM delivery_rule WHERE id = :id',
                    ['id' => $ruleId],
                );
            }
        }
    }

    public function testBoutiqueOperationalCollectionsAreReachable(): void
    {
        $client = static::createClient();
        $headers = $this->boutiqueAdminHeaders($client);

        foreach ([
            '/api/delivery-rules',
            '/api/notifications',
            '/api/orders',
            '/api/boutique/loyalty/dashboard',
            '/api/boutique/loyalty/program',
        ] as $path) {
            $response = $client->request('GET', 'http://demo-hanooti.localhost'.$path, [
                'headers' => $headers,
            ]);
            self::assertResponseIsSuccessful();

            if ('/api/orders' === $path) {
                self::assertNotEmpty($response->toArray(false)['member'] ?? []);
            }
        }
    }

    /** @return array<string, string> */
    private function superAdminHeaders($client): array
    {
        return $this->loginHeaders($client, 'super-admin.fixture@hanooti.local');
    }

    /** @return array<string, string> */
    private function boutiqueAdminHeaders($client): array
    {
        return $this->loginHeaders($client, 'owner.demo-hanooti@hanooti.local');
    }

    /** @return array<string, string> */
    private function loginHeaders($client, string $email): array
    {
        $response = $client->request('POST', 'http://localhost/api/auth/login', [
            'json' => ['email' => $email, 'password' => 'password123'],
        ]);
        self::assertResponseStatusCodeSame(200);

        return [
            'Authorization' => 'Bearer '.$response->toArray(false)['accessToken'],
            'Host' => 'demo-hanooti.localhost',
        ];
    }
}
