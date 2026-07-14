<?php

namespace App\ApiResource\ExtensionRequest;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Dto\ExtensionRequest\ExtensionRequestDecisionInput;
use App\Dto\ExtensionRequest\ExtensionRequestInput;
use App\Dto\ExtensionRequest\ExtensionRequestOutput;
use App\State\ExtensionRequest\ExtensionRequestProcessor;
use App\State\ExtensionRequest\ExtensionRequestProvider;

#[ApiResource(
    shortName: 'ExtensionRequest',
    operations: [
        new GetCollection(
            uriTemplate: '/extension-requests',
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            output: ExtensionRequestOutput::class,
            provider: ExtensionRequestProvider::class,
        ),
        new GetCollection(
            uriTemplate: '/admin/extension-requests',
            security: "is_granted('ROLE_SUPER_ADMIN')",
            output: ExtensionRequestOutput::class,
            provider: ExtensionRequestProvider::class,
        ),
        new Post(
            uriTemplate: '/extension-requests',
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            read: false,
            input: ExtensionRequestInput::class,
            output: ExtensionRequestOutput::class,
            processor: ExtensionRequestProcessor::class,
        ),
        new Get(
            uriTemplate: '/extension-requests/{id}',
            uriVariables: ['id' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id')],
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            output: ExtensionRequestOutput::class,
            provider: ExtensionRequestProvider::class,
        ),
        new Patch(
            name: 'pay_extension_request',
            uriTemplate: '/extension-requests/{id}/pay',
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            read: false,
            input: false,
            output: ExtensionRequestOutput::class,
            processor: ExtensionRequestProcessor::class,
        ),
        new Patch(
            name: 'cancel_extension_request',
            uriTemplate: '/extension-requests/{id}/cancel',
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            read: false,
            input: false,
            output: ExtensionRequestOutput::class,
            processor: ExtensionRequestProcessor::class,
        ),
        new Patch(
            name: 'approve_extension_request',
            uriTemplate: '/admin/extension-requests/{id}/approve',
            security: "is_granted('ROLE_SUPER_ADMIN')",
            read: false,
            input: ExtensionRequestDecisionInput::class,
            output: ExtensionRequestOutput::class,
            processor: ExtensionRequestProcessor::class,
        ),
        new Patch(
            name: 'reject_extension_request',
            uriTemplate: '/admin/extension-requests/{id}/reject',
            security: "is_granted('ROLE_SUPER_ADMIN')",
            read: false,
            input: ExtensionRequestDecisionInput::class,
            output: ExtensionRequestOutput::class,
            processor: ExtensionRequestProcessor::class,
        ),
        new Patch(
            name: 'suspend_extension_request',
            uriTemplate: '/admin/extension-requests/{id}/suspend',
            security: "is_granted('ROLE_SUPER_ADMIN')",
            read: false,
            input: ExtensionRequestDecisionInput::class,
            output: ExtensionRequestOutput::class,
            processor: ExtensionRequestProcessor::class,
        ),
    ],
)]
final class ExtensionRequestResource
{
    public ?string $id = null;
    public ?string $boutiqueId = null;
    public ?string $boutiqueName = null;
    public ?string $extensionId = null;
    public ?string $extensionCode = null;
    public ?string $extensionName = null;
    public ?string $extensionType = null;
    public int $priceTnd = 0;
    public string $status = 'draft';
    public ?string $comment = null;
    public ?string $adminComment = null;
    public ?string $invoiceId = null;
    public ?string $requestedAt = null;
    public ?string $paidAt = null;
    public ?string $decidedAt = null;
    public ?string $decidedBy = null;
    public ?string $grantId = null;
    public ?string $grantExpiresAt = null;
}
