<?php

declare(strict_types=1);

namespace Meteia\Commands\Fixtures;

use Exception;
use Meteia\Commands\Command;
use Meteia\Commands\CommandHandler;
use Override;

/**
 * @implements CommandHandler<ResultCommand, string>
 */
final readonly class FailingResultCommandHandler implements CommandHandler
{
    #[Override]
    public function handle(Command $command): string
    {
        throw new Exception('boom');
    }
}
