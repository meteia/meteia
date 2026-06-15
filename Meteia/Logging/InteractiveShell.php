<?php

declare(strict_types=1);

namespace Meteia\Logging;

final readonly class InteractiveShell
{
    public function isInteractive(): bool
    {
        if (!\defined('STDERR')) {
            return false;
        }

        return $this->isTty(\STDERR);
    }

    private function isTty(mixed $fileDescriptor): bool
    {
        $previous = set_error_handler(static fn() => true);
        try {
            if (\function_exists('stream_isatty') && \is_resource($fileDescriptor)) {
                return stream_isatty($fileDescriptor);
            }

            if (\function_exists('posix_isatty') && \is_int($fileDescriptor)) {
                return posix_isatty($fileDescriptor);
            }

            return false;
        } finally {
            if ($previous === null) {
                restore_error_handler();
            }
            if ($previous !== null) {
                set_error_handler($previous);
            }
        }
    }
}