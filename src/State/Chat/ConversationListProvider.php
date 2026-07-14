<?php

namespace App\State\Chat;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Chat\ConversationListResource;
use App\Entity\Conversation;
use App\Repository\ConversationRepository;
use App\Service\Chat\ChatAccessService;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/** @implements ProviderInterface<ConversationListResource> */
final class ConversationListProvider implements ProviderInterface
{
    public function __construct(
        private ConversationRepository $repository,
        private TokenStorageInterface $tokenStorage,
        private ChatAccessService $access,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $token = $this->tokenStorage->getToken();
        $user = $token?->getUser();

        if (null === $user || !is_object($user)) {
            return [];
        }

        $qb = $this->repository->createQueryBuilder('c')
            ->addSelect('b')
            ->leftJoin('c.boutique', 'b')
            ->orderBy('c.updatedAt', 'DESC')
            ->addOrderBy('c.createdAt', 'DESC');

        if (!$this->access->canManageAllConversations()) {
            $qb->andWhere('b IN (:boutiques)')
                ->setParameter('boutiques', $this->access->getAdministeredBoutiques());
        }

        $conversations = $qb->getQuery()->getResult();

        return array_map(fn (Conversation $c) => $this->mapSingle($c), $conversations);
    }

    private function mapSingle(Conversation $conversation): ConversationListResource
    {
        $resource = new ConversationListResource();
        $resource->id = (string) $conversation->getId();
        $resource->boutiqueId = (string) $conversation->getBoutique()->getId();
        $resource->boutiqueName = $conversation->getBoutique()->getName();
        $resource->userDisplayName = $conversation->getUser()?->getDisplayName();
        $resource->guestName = $conversation->getGuestName();
        $resource->guestEmail = $conversation->getGuestEmail();

        $last = $conversation->getLastMessage();
        if (null !== $last) {
            $resource->lastMessage = mb_substr($last->getContent(), 0, 120);
            $resource->lastMessageAt = $last->getCreatedAt()->format('c');
        }

        $resource->unreadCount = $conversation->getUnreadCount();
        $resource->active = $conversation->isActive();
        $resource->createdAt = $conversation->getCreatedAt()->format('c');

        return $resource;
    }
}
