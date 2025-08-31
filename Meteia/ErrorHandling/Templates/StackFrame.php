<?php

declare(strict_types=1);

namespace Meteia\ErrorHandling\Templates;

use Meteia\Bluestone\PhpTemplate;

class StackFrame
{
    use PhpTemplate;

    public function __construct(
        private readonly string $path,
        public readonly int $line,
        public readonly string $file,
    ) {}

    public function fileFragment(): FileFragment
    {
        return new FileFragment($this->path, $this->line);
    }

    public function href(): string
    {
        return 'idea://open?' . http_build_query(['file' => $this->path, 'line' => $this->line]);
    }
}
