<?php

namespace App\State\Payment;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Payment\PaymentMethodInput;
use App\Dto\Payment\PaymentMethodOutput;
use App\Enum\PaymentMethodType;
use App\Entity\PaymentMethod;
use App\Repository\PaymentMethodRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProcessorInterface<PaymentMethodOutput|null> */
final readonly class PaymentMethodProcessor implements ProcessorInterface
{
    public function __construct(
        private PaymentMethodRepository $methods,
        private EntityManagerInterface $em,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?PaymentMethodOutput
    {
        unset($context);

        if ($operation instanceof Delete) {
            $method = $this->find((string) ($uriVariables['id'] ?? ''));
            $this->em->remove($method);
            $this->em->flush();

            return null;
        }

        assert($data instanceof PaymentMethodInput);

        $code = strtoupper(trim($data->code));

        $existingByCode = $this->methods->findOneByCode($code);
        $id = $uriVariables['id'] ?? null;

        if ($id) {
            $method = $this->find((string) $id);
            if ($existingByCode instanceof PaymentMethod && (string) $existingByCode->getId() !== (string) $method->getId()) {
                throw new BadRequestHttpException('Payment method code already exists');
            }
            $method->setName($data->name);
            $method->setCode($code);
            $method->setDescription($data->description);
            $method->setLogo($data->logo);
            $method->setType(PaymentMethodType::tryFrom($data->type) ?? PaymentMethodType::ExternalGateway);
            $method->setIsActive($data->isActive);
            $method->setIsVisible($data->isVisible);
        } else {
            if ($existingByCode instanceof PaymentMethod) {
                throw new BadRequestHttpException('Payment method code already exists');
            }
            $method = new PaymentMethod(
                $data->name,
                $code,
                $data->description,
                $data->logo,
                PaymentMethodType::tryFrom($data->type) ?? PaymentMethodType::ExternalGateway,
                $data->isActive,
                $data->isVisible,
            );
            $this->em->persist($method);
        }

        $this->em->flush();

        return (new PaymentMethodProvider($this->methods))->provide(new \ApiPlatform\Metadata\Get(), ['id' => (string) $method->getId()]);
    }

    private function find(string $id): PaymentMethod
    {
        $method = $this->methods->find($id);
        if (!$method instanceof PaymentMethod) {
            throw new NotFoundHttpException('Payment method not found');
        }

        return $method;
    }
}
