<?php

declare(strict_types=1);

namespace Meteia\Logging;

class LogfmtBench
{
    private Logfmt $logfmt;

    public function __construct()
    {
        $this->logfmt = new Logfmt();
    }

    public function benchMessage(): void
    {
        $this->logfmt->format('debug', 'a simple message string');
    }

    public function benchMessageWithContext(): void
    {
        $this->logfmt->format('debug', 'a simple message string', [
            'someString' => '9EE28D4F-EF87-480D-961E-364E08A152B4',
            'float' => 3.14159,
            'bool' => true,
            'int' => 1,
        ]);
    }
}
