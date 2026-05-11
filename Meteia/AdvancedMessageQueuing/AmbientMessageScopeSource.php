<?php

declare(strict_types=1);

namespace Meteia\AdvancedMessageQueuing;

use Meteia\ValueObjects\Identity\MessageScope;
use Meteia\ValueObjects\Identity\MessageScopeSource;
use Override;

final class AmbientMessageScopeSource implements MessageScopeSource
{
    private ?MessageScope $current = null;

    public function __construct(
        private readonly MessageScope $default,
    ) {}

    #[Override]
    public function current(): MessageScope
    {
        return $this->current ?? $this->default;
    }

    /**
     * Run $body with $scope as the ambient scope. The previous scope (if any) is restored on
     * return or throw, supporting nested invocations safely.
     *
     * @template T
     * @param callable(): T $body
     * @return T
     */
    public function using(MessageScope $scope, callable $body): mixed
    {
        $previous = $this->current;
        $this->current = $scope;

        try {
            return $body();
        } finally {
            $this->current = $previous;
        }
    }
}
