<?php

declare(strict_types=1);

namespace Meteia\Html\Elements;

use Meteia\Bluestone\PhpTemplate;

class Title
{
    use PhpTemplate;

    /**
     * @var string
     */
    protected $prefix;

    /**
     * @var string
     */
    protected $suffix;

    public function __construct(private string $title)
    {
    }

    public function set(string $title): void
    {
        $this->title = $title;
    }

    public function prefix(string $prefix): void
    {
        $this->prefix = $prefix;
    }

    public function suffix(string $suffix): void
    {
        $this->suffix = $suffix;
    }
}
