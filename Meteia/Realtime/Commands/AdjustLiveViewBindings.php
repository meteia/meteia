<?php

declare(strict_types=1);

namespace Meteia\Realtime\Commands;

use Meteia\Commands\Command;
use SensitiveParameter;

/**
 * @implements Command<void>
 */
final readonly class AdjustLiveViewBindings implements Command
{
    /**
     * @param list<string> $add
     * @param list<string> $remove
     */
    public function __construct(
        #[SensitiveParameter]
        public string $token,
        public array $add,
        public array $remove,
    ) {}
}
