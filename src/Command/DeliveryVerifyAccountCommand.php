<?php

namespace App\Command;

use App\Entity\BoutiqueDeliveryAccount;
use App\Service\Delivery\DeliveryEngine;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:delivery-verify-accounts',
    description: 'Vérifie les comptes livraison des boutiques auprès des transporteurs.',
)]
final class DeliveryVerifyAccountCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly DeliveryEngine $engine,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $accounts = $this->em->getRepository(BoutiqueDeliveryAccount::class)->findAll();
        $checked = 0;

        foreach ($accounts as $account) {
            if (!$account->isActive()) {
                continue;
            }

            $result = $this->engine->testConnection($account);
            $company = $account->getDeliveryCompany();

            if ($result->success) {
                $account->markAsVerified();
                $output->writeln(sprintf('  [vérifié] Compte #%s — %s', $account->getId(), $company->getName()));
            } else {
                $account->markAsUnverified($result->errorMessage ?? 'Échec vérification');
                $output->writeln(sprintf('  [échec] Compte #%s — %s: %s', $account->getId(), $company->getName(), $result->errorMessage ?? ''));
            }

            ++$checked;
        }

        $this->em->flush();

        $output->writeln(sprintf('Vérifié(s) : %d compte(s).', $checked));

        return Command::SUCCESS;
    }
}
