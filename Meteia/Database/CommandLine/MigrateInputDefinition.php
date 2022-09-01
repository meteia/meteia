<?php

declare(strict_types=1);

namespace Meteia\Database\CommandLine;

use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

class MigrateInputDefinition extends InputDefinition
{
    public const DEV = 'dev';
    public const RESET = 'reset';

    public function __construct()
    {
        parent::__construct(
            [
                new InputOption(self::DEV, '', InputOption::VALUE_NONE, 'apply without marking as ran'),
                new InputOption(self::RESET, '', InputOption::VALUE_NONE, 'reset migration table (dangerous option if all migrations are not idempotent)'),
            ],
        );
    }
}
