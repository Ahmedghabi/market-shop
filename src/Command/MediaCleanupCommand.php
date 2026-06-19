<?php

namespace App\Command;

use App\Repository\MediaRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'media:cleanup', description: 'Remove orphan media files and unused thumbnails')]
final class MediaCleanupCommand extends Command
{
    public function __construct(
        private readonly MediaRepository $media,
        private readonly string $uploadDir,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->section('Scanning for orphan files...');
        $removed = $this->removeOrphanFiles($io);

        $io->section('Scanning for unused thumbnails...');
        $removed += $this->removeUnusedThumbnails($io);

        $io->success(sprintf('Cleanup complete. Removed %d files.', $removed));

        return Command::SUCCESS;
    }

    private function removeOrphanFiles(SymfonyStyle $io): int
    {
        $knownPaths = $this->media->findAllPaths();
        $knownPaths = array_map(fn (string $path): string => sprintf('%s/%s', $this->uploadDir, $path), $knownPaths);

        $absoluteUploadDir = $this->uploadDir;
        $orphans = 0;

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($absoluteUploadDir, \RecursiveDirectoryIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            if (!$file instanceof \SplFileInfo) {
                continue;
            }
            if (!$file->isFile()) {
                continue;
            }

            $path = $file->getPathname();

            if ('webp' === $file->getExtension() && preg_match('/_(small|medium|large)\.webp$/', $path)) {
                continue;
            }

            if (!in_array($path, $knownPaths, true)) {
                @unlink($path);
                ++$orphans;
            }
        }

        $io->text(sprintf('Removed %d orphan files.', $orphans));

        return $orphans;
    }

    private function removeUnusedThumbnails(SymfonyStyle $io): int
    {
        $usedThumbnails = $this->media->findAllThumbnailPaths();
        $usedThumbnails = array_map(fn (string $path): string => sprintf('%s/%s', $this->uploadDir, $path), $usedThumbnails);

        $removed = 0;
        $absoluteUploadDir = $this->uploadDir;

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($absoluteUploadDir, \RecursiveDirectoryIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            if (!$file instanceof \SplFileInfo) {
                continue;
            }
            if (!$file->isFile()) {
                continue;
            }
            if (!preg_match('/_(small|medium|large)\.webp$/', $file->getFilename())) {
                continue;
            }

            if (!in_array($file->getPathname(), $usedThumbnails, true)) {
                @unlink($file->getPathname());
                ++$removed;
            }
        }

        $io->text(sprintf('Removed %d unused thumbnails.', $removed));

        return $removed;
    }
}
