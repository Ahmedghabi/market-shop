<?php

namespace App\Controller\Admin;

use App\Service\AppConfigService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class AppConfigController extends AbstractController
{
    public function __construct(private AppConfigService $config)
    {
    }

    public function getConfig(): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        return new JsonResponse($this->config->get());
    }

    public function updateConfig(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return new JsonResponse(['error' => 'Invalid JSON'], 400);
        }

        $errors = $this->config->validate($data);
        if ([] !== $errors) {
            return new JsonResponse(['error' => 'Invalid config payload', 'details' => $errors], 400);
        }

        return new JsonResponse($this->config->update($data));
    }
}
