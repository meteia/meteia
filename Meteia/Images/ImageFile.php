<?php

declare(strict_types=1);

namespace Meteia\Images;

use Meteia\ValueObjects\Identity\FilesystemPath;

readonly class ImageFile implements Image
{
    public FilesystemPath $path;

    public function __construct(FilesystemPath $path)
    {
        $this->path = $path;
    }

    public function dimensions(): array
    {
        [$sourceWidth, $sourceHeight] = getimagesize((string) $this->path);

        return [$sourceWidth, $sourceHeight];
    }

    public function gdImage(): \GdImage
    {
        $imageFormat = ImageFormat::of($this->path);
        $src = match ($imageFormat) {
            ImageFormat::JPEG => imagecreatefromjpeg((string) $this->path),
            ImageFormat::PNG => imagecreatefrompng((string) $this->path),
            ImageFormat::GIF => imagecreatefromgif((string) $this->path),
            ImageFormat::WEBP => imagecreatefromwebp((string) $this->path),
            ImageFormat::AVIF => imagecreatefromavif((string) $this->path),
            default => throw new \Exception("Unexpected match value {$imageFormat->value}."),
        };
        if ($src === false) {
            throw new \Exception('Failed to read source.');
        }

        return $src;
    }
}
