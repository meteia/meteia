<?php

declare(strict_types=1);

namespace Meteia\Dulce\Templates;

use Meteia\Bluestone\Contracts\Renderable;
use Meteia\Bluestone\PhpTemplate;

class StackFrame implements Renderable
{
    use PhpTemplate;

    public function __construct(
        private string $path,
        public int $line,
        public string $file,
    ) {
    }

    public function fileFragment(): Renderable
    {
        return new FileFragment($this->path, $this->line);
    }

    public function href(): string
    {
        return 'idea://open?' . http_build_query(['file' => $this->path, 'line' => $this->line]);
    }
}
