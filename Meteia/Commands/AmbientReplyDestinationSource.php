<?php

declare(strict_types=1);

namespace Meteia\Commands;

use Meteia\Commands\Exceptions\MissingReplyDestination;
use Override;

final class AmbientReplyDestinationSource implements ReplyDestinationSource
{
    private ?ReplyDestination $current = null;

    #[Override]
    public function current(): ReplyDestination
    {
        return $this->current ?? throw new MissingReplyDestination();
    }

    /**
     * @template T
     * @param callable(): T $body
     * @return T
     */
    public function using(ReplyDestination $destination, callable $body): mixed
    {
        $previous = $this->current;
        $this->current = $destination;

        try {
            return $body();
        } finally {
            $this->current = $previous;
        }
    }
}
