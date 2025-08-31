<?php

declare(strict_types=1);

namespace Meteia\Logging;

use Psr\Log\AbstractLogger;

class StandardError extends AbstractLogger
{
    private $stderr;

    public function __construct()
    {
        $this->stderr = fopen('php://stderr', 'w');
    }

    #[\Override]
    public function log($level, $message, array $context = []): void
    {
        fwrite($this->stderr, $message . PHP_EOL);
    }
}
