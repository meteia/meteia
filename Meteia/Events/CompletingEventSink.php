<?php

declare(strict_types=1);

namespace Meteia\Events;

use Meteia\Domain\Contracts\UnitOfWork;
use Meteia\ValueObjects\Identity\MessageScope;
use Override;

final readonly class CompletingEventSink implements EventSink
{
    public function __construct(
        private EventSink $inner,
        private UnitOfWork $unitOfWork,
    ) {}

    #[Override]
    public function drain(PublishedEvent $event, MessageScope $scope): void
    {
        $this->inner->drain($event, $scope);
        $this->unitOfWork->complete($scope);
    }
}
