<?php

namespace App\Service;

use App\Enum\MediaType;
use Intervention\Image\ImageManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class MediaService
{
    private const int IMAGE_QUALITY = 80;
    private const int THUMB_SMALL = 150;
    private const int THUMB_MEDIUM = 400;
    private const int THUMB_LARGE = 1200;
    private const array ALLOWED_EXTENSIONS = [
        'jpg', 'jpeg', 'png', 'webp', 'svg',
        'mp4', 'webm', 'mov',
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'txt',
        'mp3', 'wav', 'ogg', 'aac',
    ];
    private const int MAX_IMAGE_SIZE = 10 * 1024 * 1024; // 10MB
    private const int MAX_VIDEO_SIZE = 200 * 1024 * 1024; // 200MB
    private const int MAX_DOCUMENT_SIZE = 50 * 1024 * 1024; // 50MB

    private readonly ?ImageManager $imageManager;

    public function __construct(
        private readonly string $uploadDir,
        private readonly string $publicDir,
    ) {
        try {
            $this->imageManager = new ImageManager('gd');
        } catch (\Throwable) {
            $this->imageManager = null;
        }
    }

    public function upload(UploadedFile $file, string $boutiqueId, string $context = 'general'): MediaResult
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $this->validate($file, $extension);

        $type = $this->resolveType($extension);
        $originalName = $file->getClientOriginalName();
        $fileName = sprintf('%s.%s', bin2hex(random_bytes(16)), $extension);
        $relativePath = sprintf('shops/%s/%s/%s', $boutiqueId, $context, date('Y/m'));
        $absolutePath = sprintf('%s/%s', $this->uploadDir, $relativePath);

        if (!is_dir($absolutePath)) {
            mkdir($absolutePath, 0755, true);
        }

        $destPath = sprintf('%s/%s', $absolutePath, $fileName);
        $file->move($absolutePath, $fileName);

        $result = new MediaResult(
            type: $type,
            originalName: $originalName,
            fileName: $fileName,
            extension: $extension,
            mimeType: $file->getClientMimeType(),
            size: filesize($destPath),
            path: sprintf('%s/%s', $relativePath, $fileName),
        );

        if (MediaType::Image === $type && 'svg' !== $extension) {
            $this->optimizeImage($destPath, $absolutePath, $fileName, $result);
        }

        if (MediaType::Video === $type) {
            $this->readVideoMetadata($destPath, $result);
        }

        if (MediaType::Image === $type && 'svg' !== $extension) {
            $this->generateThumbnails($destPath, $absolutePath, $fileName, $result);
        }

        return $result;
    }

    private function optimizeImage(string $filePath, string $dir, string $fileName, MediaResult $result): void
    {
        if (null === $this->imageManager) {
            return;
        }

        try {
            $image = $this->imageManager->read($filePath);
            $result->width = $image->width();
            $result->height = $image->height();

            if ('webp' !== $result->extension) {
                $webpName = pathinfo($fileName, PATHINFO_FILENAME).'.webp';
                $webpPath = sprintf('%s/%s', $dir, $webpName);
                $image->encode('webp', quality: self::IMAGE_QUALITY)->save($webpPath);

                $originalSize = filesize($filePath);
                $webpSize = filesize($webpPath);

                if ($webpSize < $originalSize) {
                    unlink($filePath);
                    $result->path = sprintf('%s/%s', dirname($result->path), $webpName);
                    $result->extension = 'webp';
                    $result->mimeType = 'image/webp';
                    $result->size = $webpSize;
                    $result->fileName = $webpName;
                } else {
                    unlink($webpPath);
                }
            }
        } catch (\Throwable) {
        }
    }

    private function generateThumbnails(string $filePath, string $dir, string $fileName, MediaResult $result): void
    {
        if (null === $this->imageManager) {
            return;
        }

        try {
            $image = $this->imageManager->read($filePath);
            $baseName = pathinfo($fileName, PATHINFO_FILENAME);

            $sizes = [
                'small' => self::THUMB_SMALL,
                'medium' => self::THUMB_MEDIUM,
                'large' => self::THUMB_LARGE,
            ];

            foreach ($sizes as $label => $maxSize) {
                if ($image->width() <= $maxSize && $image->height() <= $maxSize) {
                    continue;
                }

                $thumb = $image->scaleDown(width: $maxSize, height: $maxSize);
                $thumbName = sprintf('%s_%s.webp', $baseName, $label);
                $thumbPath = sprintf('%s/%s', $dir, $thumbName);
                $thumb->encode('webp', quality: 70)->save($thumbPath);

                $result->thumbnails[$label] = sprintf('%s/%s', dirname($result->path), $thumbName);
            }
        } catch (\Throwable) {
        }
    }

    private function readVideoMetadata(string $filePath, MediaResult $result): void
    {
        $result->size = filesize($filePath);

        if (extension_loaded('ffprobe') || !($ffprobe = trim(shell_exec('which ffprobe 2>/dev/null') ?: ''))) {
            return;
        }

        try {
            $json = shell_exec(sprintf(
                '%s -v quiet -print_format json -show_format -show_streams %s 2>/dev/null',
                escapeshellcmd($ffprobe),
                escapeshellarg($filePath),
            ));
            $data = $json ? json_decode($json, true) : null;

            if (isset($data['format']['duration'])) {
                $result->duration = (float) $data['format']['duration'];
            }

            foreach ($data['streams'] ?? [] as $stream) {
                if ('video' === ($stream['codec_type'] ?? '')) {
                    $result->width = (int) ($stream['width'] ?? 0);
                    $result->height = (int) ($stream['height'] ?? 0);
                    break;
                }
            }
        } catch (\Throwable) {
        }
    }

    public function deleteFile(string $relativePath): void
    {
        $absolute = sprintf('%s/%s', $this->uploadDir, $relativePath);
        if (file_exists($absolute)) {
            @unlink($absolute);
        }

        $dir = dirname($absolute);
        $base = pathinfo($absolute, PATHINFO_FILENAME);

        foreach (['small', 'medium', 'large'] as $label) {
            $thumb = sprintf('%s/%s_%s.webp', $dir, $base, $label);
            if (file_exists($thumb)) {
                @unlink($thumb);
            }
        }
    }

    private function validate(UploadedFile $file, string $extension): void
    {
        if (!in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            throw new \InvalidArgumentException(sprintf('Extension "%s" not allowed', $extension));
        }

        $type = $this->resolveType($extension);

        $maxSize = match ($type) {
            MediaType::Image => self::MAX_IMAGE_SIZE,
            MediaType::Video => self::MAX_VIDEO_SIZE,
            default => self::MAX_DOCUMENT_SIZE,
        };

        if ($file->getSize() > $maxSize) {
            throw new \InvalidArgumentException(sprintf('File too large. Max %dMB for %s files', $maxSize / 1024 / 1024, $type->value));
        }

        $this->validateExecutable($file);
    }

    private function validateExecutable(UploadedFile $file): void
    {
        $mime = $file->getClientMimeType();
        $executableMimes = [
            'application/x-php', 'application/x-httpd-php',
            'application/x-sh', 'application/x-shellscript',
            'text/x-php', 'text/x-python', 'text/x-perl',
            'application/java-archive', 'application/x-java-applet',
            'application/x-msdownload', 'application/x-msi',
        ];

        if (in_array($mime, $executableMimes, true)) {
            throw new \InvalidArgumentException('Executable files are not allowed');
        }

        $executableExts = ['php', 'phtml', 'php3', 'php4', 'php5', 'php7', 'pht', 'phtml', 'sh', 'bash', 'exe', 'msi', 'bat', 'cmd', 'pl', 'py', 'rb', 'jsp', 'asp', 'aspx'];
        $ext = strtolower($file->getClientOriginalExtension());

        if (in_array($ext, $executableExts, true)) {
            throw new \InvalidArgumentException('Executable files are not allowed');
        }
    }

    private function resolveType(string $extension): MediaType
    {
        return match ($extension) {
            'jpg', 'jpeg', 'png', 'webp', 'svg', 'gif', 'avif' => MediaType::Image,
            'mp4', 'webm', 'mov', 'avi', 'mkv' => MediaType::Video,
            'mp3', 'wav', 'ogg', 'aac', 'flac', 'wma' => MediaType::Audio,
            default => MediaType::Document,
        };
    }

    public function getUploadDir(): string
    {
        return $this->uploadDir;
    }
}

final class MediaResult
{
    public ?int $width = null;
    public ?int $height = null;
    public ?float $duration = null;
    /** @var array<string, string> */
    public array $thumbnails = [];

    public function __construct(
        public MediaType $type,
        public string $originalName,
        public string $fileName,
        public string $extension,
        public string $mimeType,
        public int $size,
        public string $path,
    ) {
    }
}
