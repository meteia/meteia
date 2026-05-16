<?php

declare(strict_types=1);

namespace Meteia\Events;

use IteratorAggregate;

/**
 * @extends IteratorAggregate<class-string<\Meteia\Domain\Contracts\DomainEvent>, array<array-key, class-string<EventSink>>>
 */
interface EventSinks extends IteratorAggregate {}
