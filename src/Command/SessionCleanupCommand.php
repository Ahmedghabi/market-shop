<?php

namespace App\Command;

use App\Service\Session\SessionService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'session:cleanup', description: 'Remove expired and inactive user sessions.')]
final class SessionCleanupCommand extends Command
{
    public function __construct(private readonly SessionService $sessions)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $count = $this->sessions->cleanupExpired();
        $io->success(sprintf('Cleaned up %d expired/inactive sessions.', $count));

        return Command::SUCCESS;
    }
}
