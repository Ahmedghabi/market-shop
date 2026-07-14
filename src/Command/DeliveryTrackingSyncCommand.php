<?php

namespace App\Command;

use App\Message\SyncShipmentTrackingMessage;
use App\Repository\ShipmentRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:delivery-tracking-sync',
    description: 'Dispatche une synchronisation de tracking (async) pour chaque expédition non finalisée.',
)]
final class DeliveryTrackingSyncCommand extends Command
{
    public function __construct(
        private readonly ShipmentRepository $shipments,
        private readonly MessageBusInterface $bus,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $shipments = $this->shipments->findNonFinal();

        foreach ($shipments as $shipment) {
            $this->bus->dispatch(new SyncShipmentTrackingMessage((string) $shipment->getId()));
        }

        $output->writeln(sprintf('Synchronisation planifiée pour %d expédition(s).', count($shipments)));

        return Command::SUCCESS;
    }
}
