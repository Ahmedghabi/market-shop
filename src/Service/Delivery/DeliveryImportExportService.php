<?php

namespace App\Service\Delivery;

use App\Entity\DeliveryCompany;
use App\Entity\DeliveryEndpoint;
use App\Enum\DeliveryAuthType;
use App\Enum\DeliveryEndpointType;
use App\Enum\DeliveryHttpMethod;
use App\Enum\DeliveryResponseType;
use App\Repository\DeliveryCompanyRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Validates and imports a delivery company JSON configuration, and exports
 * an existing company back to the same JSON shape (never including secrets,
 * since credentials live per-boutique in BoutiqueDeliveryAccount, not on the
 * company configuration itself).
 */
final class DeliveryImportExportService
{
    public function __construct(
        private readonly DeliveryVariableRegistry $variables,
        private readonly DeliveryCompanyRepository $companies,
        private readonly EntityManagerInterface $em,
    ) {
    }

    /** @return list<string> validation error messages, empty when the config is valid */
    public function validate(array $config): array
    {
        $errors = [];

        if (empty($config['name']) || !is_string($config['name'])) {
            $errors[] = "Le champ 'name' est obligatoire.";
        }

        if (empty($config['baseUrl']) || !is_string($config['baseUrl'])) {
            $errors[] = "Le champ 'baseUrl' est obligatoire.";
        }

        $provider = (string) ($config['provider'] ?? '');
        if ('' === $provider) {
            $errors[] = "Le champ 'provider' est obligatoire.";
        }

        $authType = (string) ($config['auth']['type'] ?? 'none');
        if (null === DeliveryAuthType::tryFrom($authType)) {
            $errors[] = sprintf("Type d'authentification inconnu : '%s'.", $authType);
        } else {
            $errors = array_merge($errors, $this->validateAuthCompatibility(DeliveryAuthType::from($authType), (array) ($config['auth']['config'] ?? [])));
        }

        $endpoints = (array) ($config['endpoints'] ?? []);
        if ([] === $endpoints) {
            $errors[] = "Aucun endpoint fourni : au moins 'create_shipment' est requis.";
        }
        $hasCreateShipment = false;
        foreach ($endpoints as $index => $endpoint) {
            $errors = array_merge($errors, $this->validateEndpoint($endpoint, $index));
            if (is_array($endpoint) && 'create_shipment' === ($endpoint['type'] ?? null)) {
                $hasCreateShipment = true;
            }
        }
        if ([] !== $endpoints && !$hasCreateShipment) {
            $errors[] = "L'endpoint 'create_shipment' est obligatoire.";
        }

        $mapping = (array) ($config['mapping'] ?? []);
        if ([] === $mapping) {
            $errors[] = 'Le mapping des champs est obligatoire.';
        }
        $errors = array_merge($errors, $this->validateMappingVariables($mapping));

        return $errors;
    }

    /**
     * @return array{company: ?DeliveryCompany, errors: list<string>}
     */
    public function import(array $config, bool $activate = false): array
    {
        $errors = $this->validate($config);
        if ([] !== $errors) {
            return ['company' => null, 'errors' => $errors];
        }

        $slug = (string) ($config['slug'] ?? $this->slugify((string) $config['name']));
        if (null !== $this->companies->findOneBy(['slug' => $slug])) {
            $slug .= '-'.substr(bin2hex(random_bytes(3)), 0, 6);
        }

        $company = new DeliveryCompany(
            name: (string) $config['name'],
            slug: $slug,
            baseUrl: (string) $config['baseUrl'],
            provider: (string) $config['provider'],
            authType: DeliveryAuthType::from((string) ($config['auth']['type'] ?? 'none')),
            authConfig: (array) ($config['auth']['config'] ?? []),
            mappingConfig: (array) ($config['mapping'] ?? []),
            parametersConfig: (array) ($config['parameters'] ?? []),
            logoUrl: isset($config['logoUrl']) ? (string) $config['logoUrl'] : null,
            description: isset($config['description']) ? (string) $config['description'] : null,
            isActive: $activate,
        );
        $this->em->persist($company);

        foreach ((array) ($config['endpoints'] ?? []) as $endpointConfig) {
            $endpoint = new DeliveryEndpoint(
                company: $company,
                type: DeliveryEndpointType::from((string) $endpointConfig['type']),
                name: (string) ($endpointConfig['name'] ?? $endpointConfig['type']),
                url: (string) $endpointConfig['url'],
                httpMethod: DeliveryHttpMethod::from(strtoupper((string) ($endpointConfig['method'] ?? 'POST'))),
                headers: (array) ($endpointConfig['headers'] ?? []),
                responseType: DeliveryResponseType::from((string) ($endpointConfig['responseType'] ?? 'json')),
            );
            $company->addEndpoint($endpoint);
            $this->em->persist($endpoint);
        }

        $this->em->flush();

        return ['company' => $company, 'errors' => []];
    }

