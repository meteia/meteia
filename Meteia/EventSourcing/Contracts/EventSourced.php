<?php

declare(strict_types=1);

namespace Meteia\EventSourcing\Contracts;

use Meteia\Domain\Contracts\AggregateRoot;
use Meteia\Domain\Contracts\CommitEventsInUnitOfWork;

interface EventSourced extends EventMessageTarget, AggregateRoot, CommitEventsInUnitOfWork
{
    // TODO: Was there a good reason for this? Hmm....
    // public function withEventVersion($eventVersion);
}
