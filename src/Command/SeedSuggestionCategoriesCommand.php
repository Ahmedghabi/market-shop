<?php

namespace App\Command;

use App\Entity\SuggestionCategory;
use App\Repository\SuggestionCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:seed:suggestion-categories', description: 'Seed default suggestion categories')]
final class SeedSuggestionCategoriesCommand extends Command
{
    private const CATEGORIES = [
        ['name' => 'Produit', 'slug' => 'produit'],
        ['name' => 'Commande', 'slug' => 'commande'],
        ['name' => 'Client', 'slug' => 'client'],
        ['name' => 'Stock', 'slug' => 'stock'],
        ['name' => 'Paiement', 'slug' => 'paiement'],
        ['name' => 'Livraison', 'slug' => 'livraison'],
        ['name' => 'Back Office', 'slug' => 'back-office'],
        ['name' => 'Front Office', 'slug' => 'front-office'],
        ['name' => 'Performance', 'slug' => 'performance'],
        ['name' => 'Sécurité', 'slug' => 'securite'],
        ['name' => 'Nouvelle fonctionnalité', 'slug' => 'nouvelle-fonctionnalite'],
        ['name' => 'Autre', 'slug' => 'autre'],
    ];

    public function __construct(
        private readonly SuggestionCategoryRepository $categories,
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        unset($input);
        $created = 0;

        foreach (self::CATEGORIES as $position => $data) {
            if ($this->categories->findOneBy(['slug' => $data['slug']])) {
                continue;
            }

            $this->em->persist(new SuggestionCategory($data['name'], $data['slug'], position: $position));
            ++$created;
        }

        $this->em->flush();
        $output->writeln(sprintf('Seeded %d suggestion categor(y/ies).', $created));

        return Command::SUCCESS;
    }
}
