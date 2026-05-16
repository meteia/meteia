<?php

declare(strict_types=1);

namespace Meteia\Events\CommandLine;

use InvalidArgumentException;
use Meteia\Bootstrap\ApplicationNamespace;
use Meteia\CommandLine\Command;
use Meteia\CommandLine\PayloadParser;
use Meteia\DependencyInjection\Container;
use Meteia\Events\Event;
use Meteia\Events\EventOutbox;
use Override;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
        return 'Send a domain event to the outbox.';
    }

    #[Override]
    public static function inputDefinition(): InputDefinition
    {
        return new InputDefinition([
            new InputArgument('target', InputArgument::REQUIRED, 'Dotted class name, e.g. Debug.Events.Pong'),
            new InputOption(
                'username',
                null,
                InputOption::VALUE_REQUIRED,
                'RabbitMQ username (falls back to RABBITMQ_USERNAME env)',
            ),
            new InputOption(
                'password',
                null,
                InputOption::VALUE_REQUIRED,
                'RabbitMQ password (falls back to RABBITMQ_PASSWORD env)',
            ),
        ]);
    }

    #[Override]
    public function execute(): void
    {
        $target = (string) $this->input->getArgument('target');
        $parser = new PayloadParser();
        $fqcn = $parser->resolve($target, $this->namespace, Event::class);
        if ($fqcn === null) {
            throw new InvalidArgumentException(sprintf(
                'Target "%s" must resolve to a class implementing %s',
                $target,
                Event::class,
            ));
        }

        // Apply auth overrides from CLI args (or fall back to RABBITMQ_* env) before Rabbit services are resolved.
        $this->applyRabbitAuthOverrides();

        $tokens = $this->payloadTokens();
        $parsed = $parser->parseTokens($tokens);
        $data = $parser->load($parsed['jsonFile'], $parsed['overrides']);

        try {
            $serializer = $this->container->get(SerializerInterface::class);
            $outbox = $this->container->get(EventOutbox::class);
            $event = $serializer->denormalize($data, $fqcn);
            $outbox->publish($event);
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
        foreach (['events:send', 'Events:Send'] as $name) {
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

    private function applyRabbitAuthOverrides(): void
    {
        $username = $this->input->getOption('username');
        if (is_string($username) && $username !== '') {
            $_ENV['RABBITMQ_USERNAME'] = $username;
        }

        $password = $this->input->getOption('password');
        if (is_string($password) && $password !== '') {
            $_ENV['RABBITMQ_PASSWORD'] = $password;
        }
    }
}
