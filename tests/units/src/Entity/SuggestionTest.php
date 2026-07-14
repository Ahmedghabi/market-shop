<?php

namespace App\Tests\Entity;

use App\Entity\Boutique;
use App\Entity\Suggestion;
use App\Entity\User;
use App\Enum\SuggestionStatus;
use App\Enum\SuggestionVisibility;
use PHPUnit\Framework\TestCase;

final class SuggestionTest extends TestCase
{
    public function testSuggestionDerivesTenantFromBoutiqueAndStartsSubmittedByProcessorContract(): void
    {
        $boutique = new Boutique('Demo', 'demo');
        $user = new User($boutique, 'admin@example.test');
        $suggestion = new Suggestion($boutique, $user, 'Titre', 'Description');

        self::assertSame((string) $boutique->getId(), (string) $suggestion->getTenantId());
        self::assertSame($boutique, $suggestion->getBoutique());
        self::assertSame(SuggestionStatus::DRAFT, $suggestion->getStatus());
        self::assertSame(SuggestionVisibility::PrivateVisibility, $suggestion->getVisibility());
    }

    public function testPublishingAndClosingKeepPublicStateConsistent(): void
    {
        $boutique = new Boutique('Demo', 'demo');
        $suggestion = new Suggestion($boutique, new User($boutique, 'admin@example.test'), 'Titre', 'Description');

        $suggestion->setStatus(SuggestionStatus::ACCEPTED);
        $suggestion->setVisibility(SuggestionVisibility::PUBLIC);
        $suggestion->publish();

        self::assertTrue($suggestion->isPublished());
        self::assertNotNull($suggestion->getPublishedAt());

        $suggestion->close();

        self::assertSame(SuggestionStatus::ARCHIVED, $suggestion->getStatus());
        self::assertFalse($suggestion->isPublished());
        self::assertNotNull($suggestion->getClosedAt());
    }
}
