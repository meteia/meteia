<?php

declare(strict_types=1);

namespace Meteia\Commands;

use Meteia\Domain\Contracts\UnitOfWork;
use Meteia\ValueObjects\Identity\MessageScopeSource;
use Override;

final readonly class InProcessCommandExecution implements CommandExecution
{
    public function __construct(
        private CommandHandlers $handlers,
        private UnitOfWork $unitOfWork,
        private MessageScopeSource $scopeSource,
    ) {}

    /**
     * @template TResult
     * @param Command<TResult> $command
     * @return TResult
     */
    #[Override]
    public function execute(Command $command): mixed
    {
        $result = $this->handlers->handlerFor($command)->handle($command);
        $this->unitOfWork->complete($this->scopeSource->current());

        return $result;
    }
}
