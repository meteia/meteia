<?php

declare(strict_types=1);

namespace Meteia\Database\CommandLine;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;

class NewMigrationInputDefinition extends InputDefinition
{
    public const NAME = 'name';

    public const TYPE = 'type';

    public function __construct()
    {
        parent::__construct(
            [
                new InputArgument(self::TYPE, InputArgument::REQUIRED, 'either i (idempotent) or ni (non-idempotent); non-idempotent migration errors are ignored'),
                new InputArgument(self::NAME, InputArgument::REQUIRED, 'migration filename name'),
            ],
        );
    }
}
