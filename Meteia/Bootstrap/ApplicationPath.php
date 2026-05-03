<?php

declare(strict_types=1);

namespace Meteia\Bootstrap;

use Meteia\ValueObjects\Identity\FilesystemPath;

class ApplicationPath extends FilesystemPath
{
    public function __construct(...$paths)
    {
        parent::__construct(...$paths);
        $value = realpath($this->value);
        if (!$value) {
            throw new InvalidApplicationPath($this->value);
        }
        $this->value = $value;
    }
}
