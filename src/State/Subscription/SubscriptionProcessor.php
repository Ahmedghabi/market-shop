<?php

namespace App\State\Subscription;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Subscription\SubscriptionInput;
use App\Dto\Subscription\SubscriptionOutput;
use App\Enum\PlanType;
use App\Entity\Boutique;
use App\Entity\Subscription;
use App\Repository\BoutiqueRepository;
use App\Repository\SubscriptionRepository;
use App\Security\BoutiqueContext;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class SubscriptionProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly SubscriptionRepository $repository,
        private readonly BoutiqueRepository $boutiques,
        private readonly EntityManagerInterface $em,
        private readonly BoutiqueContext $context,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): SubscriptionOutput
    {
        $boutique = $this->findBoutique((string) ($uriVariables['boutiqueId'] ?? ''));

        if (!$this->context->canAccessBoutique($boutique)) {
            throw new AccessDeniedHttpException('Access denied');
        }

        $operationName = $operation->getName() ?? '';

        if ('accept_subscription' === $operationName) {
            return $this->accept((string) ($uriVariables['id'] ?? ''));
        }

        if ('reject_subscription' === $operationName) {
            return $this->reject((string) ($uriVariables['id'] ?? ''));
        }

        if (!$data instanceof SubscriptionInput) {
            throw new \InvalidArgumentException('Expected SubscriptionInput');
        }

        $entity = new Subscription($boutique, PlanType::from($data->plan));
        $this->em->persist($entity);
        $this->em->flush();

        return $this->toOutput($entity);
    }

    private function accept(string $id): SubscriptionOutput
    {
        $entity = $this->findSubscription($id);
        $user = $this->context->getUserIdentifier();
        $entity->activate($user ?? 'super-admin');
        $this->em->flush();

        return $this->toOutput($entity);
    }

    private function reject(string $id): SubscriptionOutput
    {
        $entity = $this->findSubscription($id);
        $entity->reject();
        $this->em->flush();

        return $this->toOutput($entity);
    }

    private function toOutput(Subscription $entity): SubscriptionOutput
    {
        $output = new SubscriptionOutput();
        $output->id = (string) $entity->getId();
        $output->boutiqueId = (string) $entity->getBoutique()->getId();
        $output->boutiqueName = $entity->getBoutique()->getName();
        $output->plan = $entity->getPlan();
        $output->status = $entity->getStatus();
        $output->startDate = $entity->getStartDate()?->format('c');
        $output->endDate = $entity->getEndDate()?->format('c');
        $output->acceptedBy = $entity->getAcceptedBy();
        $output->acceptedAt = $entity->getAcceptedAt()?->format('c');
        $output->createdAt = $entity->getCreatedAt()->format('c');
        $output->priceCents = $entity->getPlan()->priceCents();

        return $output;
    }

    private function findBoutique(string $id): Boutique
    {
        $entity = $this->boutiques->find($id);
        if (!$entity) {
            throw new NotFoundHttpException('Boutique not found');
        }

        return $entity;
    }

    private function findSubscription(string $id): Subscription
    {
        $entity = $this->repository->find($id);
        if (!$entity) {
            throw new NotFoundHttpException('Subscription not found');
        }

        return $entity;
    }
}
