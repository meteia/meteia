<?php

declare(strict_types=1);

namespace Meteia\Commands\CommandLine;

use Bunny\Channel;
use Bunny\Client;
use InvalidArgumentException;
use Meteia\Bootstrap\ApplicationNamespace;
use Meteia\CommandLine\Command;
use Meteia\CommandLine\PayloadParser;
use Meteia\CommandLine\PendingReply;
use Meteia\Commands\Command as DomainCommand;
use Meteia\Commands\CommandOutbox;
use Meteia\DependencyInjection\Container;
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
        return 'Send a domain command (Meteia\Commands\Command) to the outbox. Target: dotted class name. Payload via --dotted=val or @json.file. Use --wait-for-reply to auto-generate a private queue and wait for a response (replyTo carried inside the command). Supports --username/--password or RABBITMQ_* env vars for auth.';
    }

    #[Override]
    public static function inputDefinition(): InputDefinition
    {
        return new InputDefinition([
            new InputArgument('target', InputArgument::REQUIRED, 'Dotted class name, e.g. Commands.Debug.Ping'),
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
            new InputOption(
                'wait-for-reply',
                null,
                InputOption::VALUE_NONE,
                'Auto-generate exclusive reply queue, embed its name as replyTo in the command, and wait for a response message on it',
            ),
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

        // Apply auth overrides from CLI args or fall back to existing env (RABBITMQ_*)
        // before any Rabbit services are resolved from the container.
        $this->applyRabbitAuthOverrides();

        $tokens = $this->payloadTokens();
        $parsed = $parser->parseTokens($tokens);
        $data = $parser->load($parsed['jsonFile'], $parsed['overrides']);

        $waitForReply = (bool) $this->input->getOption('wait-for-reply');
        $replyQueue = null;

        try {
            $serializer = $this->container->get(SerializerInterface::class);
            $outbox = $this->container->get(CommandOutbox::class);

            $channelForReply = null;
            $clientForReply = null;

            if ($waitForReply) {
                /** @var Channel $channelForReply */
                $channelForReply = $this->container->get(Channel::class);
                /** @var Client $clientForReply */
                $clientForReply = $this->container->get(Client::class);

                /** @var \Bunny\Protocol\MethodQueueDeclareOkFrame $result */
                $result = $channelForReply->queueDeclare(queue: '', exclusive: true, autoDelete: true);
                $replyQueue = $result->queue;

                $data['replyTo'] = $replyQueue;

                $this->output->writeln('<info>Generated private reply queue: ' . $replyQueue . '</info>');
            }

            $command = $serializer->denormalize($data, $fqcn);
            $outbox->publish($command);
            $this->output->writeln('<info>Sent ' . $fqcn . '</info>');

            if ($waitForReply && $replyQueue !== null && $channelForReply !== null && $clientForReply !== null) {
                $this->waitForReply($replyQueue, $channelForReply, $clientForReply, $serializer, $fqcn);
            }
        } catch (Throwable $throwable) {
            $this->output->writeln('<error>Send failed: ' . $throwable->getMessage() . '</error>');
            throw $throwable;
        }
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

    private function waitForReply(
        string $replyQueue,
        Channel $channel,
        Client $client,
        SerializerInterface $serializer,
        string $target,
    ): void {
        $pendingReply = new PendingReply($replyQueue, $channel, $client, $serializer, $this->output);

        $reply = $pendingReply->await();

        $replyType = is_object($reply) ? $reply::class : gettype($reply);
        $this->output->writeln('<info>Reply received for ' . $target . ': ' . $replyType . '</info>');
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
