<?php

declare(strict_types=1);

namespace Meteia\Commands\Fixtures;

use Meteia\Commands\Command;
use Meteia\Commands\CommandHandler;
use Override;

/**
 * @implements CommandHandler<ResultCommand, string>
 */
final readonly class ReturningResultCommandHandler implements CommandHandler
{
    #[Override]
    public function handle(Command $command): string
    {
        return 'handled';
    }
}
