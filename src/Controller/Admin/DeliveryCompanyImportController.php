<?php

namespace App\Controller\Admin;

use App\Repository\DeliveryCompanyRepository;
use App\Service\Audit\AuditLogService;
use App\Service\Delivery\DeliveryEngine;
use App\Service\Delivery\DeliveryImportExportService;
use App\State\Delivery\DeliveryCompanyProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class DeliveryCompanyImportController extends AbstractController
{
    public function __construct(
        private readonly DeliveryImportExportService $importExport,
        private readonly DeliveryCompanyProvider $provider,
        private readonly DeliveryCompanyRepository $companies,
        private readonly DeliveryEngine $engine,
        private readonly AuditLogService $auditLog,
        private readonly Security $security,
    ) {
    }

    public function validateImport(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        $config = json_decode($request->getContent(), true);
        if (!is_array($config)) {
            return new JsonResponse(['valid' => false, 'errors' => ['JSON invalide.']], 400);
        }

        $errors = $this->importExport->validate($config);

        return new JsonResponse(['valid' => [] === $errors, 'errors' => $errors]);
    }

    public function import(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        $config = json_decode($request->getContent(), true);
        if (!is_array($config)) {
            return new JsonResponse(['errors' => ['JSON invalide.']], 400);
        }

        $activate = (bool) ($config['activate'] ?? false);
        unset($config['activate']);

        $result = $this->importExport->import($config, $activate);
        if (null === $result['company']) {
            return new JsonResponse(['errors' => $result['errors']], 400);
        }

        $user = $this->security->getUser();
        $this->auditLog->log(
            actorEmail: $user?->getUserIdentifier() ?? 'system',
            actorRole: 'ROLE_SUPER_ADMIN',
            action: 'delivery_company.import',
            resourceType: 'DeliveryCompany',
            resourceId: (string) $result['company']->getId(),
            details: ['name' => $result['company']->getName(), 'provider' => $result['company']->getProvider()],
        );

        return new JsonResponse($this->provider->toOutput($result['company']));
    }

    public function export(string $id): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        $company = $this->companies->find($id);
        if (!$company) {
            return new JsonResponse(['error' => 'Not found'], 404);
        }

        return new JsonResponse($this->importExport->export($company));
    }

    public function previewMapping(string $id): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        $company = $this->companies->find($id);
        if (!$company) {
            return new JsonResponse(['error' => 'Not found'], 404);
        }

        return new JsonResponse(['preview' => $this->engine->previewMapping($company)]);
    }
}
