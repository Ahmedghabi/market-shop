<?php

namespace App\Command;

use App\Entity\BoutiqueDeliveryAccount;
use App\Service\Delivery\DeliveryApiClient;
use App\Service\Delivery\EncryptionService;
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
        private readonly DeliveryApiClient $apiClient,
        private readonly EncryptionService $encryption,
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

            $company = $account->getDeliveryCompany();

            try {
                $login = $this->encryption->decrypt($account->getEncryptedLogin());
                $password = $this->encryption->decrypt($account->getEncryptedPassword());
            } catch (\RuntimeException $e) {
                $account->markAsUnverified('Erreur déchiffrement');
                $output->writeln(sprintf('  [erreur] Compte #%s — déchiffrement impossible', $account->getId()));
                ++$checked;
                continue;
            }

            $result = $this->apiClient->verifyCredentials($company, $login, $password);

            if ($result['success']) {
                $account->markAsVerified();
                $output->writeln(sprintf('  [vérifié] Compte #%s — %s', $account->getId(), $company->getName()));
            } else {
                $account->markAsUnverified($result['message'] ?? 'Échec vérification');
                $output->writeln(sprintf('  [échec] Compte #%s — %s: %s', $account->getId(), $company->getName(), $result['message'] ?? ''));
            }

            ++$checked;
        }

        $this->em->flush();

        $output->writeln(sprintf('Vérifié(s) : %d compte(s).', $checked));

        return Command::SUCCESS;
    }
}
