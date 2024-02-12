<?php

declare(strict_types=1);

namespace Meteia\Http\Responses;

class ResizedImageResponse extends BinaryResponse
{
    public function __construct(
        string $sourcePath,
        string $sourceImageFormat,
        string $outputFileFormat,
        int $width,
        int $height,
        int $pixelDensity,
    ) {
        if ($sourceImageFormat === 'jpg') {
            $sourceImageFormat = 'jpeg';
        }
        if ($outputFileFormat === 'jpg') {
            $outputFileFormat = 'jpeg';
        }
        if (!\in_array($outputFileFormat, ['jpeg', 'png', 'gif', 'webp', 'avif'], true)) {
            throw new \Exception('Unsupported image type');
        }

        $src = match ($sourceImageFormat) {
            'jpeg' => imagecreatefromjpeg($sourcePath),
            'png' => imagecreatefrompng($sourcePath),
            'gif' => imagecreatefromgif($sourcePath),
            'webp' => imagecreatefromwebp($sourcePath),
            'avif' => imagecreatefromavif($sourcePath),
            default => throw new \Exception("Unsupported source image type {$sourceImageFormat}."),
        };
        if ($src === false) {
            throw new \Exception('Failed to read source.');
        }

        $canvasWidth = $width * $pixelDensity;
        $canvasHeight = $height * $pixelDensity;
        [$sourceWidth, $sourceHeight] = getimagesize($sourcePath);
        $dst = imagecreatetruecolor($canvasWidth, $canvasHeight);
        imagefill($dst, 0, 0, imagecolorallocatealpha($dst, 0, 0, 0, 127));
        imagealphablending($dst, true);
        imagesavealpha($dst, true);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $canvasWidth, $canvasHeight, $sourceWidth, $sourceHeight);

        $quality = match ($pixelDensity) {
            1 => 65,
            2 => 55,
            3 => 45,
            default => throw new \Exception('Unsupported pixel density.'),
        };

        $resizedPath = tempnam(sys_get_temp_dir(), 'img');
        match ($outputFileFormat) {
            'jpeg' => imagejpeg($dst, $resizedPath, $quality),
            'png' => imagepng($dst, $resizedPath, (int) (($quality / 100) * 9)),
            'gif' => imagegif($dst, $resizedPath),
            'webp' => imagewebp($dst, $resizedPath, $quality),
            'avif' => imageavif($dst, $resizedPath, $quality - 20),
            default => throw new \Exception("Unsupported output image type {$outputFileFormat}."),
        };
        $newImage = file_get_contents($resizedPath);
        unlink($resizedPath);
        parent::__construct($newImage, 200, [
            'Content-Type' => 'image/' . $outputFileFormat,
            'Cache-Control' => 'public, no-transform, max-age=31536000, stale-while-revalidate=86400, immutable',
            'Content-Length' => (string) \strlen($newImage),
        ]);
    }
}
