<?php

declare(strict_types=1);

namespace Meteia\ValueObjects;

use Meteia\ValueObjects\Identity\UniqueId;

abstract readonly class AggregateRootId extends UniqueId {}
