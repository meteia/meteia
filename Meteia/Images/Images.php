<?php

declare(strict_types=1);

namespace Meteia\Images;

use GuzzleHttp\Client;
use kornrunner\Blurhash\Blurhash;
use Meteia\Application\ApplicationPublicDir;
use Meteia\ValueObjects\Identity\FilesystemPath;

readonly class Images
{
    public function __construct(private ApplicationPublicDir $publicDir)
    {
    }

    public function blurhash(ImageFile $image, int $componentsX = 4, int $componentsY = 3): string
    {
        $img = $image->gdImage();
        $width = imagesx($img);
        $height = imagesy($img);

        $pixels = [];
        for ($y = 0; $y < $height; ++$y) {
            $row = [];
            for ($x = 0; $x < $width; ++$x) {
                $index = imagecolorat($img, $x, $y);
                $colors = imagecolorsforindex($img, $index);

                $row[] = [$colors['red'], $colors['green'], $colors['blue']];
            }
            $pixels[] = $row;
        }

        return Blurhash::encode($pixels, $componentsX, $componentsY);
    }

    public function resize(
        ImageFile $image,
        int $width,
        int $height,
        int $pixelDensity,
        ImageFormat $format,
        int $quality,
    ): ImageFile {
        $src = $image->gdImage();

        [$width, $height] = $this->constrainedDimensions(imagesx($src), imagesy($src), $width, $height);

        $canvasWidth = $width * $pixelDensity;
        $canvasHeight = $height * $pixelDensity;
        $sourceWidth = imagesx($src);
        $sourceHeight = imagesy($src);

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
        match ($format) {
            ImageFormat::JPEG => imagejpeg($dst, $resizedPath, $quality),
            ImageFormat::PNG => imagepng($dst, $resizedPath, (int) (($quality / 100) * 9)),
            ImageFormat::GIF => imagegif($dst, $resizedPath),
            ImageFormat::WEBP => imagewebp($dst, $resizedPath, $quality),
            ImageFormat::AVIF => imageavif($dst, $resizedPath, $quality),
            default => throw new \Exception("Unsupported output image type {$format->value}."),
        };

        return new ImageFile(new FilesystemPath($resizedPath));
    }

    public function fetchRemoteImage(string $url): ImageFile
    {
        $client = new Client();
        $tempPath = tempnam(sys_get_temp_dir(), 'img');
        $response = $client->get($url, [
            'sink' => $tempPath,
        ]);
        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException('Failed to download image.');
        }

        return new ImageFile(new FilesystemPath($tempPath));
    }

    private function constrainedDimensions(int $width, int $height, int $targetWidth, int $targetHeight): array
    {
        if ($targetWidth === -1 && $targetHeight === -1) {
            $targetWidth = min($width, 2048);
            $targetHeight = min($height, 2048);
        } elseif ($targetHeight === -1) {
            $targetHeight = ($targetWidth * $height) / $width;
        } elseif ($targetWidth === -1) {
            $targetWidth = ($targetHeight * $width) / $height;
        }

        $aspectRatio = $width / $height;
        if ($targetWidth / $targetHeight > $aspectRatio) {
            $newWidth = $targetHeight * $aspectRatio;
            $newHeight = $targetHeight;
        } else {
            $newHeight = $targetWidth / $aspectRatio;
            $newWidth = $targetWidth;
        }

        if ($newWidth > $width) {
            $newWidth = $width;
            $newHeight = $width / $aspectRatio;
        } elseif ($newHeight > $height) {
            $newHeight = $height;
            $newWidth = $height * $aspectRatio;
        }

        $newWidth = (int) round($newWidth);
        $newHeight = (int) round($newHeight);

        $newWidth = max(1, $newWidth);
        $newHeight = max(1, $newHeight);

        return [$newWidth, $newHeight];
    }
}
