<?php

declare(strict_types=1);

namespace Meteia\Commands;

use InvalidArgumentException;

final readonly class ReplyDestination
{
    public function __construct(
        private string $destination,
    ) {
        if (trim($destination) === '') {
            throw new InvalidArgumentException('Reply destination cannot be empty.');
        }
    }

    public function toNative(): string
    {
        return $this->destination;
    }

    public function queueName(): string
    {
        foreach (['/reply-queue/', '/amq/queue/', '/queue/'] as $prefix) {
            if (str_starts_with($this->destination, $prefix)) {
                return substr($this->destination, \strlen($prefix));
            }
        }

        return $this->destination;
    }
}
