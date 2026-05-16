<?php

declare(strict_types=1);

namespace Meteia\Events\CommandLine;

use DateTimeImmutable;
use InvalidArgumentException;
use Meteia\Bootstrap\ApplicationNamespace;
use Meteia\CommandLine\Command;
use Meteia\CommandLine\PayloadParser;
use Meteia\DependencyInjection\Container;
use Meteia\Domain\Contracts\DomainEvent;
use Meteia\EventSourcing\StreamId;
use Meteia\EventSourcing\StreamVersion;
use Meteia\Events\PublishedEvent;
use Meteia\Events\PublishedEvents;
use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;
use Override;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
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
        return 'Send a recorded domain event to the published-events channel.';
    }

    #[Override]
    public static function inputDefinition(): InputDefinition
    {
        return new InputDefinition([
            new InputArgument('target', InputArgument::REQUIRED, 'Dotted class name, e.g. Debug.Events.Pong'),
            new InputOption('stream-id', null, InputOption::VALUE_REQUIRED, 'Recorded event stream id.'),
            new InputOption('stream-version', null, InputOption::VALUE_REQUIRED, 'Recorded event stream version.'),
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
        $fqcn = $parser->resolve($target, $this->namespace, DomainEvent::class);
        if ($fqcn === null) {
            throw new InvalidArgumentException(sprintf(
                'Target "%s" must resolve to a class implementing %s',
                $target,
                DomainEvent::class,
            ));
        }

        // Apply auth overrides from CLI args (or fall back to RABBITMQ_* env) before Rabbit services are resolved.
        $this->applyRabbitAuthOverrides();
        $streamId = $this->requiredStringOption('stream-id');
        $streamVersion = $this->requiredNumericOption('stream-version');

        $tokens = $this->payloadTokens();
        $parsed = $parser->parseTokens($tokens);
        $data = $parser->load($parsed['jsonFile'], $parsed['overrides']);

        try {
            $serializer = $this->container->get(SerializerInterface::class);
            \assert($serializer instanceof SerializerInterface, 'serializer must be available for events:send');
            \assert($serializer instanceof DenormalizerInterface, 'serializer must denormalize events for events:send');
            $publishedEvents = $this->container->get(PublishedEvents::class);
            \assert($publishedEvents instanceof PublishedEvents, 'published events channel must be available for events:send');
            $event = $serializer->denormalize($data, $fqcn);
            if (!$event instanceof DomainEvent) {
                throw new InvalidArgumentException(sprintf(
                    'Target "%s" must denormalize to a class implementing %s',
                    $target,
                    DomainEvent::class,
                ));
            }
            $publishedEvents->publish(PublishedEvent::fromMessage(
                StreamId::fromToken($streamId),
                new StreamVersion((int) $streamVersion),
                $event,
                CausationId::random(),
                CorrelationId::random(),
                new DateTimeImmutable(),
            ));
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
        $username = $this->optionalStringOption('username');
        if ($username !== null && $username !== '') {
            $_ENV['RABBITMQ_USERNAME'] = $username;
        }

        $password = $this->optionalStringOption('password');
        if ($password !== null && $password !== '') {
            $_ENV['RABBITMQ_PASSWORD'] = $password;
        }
    }

    private function requiredStringOption(string $name): string
    {
        $value = $this->optionalStringOption($name);
        if ($value === null || $value === '') {
            throw new InvalidArgumentException(sprintf('events:send requires --%s.', $name));
        }

        return $value;
    }

    private function requiredNumericOption(string $name): string
    {
        $value = $this->requiredStringOption($name);
        if (!is_numeric($value)) {
            throw new InvalidArgumentException(sprintf('events:send requires numeric --%s.', $name));
        }

        return $value;
    }

    private function optionalStringOption(string $name): ?string
    {
        $value = $this->input->getOption($name);
        if ($value === null || is_string($value)) {
            return $value;
        }

        throw new InvalidArgumentException(sprintf('events:send option --%s must be a string.', $name));
    }
}
