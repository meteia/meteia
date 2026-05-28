<?php

declare(strict_types=1);

namespace Meteia\Realtime\Commands;

use Meteia\Commands\Command;
use SensitiveParameter;

/**
 * @implements Command<void>
 */
final readonly class OpenLiveViewSession implements Command
{
    public function __construct(
        #[SensitiveParameter]
        public string $token,
    ) {}
}
