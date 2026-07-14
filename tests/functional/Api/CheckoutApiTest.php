<?php

namespace App\Tests\Functional\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Boutique;
use App\Entity\Product;
use App\Repository\BoutiqueRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;

final class CheckoutApiTest extends ApiTestCase
{
    protected static ?bool $alwaysBootKernel = true;

    public function testCustomerCanAddProductAndCheckoutWithCashOnDelivery(): void
    {
        $client = static::createClient();
        $login = $client->request('POST', 'http://localhost/api/auth/login', [
            'json' => [
                'email' => $_SERVER['TEST_BOUTIQUE_ADMIN_EMAIL'] ?? 'owner.demo-hanooti@hanooti.local',
                'password' => $_SERVER['TEST_BOUTIQUE_ADMIN_PASSWORD'] ?? 'password123',
            ],
        ]);
        self::assertResponseStatusCodeSame(200);
        $headers = [
            'Host' => 'demo-hanooti.localhost',
            'Authorization' => 'Bearer '.$login->toArray(false)['accessToken'],
        ];
        $product = $this->findActiveProduct();

        $client->request('POST', 'http://demo-hanooti.localhost/api/cart/items', [
            'headers' => $headers,
            'json' => ['productId' => (string) $product->getId(), 'quantity' => 1],
        ]);
        self::assertResponseIsSuccessful();

        $response = $client->request('POST', 'http://demo-hanooti.localhost/api/cart/checkout', [
            'headers' => $headers,
            'json' => [
                'paymentMethodCode' => 'CASH_ON_DELIVERY',
                'customerEmail' => 'client0.demo-hanooti@example.test',
                'firstName' => 'API',
                'lastName' => 'Customer',
                'phone' => '+21620000000',
                'shippingAddress' => '1 Rue de Test',
                'shippingCity' => 'Tunis',
                'shippingPostalCode' => '1000',
                'shippingCountry' => 'Tunisie',
                'shippingGovernorate' => 'Tunis',
                'shippingLocality' => 'Tunis Centre',
            ],
        ]);
        self::assertResponseStatusCodeSame(201);

        $payload = $response->toArray(false);
        self::assertIsString($payload['orderId'] ?? null);
        self::assertSame('CASH_ON_DELIVERY', $payload['paymentMethodCode']);
        self::assertSame('pending', $payload['status']);
        self::assertGreaterThan(0, $payload['totalCents']);

        $order = static::getContainer()->get(OrderRepository::class)->find($payload['orderId']);
        if (null !== $order) {
            static::getContainer()->get('doctrine')->getManager()->remove($order);
            static::getContainer()->get('doctrine')->getManager()->flush();
        }
    }

    private function findActiveProduct(): Product
    {
        $boutique = static::getContainer()->get(BoutiqueRepository::class)->findOneBy(['slug' => 'demo-hanooti']);
        self::assertInstanceOf(Boutique::class, $boutique);

        $product = static::getContainer()->get(ProductRepository::class)->findOneBy([
            'boutique' => $boutique,
            'status' => 'ACTIVE',
            'deletedAt' => null,
        ]);
        self::assertInstanceOf(Product::class, $product);

        return $product;
    }
}
