<?php

namespace App\Service;

use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\AutoEncoder;
use Intervention\Image\ImageManager;

final class ImageService
{
    private const int QUALITY = 80;
    private const int SMALL_MAX = 300;
    private const int LARGE_MAX = 1920;

    private ?ImageManager $manager = null;

    public function __construct(
        private readonly string $uploadDir,
    ) {
    }

    public function uploadAndResize(\SplFileInfo $file, string $subDir = 'products'): array
    {
        $extension = strtolower($file->getExtension());
        $filename = sprintf('%s.%s', bin2hex(random_bytes(16)), $extension);
        $relativeDir = sprintf('uploads/%s/%s', $subDir, date('Y/m'));
        $absoluteDir = sprintf('%s/%s', $this->uploadDir, $relativeDir);

        if (!is_dir($absoluteDir)) {
            mkdir($absoluteDir, 0755, true);
        }

        $originalPath = sprintf('%s/%s', $absoluteDir, $filename);
        $smallPath = sprintf('%s/small_%s', $absoluteDir, $filename);
        $largePath = sprintf('%s/large_%s', $absoluteDir, $filename);

        $image = $this->getManager()->read($file->getPathname());

        $image->save($originalPath);

        $large = $image->scaleDown(width: self::LARGE_MAX, height: self::LARGE_MAX);
        $large->encode(new AutoEncoder(quality: self::QUALITY));
        $large->save($largePath);

        $small = $image->scaleDown(width: self::SMALL_MAX, height: self::SMALL_MAX);
        $small->encode(new AutoEncoder(quality: 60));
        $small->save($smallPath);

        return [
            'url' => sprintf('/%s/%s', $relativeDir, $filename),
            'smallUrl' => sprintf('/%s/small_%s', $relativeDir, $filename),
            'largeUrl' => sprintf('/%s/large_%s', $relativeDir, $filename),
        ];
    }

    public function uploadRaw(\SplFileInfo $file, string $subDir = 'chat'): array
    {
        $extension = strtolower($file->getExtension());
        $filename = sprintf('%s.%s', bin2hex(random_bytes(16)), $extension);
        $relativeDir = sprintf('uploads/%s/%s', $subDir, date('Y/m'));
        $absoluteDir = sprintf('%s/%s', $this->uploadDir, $relativeDir);

        if (!is_dir($absoluteDir)) {
            mkdir($absoluteDir, 0755, true);
        }

        $destPath = sprintf('%s/%s', $absoluteDir, $filename);
        copy($file->getPathname(), $destPath);

        return [
            'url' => sprintf('/%s/%s', $relativeDir, $filename),
        ];
    }

    private function getManager(): ImageManager
    {
        if (!$this->manager instanceof ImageManager) {
            $this->manager = new ImageManager(new Driver());
        }

        return $this->manager;
    }
}
