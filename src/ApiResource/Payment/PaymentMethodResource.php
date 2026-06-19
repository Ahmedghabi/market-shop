<?php

namespace App\ApiResource\Payment;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Dto\Payment\PaymentMethodInput;
use App\Dto\Payment\PaymentMethodOutput;
use App\State\Payment\PaymentMethodProcessor;
use App\State\Payment\PaymentMethodProvider;

#[ApiResource(
    shortName: 'PaymentMethod',
    operations: [
        new GetCollection(uriTemplate: '/admin/payment-methods', security: "is_granted('ROLE_SUPER_ADMIN')", output: PaymentMethodOutput::class, provider: PaymentMethodProvider::class),
        new Post(uriTemplate: '/admin/payment-methods', security: "is_granted('ROLE_SUPER_ADMIN')", input: PaymentMethodInput::class, output: PaymentMethodOutput::class, processor: PaymentMethodProcessor::class),
        new Get(uriTemplate: '/admin/payment-methods/{id}', security: "is_granted('ROLE_SUPER_ADMIN')", output: PaymentMethodOutput::class, provider: PaymentMethodProvider::class),
        new Patch(uriTemplate: '/admin/payment-methods/{id}', security: "is_granted('ROLE_SUPER_ADMIN')", input: PaymentMethodInput::class, output: PaymentMethodOutput::class, processor: PaymentMethodProcessor::class),
        new Delete(uriTemplate: '/admin/payment-methods/{id}', security: "is_granted('ROLE_SUPER_ADMIN')", processor: PaymentMethodProcessor::class),
    ],
)]
final class PaymentMethodResource
{
    public ?string $id = null;
}
