<?php

namespace App\ApiResource\Common;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\State\Common\FrontendBootstrapProvider;

#[ApiResource(
    shortName: 'FrontendBootstrap',
    operations: [
        new Get(uriTemplate: '/frontend-bootstrap', provider: FrontendBootstrapProvider::class),
    ],
)]
final class FrontendBootstrapResource
{
    /** @var array<int, array<string, string>> */
    public array $boutiques = [];

    /** @var array<int, array<string, string>> */
    public array $records = [];

    /** @var array<int, array<string, string>> */
    public array $chatMessages = [];

    /** @var array<string, string> */
    public array $session = [];

    /** @var array<int, array{label: string, color: string}> */
    public array $designTokens = [];

    /**
     * @param array<int, array<string, string>>               $boutiques
     * @param array<int, array<string, string>>               $records
     * @param array<int, array<string, string>>               $chatMessages
     * @param array<string, string>                           $session
     * @param array<int, array{label: string, color: string}> $designTokens
     */
    public function __construct(array $boutiques, array $records, array $chatMessages, array $session, array $designTokens = [])
    {
        $this->boutiques = $boutiques;
        $this->records = $records;
        $this->chatMessages = $chatMessages;
        $this->session = $session;
        $this->designTokens = $designTokens;
    }
}
