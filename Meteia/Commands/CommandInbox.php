<?php

declare(strict_types=1);

namespace Meteia\Commands;

interface CommandInbox
{
    public function subscribe(string $commandClassName, CommandSink $sink): void;

    public function run(): void;

    public function runOnce(): void;
}
