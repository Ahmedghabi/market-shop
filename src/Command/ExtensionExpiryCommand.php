<?php

namespace App\Command;

use App\Entity\BoutiqueExtension;
use App\Entity\Notification;
use App\Repository\BoutiqueExtensionRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:extension-expiry',
    description: 'Verifie les extensions (module/theme/quota) qui expirent et desactive celles qui sont echues.',
)]
final class ExtensionExpiryCommand extends Command
{
    public function __construct(
        private readonly BoutiqueExtensionRepository $repository,
        private readonly UserRepository $users,
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $now = new \DateTimeImmutable();
        $notified = 0;

        $expiringSoon = $this->repository->findExpiringSoon($now, $now->modify('+3 days'));
        foreach ($expiringSoon as $grant) {
            $boutique = $grant->getBoutique();
            $message = sprintf(
                'L\'extension "%s" de la boutique "%s" expire le %s.',
                $grant->getExtension()->getName(),
                $boutique->getName(),
                $grant->getExpiresAt()?->format('d/m/Y') ?? '?',
            );

            $this->notifyBoutiqueAdmins($grant, 'extension_expiring', $message);
            $this->notifySuperAdmins($grant, 'extension_expiring', $message);
            $grant->markExpiryNotified();
            ++$notified;
            $output->writeln(sprintf('  [bientot expire] %s — %s', $boutique->getName(), $grant->getExtension()->getName()));
        }

        $expired = $this->repository->findExpiredButStillActive($now);
        foreach ($expired as $grant) {
            $boutique = $grant->getBoutique();
            $message = sprintf(
                'L\'extension "%s" de la boutique "%s" a expire et a ete desactivee.',
                $grant->getExtension()->getName(),
                $boutique->getName(),
            );

            $grant->deactivate();
            $this->notifyBoutiqueAdmins($grant, 'extension_expired', $message);
            $this->notifySuperAdmins($grant, 'extension_expired', $message);
            ++$notified;
            $output->writeln(sprintf('  [expire] %s — %s', $boutique->getName(), $grant->getExtension()->getName()));
        }

        $this->em->flush();

        if (0 === $notified) {
            $output->writeln('Aucune extension a notifier.');
        }

        $output->writeln(sprintf('Notified %d extension grant(s).', $notified));

        return Command::SUCCESS;
    }

    private function notifyBoutiqueAdmins(BoutiqueExtension $grant, string $type, string $message): void
    {
        $boutique = $grant->getBoutique();
        foreach ($boutique->getUsers() as $user) {
            $this->em->persist(new Notification(
                recipientIdentifier: $user->getUserIdentifier(),
                type: $type,
                title: 'Extension',
                message: $message,
                boutique: $boutique,
            ));
        }
    }

    private function notifySuperAdmins(BoutiqueExtension $grant, string $type, string $message): void
    {
        foreach ($this->users->findByRole('ROLE_SUPER_ADMIN') as $user) {
            $this->em->persist(new Notification(
                recipientIdentifier: $user->getUserIdentifier(),
                type: $type,
                title: 'Extension',
                message: $message,
                boutique: $grant->getBoutique(),
            ));
        }
    }
}
