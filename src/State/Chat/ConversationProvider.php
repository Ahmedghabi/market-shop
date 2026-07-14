<?php

namespace App\State\Chat;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Chat\ConversationResource;
use App\Entity\Conversation;
use App\Repository\ConversationRepository;
use App\Service\Chat\ChatAccessService;
use App\State\Common\BoutiqueAwareProviderTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/** @implements ProviderInterface<ConversationResource> */
final class ConversationProvider implements ProviderInterface
{
    use BoutiqueAwareProviderTrait;

    public function __construct(
        private ConversationRepository $repository,
        private ChatAccessService $access,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ConversationResource|array|null
    {
        $id = $uriVariables['id'] ?? null;

        if (null !== $id) {
            $conversation = $this->repository->find($id);

            if (!$conversation instanceof Conversation) {
                return null;
            }

            if (!$this->access->canAccessConversation($conversation, $this->getGuestToken($context))) {
                throw new AccessDeniedHttpException('Conversation access denied');
            }

            return $this->mapSingle($conversation);
        }

        if ($this->access->canManageAllConversations()) {
            $items = $this->repository->findBy([], ['createdAt' => 'DESC']);

            return array_map(fn (Conversation $c) => $this->mapSingle($c), $items);
        }

        $boutique = $this->resolveBoutiqueFromRequest($context);

        if (!$boutique) {
            return [];
        }

        $items = $this->repository->findBy(
            ['boutique' => $boutique],
            ['createdAt' => 'DESC'],
        );

        return array_map(fn (Conversation $c) => $this->mapSingle($c), $items);
    }

    private function getGuestToken(array $context): ?string
    {
        $request = $context['request'] ?? null;

        return $request instanceof Request ? $request->headers->get('X-Guest-Chat-Token') : null;
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
        $resource->guestAccessToken = null;
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
