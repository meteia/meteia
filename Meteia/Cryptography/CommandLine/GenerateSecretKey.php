<?php

declare(strict_types=1);

namespace Meteia\Cryptography\CommandLine;

use Meteia\CommandLine\Command;
use Meteia\Cryptography\SecretKey;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateSecretKey implements Command
{
    public function execute(OutputInterface $output): void
    {
        $sk = new SecretKey(random_bytes(24));

        $output->writeln($sk);
    }
}
