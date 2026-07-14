<?php

namespace App\Command;

use App\Entity\Theme;
use App\Repository\ThemeRepository;
use App\Service\Theme\ThemePresetRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:seed:themes',
    description: 'Seed storefront visual themes.',
)]
final class SeedThemesCommand extends Command
{
    public function __construct(
        private readonly ThemePresetRegistry $presets,
        private readonly ThemeRepository $themes,
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        unset($input);

        $defaultCode = $this->presets->defaultCode();
        $created = 0;
        $updated = 0;

        foreach ($this->presets->all() as $code => $preset) {
            $theme = $this->themes->findOneByCode($code);
            $isDefault = $code === $defaultCode;

            if (!$theme instanceof Theme) {
                $theme = new Theme(
                    name: $preset['name'],
                    code: $code,
                    previewImage: sprintf('/images/themes/%s.jpg', $code),
                    isActive: true,
                    isDefault: $isDefault,
                );
                $this->em->persist($theme);
                ++$created;
                $output->writeln(sprintf('  [créé] %s (%s)', $preset['name'], $code));
                continue;
            }

            $theme->setName($preset['name']);
            $theme->setPreviewImage(sprintf('/images/themes/%s.jpg', $code));
            $theme->setIsActive(true);
            $theme->setIsDefault($isDefault);
            ++$updated;
            $output->writeln(sprintf('  [mis à jour] %s (%s)', $preset['name'], $code));
        }

        $this->themes->clearDefault(null);
        $default = $this->themes->findOneByCode($defaultCode);
        if ($default instanceof Theme) {
            $default->setIsDefault(true);
        }

        $this->em->flush();

        $output->writeln(sprintf('Terminé : %d créé(s), %d mis à jour.', $created, $updated));

        return Command::SUCCESS;
    }
}
