<?php

declare(strict_types=1);

namespace Meteia\Commands;

use IteratorAggregate;

/**
 * @extends IteratorAggregate<array-key, class-string<Command>>
 */
interface Commands extends IteratorAggregate {}
