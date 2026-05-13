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

    public function __construct(
        private readonly Logfmt $logfmt = new Logfmt(),
    ) {
        $stderr = fopen('php://stderr', 'w');
        if ($stderr === false) {
            throw new RuntimeException('Failed to open php://stderr');
        }
        $this->stderr = $stderr;
    }

    #[Override]
    public function log($level, string|Stringable $message, array $context = []): void
    {
        $line = $this->logfmt->format((string) $level, (string) $message, $context);
        fwrite($this->stderr, $line . PHP_EOL);
    }
}
