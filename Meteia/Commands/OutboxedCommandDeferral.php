<?php

declare(strict_types=1);

namespace Meteia\Commands;

use Meteia\ValueObjects\Identity\MessageScopeSource;
use Override;

final readonly class OutboxedCommandDeferral implements CommandDeferral
{
    public function __construct(
        private CommandDeliveries $deliveries,
        private MessageScopeSource $scopeSource,
    ) {}

    /**
     * @param Command<mixed> $command
     */
    #[Override]
    public function defer(Command $command): DeferredCommand
    {
        $commandId = CommandId::random();
        $this->deliveries->publishDelivery(new CommandDelivery(
            $commandId,
            $command,
            $this->scopeSource->current(),
        ));

        return new DeferredCommand($commandId);
    }
}
