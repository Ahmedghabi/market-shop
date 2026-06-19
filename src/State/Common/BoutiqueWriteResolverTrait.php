<?php

namespace App\State\Common;

use App\Entity\Boutique;
use App\Repository\BoutiqueRepository;
use App\Security\BoutiqueContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait BoutiqueWriteResolverTrait
{
    private function resolveBoutiqueForWrite(mixed $data, array $uriVariables, array $context): Boutique
    {
        $request = $context['request'] ?? null;
        $boutique = $request instanceof Request ? $request->attributes->get('_boutique') : null;

        if (!$boutique instanceof Boutique) {
            $boutiqueId = $this->resolveBoutiqueIdForWrite($data, $request, $uriVariables);
            if ('' !== $boutiqueId && isset($this->boutiques) && $this->boutiques instanceof BoutiqueRepository) {
                $boutique = $this->boutiques->findBySlugOrId($boutiqueId) ?? $this->boutiques->find($boutiqueId);
            }
        }

        if (!$boutique instanceof Boutique && isset($this->context) && $this->context instanceof BoutiqueContext) {
            $boutiqueId = $this->context->getBoutiqueId();
            if (null !== $boutiqueId && isset($this->boutiques) && $this->boutiques instanceof BoutiqueRepository) {
                $boutique = $this->boutiques->find((string) $boutiqueId);
            }
        }

        if (!$boutique instanceof Boutique) {
            throw new NotFoundHttpException('Boutique not found');
        }

        if (isset($this->context) && $this->context instanceof BoutiqueContext && !$this->context->canAccessBoutique($boutique)) {
            throw new AccessDeniedHttpException('Access denied');
        }

        return $boutique;
    }

    private function resolveBoutiqueIdForWrite(mixed $data, ?Request $request, array $uriVariables): string
    {
        $dataBoutiqueId = is_object($data) && property_exists($data, 'boutiqueId') ? $data->boutiqueId : null;
        if (is_string($dataBoutiqueId) && '' !== $dataBoutiqueId) {
            return $dataBoutiqueId;
        }

        $queryBoutiqueId = $request?->query->get('boutiqueId');
        if (is_string($queryBoutiqueId) && '' !== $queryBoutiqueId) {
            return $queryBoutiqueId;
        }

        $uriBoutiqueId = $uriVariables['boutiqueId'] ?? null;

        return is_string($uriBoutiqueId) ? $uriBoutiqueId : '';
    }
}
