<?php

namespace App\Service;

final class ImageCompressionService
{
    private const int DEFAULT_QUALITY = 80;
    private const int LARGE_MAX_DIMENSION = 1920;
    private const int SMALL_MAX_DIMENSION = 300;

    public function compressAndResize(string $sourcePath, string $destinationPath, ?int $maxDimension = null, ?int $quality = null): void
    {
        $maxDimension ??= self::LARGE_MAX_DIMENSION;
        $quality ??= self::DEFAULT_QUALITY;

        $imageInfo = @getimagesize($sourcePath);
        if (false === $imageInfo) {
            copy($sourcePath, $destinationPath);

            return;
        }

        [$width, $height] = $imageInfo;
        $mimeType = $imageInfo['mime'];

        if ($width <= $maxDimension && $height <= $maxDimension && 'image/jpeg' === $mimeType) {
            $this->compressJpeg($sourcePath, $destinationPath, $quality);

            return;
        }

        $srcImage = match ($mimeType) {
            'image/jpeg' => @imagecreatefromjpeg($sourcePath),
            'image/png' => @imagecreatefrompng($sourcePath),
            'image/gif' => @imagecreatefromgif($sourcePath),
            'image/webp' => @imagecreatefromwebp($sourcePath),
            'image/avif' => function_exists('imagecreatefromavif') ? @imagecreatefromavif($sourcePath) : false,
            default => false,
        };

        if (false === $srcImage) {
            copy($sourcePath, $destinationPath);

            return;
        }

        $ratio = min($maxDimension / $width, $maxDimension / $height, 1.0);
        $newWidth = (int) round($width * $ratio);
        $newHeight = (int) round($height * $ratio);

        $dstImage = imagecreatetruecolor($newWidth, $newHeight);

        if ('image/png' === $mimeType || 'image/webp' === $mimeType) {
            imagealphablending($dstImage, false);
            imagesavealpha($dstImage, true);
        }

        imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        $extension = strtolower(pathinfo($destinationPath, PATHINFO_EXTENSION));

        match ($extension) {
            'png' => imagepng($dstImage, $destinationPath, (int) round($quality / 10)),
            'gif' => imagegif($dstImage, $destinationPath),
            'webp' => imagewebp($dstImage, $destinationPath, $quality),
            'avif' => function_exists('imageavif') ? imageavif($dstImage, $destinationPath, $quality) : imagejpeg($dstImage, $destinationPath, $quality),
            default => imagejpeg($dstImage, $destinationPath, $quality),
        };

        imagedestroy($srcImage);
        imagedestroy($dstImage);
    }

    private function compressJpeg(string $sourcePath, string $destinationPath, int $quality): void
    {
        $exif = @exif_read_data($sourcePath);
        $srcImage = @imagecreatefromjpeg($sourcePath);

        if (false === $srcImage) {
            copy($sourcePath, $destinationPath);

            return;
        }

        if (false !== $exif && isset($exif['Orientation'])) {
            $orientation = (int) $exif['Orientation'];
            $rotated = match ($orientation) {
                2 => imageflip($srcImage, IMG_FLIP_HORIZONTAL),
                3 => $srcImage = imagerotate($srcImage, 180, 0),
                4 => imageflip($srcImage, IMG_FLIP_VERTICAL),
                5 => $srcImage = imagerotate($srcImage, -90, 0),
                6 => $srcImage = imagerotate($srcImage, -90, 0),
                7 => $srcImage = imagerotate($srcImage, 90, 0),
                8 => $srcImage = imagerotate($srcImage, 90, 0),
                default => null,
            };
        }

        imagejpeg($srcImage, $destinationPath, $quality);
        imagedestroy($srcImage);
    }

    public function getImageSizes(string $path): array
    {
        $dir = dirname($path);
        $filename = basename($path);
        $info = pathinfo($filename);

        return [
            'original' => sprintf('/uploads/chat/%s/%s', date('Y/m'), $filename),
            'small' => sprintf('/uploads/chat/%s/small_%s', date('Y/m'), $filename),
            'large' => sprintf('/uploads/chat/%s/large_%s', date('Y/m'), $filename),
        ];
    }
}
