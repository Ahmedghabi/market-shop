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
    name: 'app:delivery-process',
    description: 'Soumet les commandes payées aux transporteurs puis marque comme livrées.',
)]
final class DeliveryProcessCommand extends Command
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
        $processed = 0;

        foreach ($this->repository->findPaid() as $order) {
            $result = $this->submitter->submit($order);
            if ($result['success']) {
                $output->writeln(sprintf('  [envoyée] Commande #%s — %s', $order->getId(), $result['tracking'] ?? ''));
            } else {
                $output->writeln(sprintf('  [erreur] Commande #%s — %s', $order->getId(), $result['error'] ?? ''));
            }
            ++$processed;
        }

        foreach ($this->repository->findShippedNotDelivered() as $order) {
            $order->markAsDelivered();
            $output->writeln(sprintf('  [livrée] Commande #%s', $order->getId()));
            ++$processed;
        }

        $this->em->flush();

        if (0 === $processed) {
            $output->writeln('Aucune commande à traiter.');
        } else {
            $output->writeln(sprintf('Traitée(s) : %d commande(s).', $processed));
        }

        return Command::SUCCESS;
    }
}
