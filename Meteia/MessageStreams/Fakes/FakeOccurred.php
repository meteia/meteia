<?php

declare(strict_types=1);

namespace Meteia\MessageStreams\Fakes;

use Meteia\Domain\Contracts\DomainEvent;

class FakeOccurred implements DomainEvent
{
    public function __construct(
        public string $publicData,
        protected string $protectedData,
        private string $privateData,
    ) {
    }
}
