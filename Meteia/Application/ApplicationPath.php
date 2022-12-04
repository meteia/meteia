<?php

declare(strict_types=1);

namespace Meteia\Application;

use Exception;
use Meteia\ValueObjects\Identity\FilesystemPath;

class ApplicationPath extends FilesystemPath
{
    public function __construct(...$paths)
    {
        parent::__construct(...$paths);
        $value = realpath($this->value);
        if ($value === false) {
            throw new Exception("Invalid ApplicationPath: $this->value");
        }
        $this->value = $value;
    }
}
