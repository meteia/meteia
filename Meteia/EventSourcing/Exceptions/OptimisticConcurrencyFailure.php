<?php

declare(strict_types=1);

namespace Meteia\EventSourcing\Exceptions;

use Meteia\EventSourcing\StreamVersion;

final class OptimisticConcurrencyFailure extends \RuntimeException
{
    public function __construct(StreamVersion $expected, StreamVersion $observed)
    {
        parent::__construct(\sprintf('Expected stream version %s but observed %s.', $expected, $observed));
    }
}
