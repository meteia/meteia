<?php

declare(strict_types=1);

namespace Meteia\Dulce\Templates;

use Meteia\Bluestone\PhpTemplate;

class FileFragment
{
    use PhpTemplate;

    public function __construct(private readonly string $file, private readonly int $line)
    {
    }

    public function lines(): array
    {
        $lines = explode("\n", file_get_contents($this->file));

        $offset = (int) max(0, $this->line - ceil(11 / 2));

        $lines = \array_slice($lines, $offset, 11);

        $output = [];
        foreach ($lines as $line) {
            ++$offset;
            $line = rtrim($line);
            $line = htmlentities($line, ENT_QUOTES | ENT_HTML5);

            $output[] = (object) [
                'href' => 'idea://open?' . http_build_query(['file' => $this->file, 'line' => $offset]),
                'number' => $offset,
                'activeLine' => $offset === $this->line,
                'source' => $line,
            ];
        }

        return $output;
    }
}
