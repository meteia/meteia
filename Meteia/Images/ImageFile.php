<?php

declare(strict_types=1);

namespace Meteia\Images;

use Exception;
use GdImage;
use Meteia\ValueObjects\Identity\FilesystemPath;
use Override;

readonly class ImageFile implements Image
{
    public FilesystemPath $path;

    public function __construct(FilesystemPath $path)
    {
        $this->path = $path;
    }

    #[Override]
    public function dimensions(): array
    {
        $size = getimagesize((string) $this->path);
        if ($size === false) {
            throw new Exception('Failed to read image dimensions.');
        }

        return [$size[0], $size[1]];
    }

    #[Override]
    public function gdImage(): GdImage
    {
        $imageFormat = ImageFormat::of($this->path);
        $src = match ($imageFormat) {
            ImageFormat::JPEG => imagecreatefromjpeg((string) $this->path),
            ImageFormat::PNG => imagecreatefrompng((string) $this->path),
            ImageFormat::GIF => imagecreatefromgif((string) $this->path),
            ImageFormat::WEBP => imagecreatefromwebp((string) $this->path),
            ImageFormat::AVIF => imagecreatefromavif((string) $this->path),
        };
        if (!$src) {
            throw new Exception('Failed to read source.');
        }

        return $src;
    }
}
