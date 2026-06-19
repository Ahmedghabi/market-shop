<?php

namespace App\State\Payment;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Payment\PaymentMethodOutput;
use App\Entity\PaymentMethod;
use App\Repository\PaymentMethodRepository;

/** @implements ProviderInterface<PaymentMethodOutput> */
final readonly class PaymentMethodProvider implements ProviderInterface
{
    public function __construct(private PaymentMethodRepository $methods)
    {
    }

    /** @return list<PaymentMethodOutput>|PaymentMethodOutput|null */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|PaymentMethodOutput|null
    {
        unset($operation, $context);

        if (isset($uriVariables['id'])) {
            $method = $this->methods->find((string) $uriVariables['id']);

            return $method instanceof PaymentMethod ? $this->toOutput($method) : null;
        }

        return array_map([$this, 'toOutput'], $this->methods->findBy([], ['name' => 'ASC']));
    }

    private function toOutput(PaymentMethod $method): PaymentMethodOutput
    {
        $output = new PaymentMethodOutput();
        $output->id = (string) $method->getId();
        $output->name = $method->getName();
        $output->code = $method->getCode();
        $output->description = $method->getDescription();
        $output->logo = $method->getLogo();
        $output->type = $method->getType()->value;
        $output->isActive = $method->isActive();
        $output->isVisible = $method->isVisible();
        $output->createdAt = $method->getCreatedAt();
        $output->updatedAt = $method->getUpdatedAt();

        return $output;
    }
}
