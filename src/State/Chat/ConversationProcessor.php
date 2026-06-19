<?php

namespace App\State\Chat;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Chat\ConversationResource;
use App\Entity\Conversation;
use App\Entity\Boutique;
use App\Entity\User;
use App\Repository\BoutiqueRepository;
use App\Repository\ConversationRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/** @implements ProcessorInterface<ConversationResource> */
final class ConversationProcessor implements ProcessorInterface
{
    public function __construct(
        private ConversationRepository $repository,
        private BoutiqueRepository $boutiqueRepository,
        private TokenStorageInterface $tokenStorage,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ConversationResource
    {
        $boutiqueId = $uriVariables['boutiqueId'] ?? null;
        $boutique = $this->boutiqueRepository->find($boutiqueId);

        if (!$boutique instanceof Boutique) {
            throw new NotFoundHttpException('Boutique not found');
        }

        $id = $uriVariables['id'] ?? null;

        if (null !== $id) {
            $conversation = $this->repository->findOneBy([
                'id' => $id,
                'boutique' => $boutique,
            ]);

            if (!$conversation instanceof Conversation) {
                throw new NotFoundHttpException('Conversation not found');
            }

            $conversation->setActive($data->active ?? $conversation->isActive());

            if (null !== $data->guestName) {
                $conversation->setGuestName($data->guestName);
            }
            if (null !== $data->guestEmail) {
                $conversation->setGuestEmail($data->guestEmail);
            }
            if (null !== $data->guestPhone) {
                $conversation->setGuestPhone($data->guestPhone);
            }

            $this->repository->save($conversation, true);

            return $this->mapToResource($conversation);
        }

        $token = $this->tokenStorage->getToken();
        $user = $token?->getUser();

        $conversation = new Conversation($boutique, $user instanceof User ? $user : null);

        if (null !== $data->guestName) {
            $conversation->setGuestName($data->guestName);
        }
        if (null !== $data->guestEmail) {
            $conversation->setGuestEmail($data->guestEmail);
        }
        if (null !== $data->guestPhone) {
            $conversation->setGuestPhone($data->guestPhone);
        }

        $this->repository->save($conversation, true);

        return $this->mapToResource($conversation);
    }

    private function mapToResource(Conversation $conversation): ConversationResource
    {
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

        return $resource;
    }
}
