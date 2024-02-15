<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Identity;

class TemporaryFilesystemPath extends FilesystemPath
{
    public function __destruct()
    {
        if (file_exists($this->value)) {
            unlink($this->value);
        }
    }

    public static function forData(string $data): self
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'meteia');
        file_put_contents($tempPath, $data);

        return new self($tempPath);
    }
}
