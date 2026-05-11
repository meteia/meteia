<?php

declare(strict_types=1);

namespace Meteia\Logging;

use Override;
use Psr\Log\AbstractLogger;
use RuntimeException;
use Stringable;

class StandardError extends AbstractLogger
{
    /**
     * @var resource
     */
    private $stderr;

    public function __construct()
    {
        $stderr = fopen('php://stderr', 'w');
        if ($stderr === false) {
            throw new RuntimeException('Failed to open php://stderr');
        }
        $this->stderr = $stderr;
    }

    #[Override]
    public function log($level, string|Stringable $message, array $context = []): void
    {
        fwrite($this->stderr, $message . PHP_EOL);
    }
}
