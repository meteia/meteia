<?php

declare(strict_types=1);

namespace Meteia\Commands\CommandLine;

use InvalidArgumentException;
use Meteia\Bootstrap\ApplicationNamespace;
use Meteia\CommandLine\Command;
use Meteia\CommandLine\PayloadParser;
use Meteia\Commands\Command as DomainCommand;
use Meteia\Commands\CommandOutbox;
use Meteia\DependencyInjection\Container;
use Override;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Throwable;

final readonly class Send implements Command
{
    public function __construct(
        private InputInterface $input,
        private OutputInterface $output,
        private Container $container,
        private ApplicationNamespace $namespace,
    ) {}

    #[Override]
    public static function description(): string
    {
        return 'Send a domain command (Meteia\Commands\Command) to the outbox. Target: dotted class name. Payload via --dotted=val or @json.file';
    }

    #[Override]
    public static function inputDefinition(): InputDefinition
    {
        return new InputDefinition([
            new InputArgument('target', InputArgument::REQUIRED, 'Dotted class name, e.g. App.Commands.CreateUser'),
        ]);
    }

    #[Override]
    public function execute(): void
    {
        $target = (string) $this->input->getArgument('target');
        $parser = new PayloadParser();
        $fqcn = $parser->resolve($target, $this->namespace, DomainCommand::class);
        if ($fqcn === null) {
            throw new InvalidArgumentException(sprintf(
                'Target "%s" must resolve to a class implementing %s',
                $target,
                DomainCommand::class,
            ));
        }

        $tokens = $this->payloadTokens();
        $parsed = $parser->parseTokens($tokens);
        $data = $parser->load($parsed['jsonFile'], $parsed['overrides']);

        try {
            $serializer = $this->container->get(SerializerInterface::class);
            $outbox = $this->container->get(CommandOutbox::class);
            $command = $serializer->denormalize($data, $fqcn);
            $outbox->publish($command);
            $this->output->writeln('<info>Sent ' . $fqcn . '</info>');
        } catch (Throwable $throwable) {
            $this->output->writeln('<error>Send failed: ' . $throwable->getMessage() . '</error>');
            throw $throwable;
        }
    }

    /**
     * @return list<string>
     */
    private function payloadTokens(): array
    {
        $argv = $_SERVER['argv'] ?? [];
        $idx = null;
        foreach (['commands:send', 'Commands:Send'] as $name) {
            $i = array_search($name, $argv, true);
            if ($i === false) {
                continue;
            }
            $idx = $i;
            break;
        }
        if ($idx === null) {
            $idx = 1;
        }

        return array_slice($argv, $idx + 2);
    }
}
