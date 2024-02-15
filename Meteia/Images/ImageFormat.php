<?php

declare(strict_types=1);

namespace Meteia\Images;

use Meteia\ValueObjects\Identity\FilesystemPath;

enum ImageFormat: string
{
    case JPEG = 'jpeg';
    case PNG = 'png';
    case GIF = 'gif';
    case WEBP = 'webp';
    case AVIF = 'avif';

    public static function of(FilesystemPath $path): ImageFormat
    {
        $data = getimagesize((string) $path);
        $mimeType = $data['mime'];

        return match ($mimeType) {
            'image/jpeg' => self::JPEG,
            'image/png' => self::PNG,
            'image/gif' => self::GIF,
            'image/webp' => self::WEBP,
            'image/avif' => self::AVIF,
            default => throw new \Exception("Unsupported source image type {$mimeType}."),
        };
    }

    public static function fromFileExtenstion(string $fileExtension): ImageFormat
    {
        return match ($fileExtension) {
            'jpeg', 'jpg' => self::JPEG,
            'png' => self::PNG,
            'gif' => self::GIF,
            'webp' => self::WEBP,
            'avif' => self::AVIF,
            default => throw new \Exception("Unsupported file extension {$fileExtension}."),
        };
    }

    public function mimeType(): string
    {
        return match ($this) {
            self::JPEG => 'image/jpeg',
            self::PNG => 'image/png',
            self::GIF => 'image/gif',
            self::WEBP => 'image/webp',
            self::AVIF => 'image/avif',
        };
    }
}
