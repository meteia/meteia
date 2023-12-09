<?php

declare(strict_types=1);

namespace Meteia\Domain;

interface DomainEventHandler
{
    public function handle(DomainEvent $event): void;
}
