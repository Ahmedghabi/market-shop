<?php

namespace App\State\Loyalty;

use App\Entity\Boutique;
use App\Entity\Customer;
use App\Entity\User;

/**
 * Resolves the current shop-scoped Customer record for the authenticated
 * user. A single User may have one Customer record per boutique (see
 * uniq_customer_boutique_email), so the current shop (subdomain) must
 * always be taken into account — never resolve by user alone.
 *
 * The security token user is a stateless InMemoryUser (see
 * BearerTokenAuthenticator), not the App\Entity\User itself — the real
 * entity must be re-fetched by identifier.
 */
trait LoyaltyCustomerResolverTrait
{
    private function resolveCurrentBoutiqueAndCustomer(): ?array
    {
        $boutique = $this->shopContext->getCurrentShop();
        if (!$boutique instanceof Boutique) {
            return null;
        }

        $tokenUser = $this->security->getUser();
        if (null === $tokenUser) {
            return null;
        }

        $appUser = $this->users->findOneBy(['identifier' => $tokenUser->getUserIdentifier()]);
        if (!$appUser instanceof User) {
            return null;
        }

        $customer = $this->customers->findOneBy(['user' => $appUser, 'boutique' => $boutique]);
        if (!$customer instanceof Customer) {
            return null;
        }

        return [$boutique, $customer];
    }
}
