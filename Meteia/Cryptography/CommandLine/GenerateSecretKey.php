<?php

declare(strict_types=1);

namespace Meteia\Cryptography\CommandLine;

use Meteia\CommandLine\Command;
use Meteia\Cryptography\SecretKey;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateSecretKey implements Command
{
    public function __construct(private readonly OutputInterface $output)
    {
    }

    public static function description(): string
    {
        return 'Generate a secret key';
    }

    public function execute(): void
    {
        $sk = new SecretKey(random_bytes(32));
        $this->output->writeln((string) $sk);
    }

    public static function inputDefinition(): InputDefinition
    {
        return new InputDefinition();
    }
}
