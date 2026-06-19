<?php

namespace App\Command;

use App\Repository\OrderRepository;
use App\Service\Delivery\DeliveryOrderSubmitter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:delivery-retry',
    description: 'Renvoie les commandes en échec de livraison toutes les 1h (max 5 tentatives).',
)]
final class DeliveryRetryCommand extends Command
{
    public function __construct(
        private readonly OrderRepository $repository,
        private readonly EntityManagerInterface $em,
        private readonly DeliveryOrderSubmitter $submitter,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $retried = 0;

        foreach ($this->repository->findDeliveryFailedForRetry(5) as $order) {
            $result = $this->submitter->submit($order);
            if ($result['success']) {
                $output->writeln(sprintf('  [renvoyée] Commande #%s — %s', $order->getId(), $result['tracking'] ?? ''));
            } else {
                $output->writeln(sprintf('  [tentative %d/5 échouée] Commande #%s — %s', $order->getDeliveryRetryCount(), $order->getId(), $result['error'] ?? ''));
            }
            ++$retried;
        }

        $this->em->flush();

        if (0 === $retried) {
            $output->writeln('Aucune commande à renvoyer.');
        } else {
            $output->writeln(sprintf('Tentative(s) : %d commande(s).', $retried));
        }

        return Command::SUCCESS;
    }
}
