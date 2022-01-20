<?php

declare(strict_types=1);

namespace Meteia\Yeso\ValueObjects\Identity;

use Meteia\Yeso\ValueObjects\Primitives\StringLiteral;
use Webmozart\PathUtil\Path;

class FilesystemPath extends StringLiteral
{
    public function __construct(...$paths)
    {
        $paths = array_map('strval', $paths);
        $value = Path::join(...$paths);
        parent::__construct($value);
    }

    public function join(...$paths)
    {
        return new static($this->value, ...$paths);
    }
}
