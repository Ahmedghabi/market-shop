<?php

namespace App\Command;

use App\Service\Loyalty\LoyaltyEngine;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:loyalty:expire-points',
    description: 'Expire les points de fidélité arrivés à échéance selon la politique de validité de chaque programme.',
)]
final class ExpireLoyaltyPointsCommand extends Command
{
    public function __construct(
        private readonly LoyaltyEngine $engine,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $expired = $this->engine->expirePoints();

        $output->writeln(sprintf('%d lot(s) de points expiré(s).', $expired));

        return Command::SUCCESS;
    }
}
