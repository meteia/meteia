<?php

declare(strict_types=1);

use Meteia\Configuration\Configuration;
use Meteia\Domain\Configuration\DomainCommandsExchangeName;
use Meteia\Domain\Contracts\UnitOfWork;
use Meteia\Domain\ImmediateUnitOfWork;

return [
    UnitOfWork::class => ImmediateUnitOfWork::class,
    DomainCommandsExchangeName::class => fn (Configuration $configuration): DomainCommandsExchangeName => new DomainCommandsExchangeName($configuration->string('METEIA_DOMAIN_COMMANDS_EXCHANGE_NAME', 'commands')),
];
