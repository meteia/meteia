<?php

declare(strict_types=1);

namespace Meteia\Events;

use IteratorAggregate;
use Meteia\Domain\Contracts\DomainEvent;

/**
 * @extends IteratorAggregate<array-key, class-string<DomainEvent>>
 */
interface Events extends IteratorAggregate {}
