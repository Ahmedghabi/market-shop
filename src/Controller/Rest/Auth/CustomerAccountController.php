<?php

namespace App\Controller\Rest\Auth;

use App\Entity\Customer;
use App\Entity\Order;
use App\Entity\User;
use App\Repository\CustomerLoyaltyRepository;
use App\Repository\CustomerRepository;
use App\Repository\OrderRepository;
use App\Security\BoutiqueContext;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final readonly class CustomerAccountController
{
    public function __construct(
        private CustomerRepository $customers,
        private OrderRepository $orders,
        private CustomerLoyaltyRepository $loyalties,
        private Security $security,
        private BoutiqueContext $boutiqueContext,
    ) {
    }

    #[Route('/api/account/profile', name: 'api_account_profile', methods: ['GET'])]
    public function profile(): JsonResponse
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return new JsonResponse(['message' => 'Non authentifié.'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse([
            'id' => (string) $user->getId(),
            'email' => $user->getUserIdentifier(),
            'firstname' => $user->getFirstname(),
            'lastname' => $user->getLastname(),
            'displayName' => $user->getDisplayName(),
            'phone' => $user->getPhone(),
            'roles' => $user->getRoles(),
            'status' => $user->getStatus()->value,
            'createdAt' => $user->getCreatedAt()->format('c'),
        ]);
    }

    #[Route('/api/account/orders', name: 'api_account_orders', methods: ['GET'])]
    public function orders(): JsonResponse
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return new JsonResponse(['message' => 'Non authentifié.'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $customers = $this->customers->findBy(['user' => $user]);
        $customerIds = array_map(fn (Customer $c) => $c->getId()->toRfc4122(), $customers);

        if (empty($customerIds)) {
            return new JsonResponse(['orders' => []]);
        }

        $orders = $this->orders->findBy(['customer' => $customerIds], ['createdAt' => 'DESC']);

        $result = array_map(function (Order $order) {
            return [
                'id' => (string) $order->getId(),
                'boutiqueId' => (string) $order->getBoutique()->getId(),
                'status' => $order->getStatus()->value,
                'totalCents' => $order->getTotalCents(),
                'currency' => $order->getCurrency(),
                'createdAt' => $order->getCreatedAt()->format('c'),
                'itemCount' => $order->getItems()->count(),
            ];
        }, $orders);

        return new JsonResponse(['orders' => $result]);
    }

    #[Route('/api/account/loyalty', name: 'api_account_loyalty', methods: ['GET'])]
    public function loyalty(): JsonResponse
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return new JsonResponse(['message' => 'Non authentifié.'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $customers = $this->customers->findBy(['user' => $user]);

        $result = [];
        foreach ($customers as $customer) {
            $loyalties = $this->loyalties->findBy(['customer' => $customer]);
            foreach ($loyalties as $loyalty) {
                $result[] = [
                    'id' => (string) $loyalty->getId(),
                    'boutiqueId' => (string) $loyalty->getBoutique()->getId(),
                    'boutiqueName' => $loyalty->getBoutique()->getName(),
                    'pointsBalance' => $loyalty->getPointsBalance(),
                    'totalEarned' => $loyalty->getTotalEarned(),
                    'totalUsed' => $loyalty->getTotalUsed(),
                ];
            }
        }

        return new JsonResponse(['loyalties' => $result]);
    }
}
