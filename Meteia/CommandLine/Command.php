<?php

declare(strict_types=1);

namespace Meteia\CommandLine;

use Symfony\Component\Console\Input\InputDefinition;

interface Command
{
    public function execute(): void;

    public static function description(): string;

    public static function inputDefinition(): InputDefinition;
}
