<?php

namespace App\State\Common;

use App\Entity\Boutique;
use App\Repository\BoutiqueRepository;
use App\Security\BoutiqueContext;
use Symfony\Component\HttpFoundation\Request;

trait BoutiqueAwareProviderTrait
{
    private function resolveBoutiqueFromRequest(array $context, array $uriVariables = []): ?Boutique
    {
        $request = $context['request'] ?? null;
        if ($request instanceof Request) {
            $boutique = $request->attributes->get('_boutique');
            if (null !== $boutique) {
                return $boutique;
            }

            $boutiqueId = $request->query->get('boutiqueId');
            if (null !== $boutiqueId && isset($this->boutiques) && $this->boutiques instanceof BoutiqueRepository) {
                return $this->boutiques->find((string) $boutiqueId);
            }

            $boutiqueSlug = $request->query->get('boutiqueSlug');
            if (null !== $boutiqueSlug && isset($this->boutiques) && $this->boutiques instanceof BoutiqueRepository) {
                $boutique = $this->boutiques->findBySlug((string) $boutiqueSlug);
                if (null !== $boutique) {
                    return $boutique;
                }
            }
        }

        $boutiqueId = $uriVariables['boutiqueId'] ?? null;
        if (is_string($boutiqueId) && '' !== $boutiqueId && isset($this->boutiques) && $this->boutiques instanceof BoutiqueRepository) {
            return $this->boutiques->find((string) $boutiqueId);
        }

        if (isset($this->context) && $this->context instanceof BoutiqueContext) {
            $boutiqueId = $this->context->getBoutiqueId();
            if (null !== $boutiqueId && isset($this->boutiques) && $this->boutiques instanceof BoutiqueRepository) {
                return $this->boutiques->find((string) $boutiqueId);
            }
        }

        return null;
    }
}
