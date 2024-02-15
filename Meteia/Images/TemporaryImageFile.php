<?php

declare(strict_types=1);

namespace Meteia\Images;

use Meteia\ValueObjects\Identity\FilesystemPath;

readonly class TemporaryImageFile extends ImageFile
{
    public function __construct(string $imageData)
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'img');
        file_put_contents($tempPath, $imageData);
        parent::__construct(new FilesystemPath($tempPath));
    }

    public function __destruct()
    {
        $this->path->delete();
    }
}