    /** @return array<string, mixed> */
    public function export(DeliveryCompany $company): array
    {
        return [
            'name' => $company->getName(),
            'slug' => $company->getSlug(),
            'logoUrl' => $company->getLogoUrl(),
            'description' => $company->getDescription(),
            'provider' => $company->getProvider(),
            'baseUrl' => $company->getBaseUrl(),
            'auth' => [
                'type' => $company->getAuthType()->value,
                // Config may reference credential field names (e.g. header -> credential key),
                // never the secrets themselves: those live per-boutique and are encrypted.
                'config' => $company->getAuthConfig(),
            ],
            'endpoints' => array_map(static fn (DeliveryEndpoint $e) => [
                'type' => $e->getType()->value,
                'name' => $e->getName(),
                'url' => $e->getUrl(),
                'method' => $e->getHttpMethod()->value,
                'headers' => $e->getHeaders(),
                'responseType' => $e->getResponseType()->value,
            ], $company->getEndpoints()->toArray()),
            'mapping' => $company->getMappingConfig(),
            'parameters' => $company->getParametersConfig(),
        ];
    }

    /** @return list<string> */
    private function validateEndpoint(mixed $endpoint, int|string $index): array
    {
        $errors = [];
        if (!is_array($endpoint)) {
            return [sprintf('Endpoint #%s invalide.', $index)];
        }

        $type = (string) ($endpoint['type'] ?? '');
        if (null === DeliveryEndpointType::tryFrom($type)) {
            $errors[] = sprintf("Endpoint #%s : type inconnu '%s'.", $index, $type);
        }

        if (empty($endpoint['url']) || !is_string($endpoint['url'])) {
            $errors[] = sprintf("Endpoint #%s : le champ 'url' est obligatoire.", $index);
        }

        $method = strtoupper((string) ($endpoint['method'] ?? 'POST'));
        if (null === DeliveryHttpMethod::tryFrom($method)) {
            $errors[] = sprintf("Endpoint #%s : méthode HTTP inconnue '%s'.", $index, $method);
        }

        if (isset($endpoint['responseType']) && null === DeliveryResponseType::tryFrom((string) $endpoint['responseType'])) {
            $errors[] = sprintf("Endpoint #%s : type de réponse inconnu '%s'.", $index, $endpoint['responseType']);
        }

        return $errors;
    }

    /** @return list<string> */
    private function validateMappingVariables(array $mapping, string $path = ''): array
    {
        $errors = [];
        $known = $this->variables->knownCodes();

        foreach ($mapping as $key => $value) {
            $currentPath = '' === $path ? (string) $key : $path.'.'.$key;
            if (is_array($value)) {
                $errors = array_merge($errors, $this->validateMappingVariables($value, $currentPath));
                continue;
            }
            if (!is_string($value)) {
                continue;
            }
            preg_match_all('/\{\{\s*([a-zA-Z0-9_.]+)/', $value, $matches);
            foreach ($matches[1] ?? [] as $variable) {
                if (!in_array($variable, $known, true)) {
                    $errors[] = sprintf("Champ '%s' : variable inconnue '{{%s}}'.", $currentPath, $variable);
                }
            }
        }

        return $errors;
    }

    /** @return list<string> */
    private function validateAuthCompatibility(DeliveryAuthType $authType, array $config): array
    {
        return match ($authType) {
            DeliveryAuthType::ApiKey => isset($config['headerName']) ? [] : ["Auth 'api_key' : le paramètre 'headerName' est obligatoire."],
            DeliveryAuthType::Custom => isset($config['headers']) && is_array($config['headers']) ? [] : ["Auth 'custom' : le paramètre 'headers' (map nom-entête → clé credential) est obligatoire."],
            default => [],
        };
    }

    private function slugify(string $value): string
    {
        $slug = strtolower(trim((string) preg_replace('/[^a-zA-Z0-9]+/', '-', $value), '-'));

        return '' !== $slug ? $slug : 'company-'.substr(bin2hex(random_bytes(3)), 0, 6);
    }
}
