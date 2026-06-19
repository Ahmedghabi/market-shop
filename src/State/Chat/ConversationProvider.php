<?php

namespace App\State\Chat;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Chat\ConversationResource;
use App\Entity\Conversation;
use App\Repository\ConversationRepository;
use App\State\Common\BoutiqueAwareProviderTrait;

/** @implements ProviderInterface<ConversationResource> */
final class ConversationProvider implements ProviderInterface
{
    use BoutiqueAwareProviderTrait;

    public function __construct(
        private ConversationRepository $repository,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ConversationResource|array|null
    {
        $boutique = $this->resolveBoutiqueFromRequest($context);

        if (!$boutique) {
            return null;
        }

        $id = $uriVariables['id'] ?? null;

        if (null !== $id) {
            return $this->mapSingle($this->repository->findOneBy([
                'id' => $id,
                'boutique' => $boutique,
            ]));
        }

        $items = $this->repository->findBy(
            ['boutique' => $boutique],
            ['createdAt' => 'DESC'],
        );

        return array_map(fn (Conversation $c) => $this->mapSingle($c), $items);
    }

    private function mapSingle(?Conversation $conversation): ?ConversationResource
    {
        if (null === $conversation) {
            return null;
        }

        $resource = new ConversationResource();
        $resource->id = (string) $conversation->getId();
        $resource->boutiqueId = (string) $conversation->getBoutique()->getId();
        $resource->userId = $conversation->getUser()?->getId()?->jsonSerialize();
        $resource->guestName = $conversation->getGuestName();
        $resource->guestEmail = $conversation->getGuestEmail();
        $resource->guestPhone = $conversation->getGuestPhone();
        $resource->active = $conversation->isActive();
        $resource->createdAt = $conversation->getCreatedAt()->format('c');
        $resource->updatedAt = $conversation->getUpdatedAt()?->format('c');
        $resource->unreadCount = $conversation->getUnreadCount();

        $last = $conversation->getLastMessage();
        if (null !== $last) {
            $resource->lastMessage = [
                'content' => $last->getContent(),
                'senderType' => $last->getSenderType(),
                'createdAt' => $last->getCreatedAt()->format('c'),
            ];
        }

        return $resource;
    }
}
