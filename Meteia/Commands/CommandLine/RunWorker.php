<?php

declare(strict_types=1);

namespace Meteia\Commands\CommandLine;

use Bunny\Channel;
use Bunny\ChannelInterface;
use Bunny\Client;
use InvalidArgumentException;
use Meteia\AdvancedMessageQueuing\AmbientMessageScopeSource;
use Meteia\Bootstrap\ApplicationNamespace;
use Meteia\Bootstrap\ApplicationPath;
use Meteia\Bootstrap\ApplicationPublicDir;
use Meteia\CommandLine\Command as CLICommand;
use Meteia\CommandLine\PayloadParser;
use Meteia\Commands\Command;
use Meteia\Commands\CommandBus;
use Meteia\Commands\CommandInbox;
use Meteia\Commands\Commands;
use Meteia\Commands\CommandSink;
use Meteia\DependencyInjection\Container;
use Meteia\DependencyInjection\ContainerBuilder;
use Meteia\Domain\Contracts\UnitOfWork;
use Meteia\ValueObjects\Identity\MessageScope;
use Override;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

final class RunWorker implements CLICommand, CommandSink
{
    private ?Container $appContainer;

    public function __construct(
        private Commands $commands,
        private LoggerInterface $log,
        private CommandInbox $commandInbox,
        private Container $container,
    ) {
        $this->appContainer = null;
    }

    #[Override]
    public static function description(): string
    {
        return 'Run the command worker queue.';
    }

    #[Override]
    public static function inputDefinition(): InputDefinition
    {
        return new InputDefinition([
            new InputOption(
                'only',
                '',
                InputOption::VALUE_REQUIRED,
                'Only subscribe to the specified dotted command class, e.g. App.Users.Commands.CreateUser',
            ),
            new InputOption(
                'once',
                '',
                InputOption::VALUE_NONE,
                'Exit after handling one command (typically used with --only)',
            ),
        ]);
    }

    #[Override]
    public function execute(): void
    {
        /** @var InputInterface $input */
        $input = $this->container->get(InputInterface::class);
        \assert($input instanceof InputInterface, 'console input must be available in the command worker container');
        /** @var ApplicationNamespace $namespace */
        $namespace = $this->container->get(ApplicationNamespace::class);
        \assert($namespace instanceof ApplicationNamespace, 'application namespace must be available in the command worker container');

        /** @var string|null $only */
        $only = $input->getOption('only');
        /** @var bool $once */
        $once = $input->getOption('once');

        $targetCommand = null;
        if ($only !== null) {
            $target = $only;
            $parser = new PayloadParser();
            $targetCommand = $parser->resolve($target, $namespace, Command::class);
            if ($targetCommand === null) {
                throw new InvalidArgumentException(sprintf(
                    'Target "%s" must resolve to a class implementing %s',
                    $target,
                    Command::class,
                ));
            }
        }

        foreach ($this->commands as $command) {
            if ($targetCommand !== null && $command !== $targetCommand) {
                continue;
            }
            $suffix = $targetCommand !== null ? ' (only)' : '';
            $this->log->info('Registering command sink' . $suffix, ['command' => $command]);
            $this->commandInbox->subscribe($command, $this);
        }

        $this->log->info('Running command worker' . ($once ? ' (once)' : ''));
        if ($once) {
            $this->commandInbox->runOnce();

            return;
        }
        $this->commandInbox->run();
    }

    private function appContainer(): Container
    {
        if ($this->appContainer === null) {
            /** @var ApplicationPath $path */
            $path = $this->container->get(ApplicationPath::class);
            \assert($path instanceof ApplicationPath, 'application path must be available in the command worker container');
            /** @var ApplicationNamespace $namespace */
            $namespace = $this->container->get(ApplicationNamespace::class);
            \assert($namespace instanceof ApplicationNamespace, 'application namespace must be available in the command worker container');
            /** @var ApplicationPublicDir $publicDir */
            $publicDir = $this->container->get(ApplicationPublicDir::class);
            \assert($publicDir instanceof ApplicationPublicDir, 'application public dir must be available in the command worker container');
            /** @var Client $client */
            $client = $this->container->get(Client::class);
            \assert($client instanceof Client, 'command worker AMQP client must be available for app container');
            /** @var Channel $channel */
            $channel = $this->container->get(Channel::class);
            \assert($channel instanceof Channel, 'command worker AMQP channel must be available for app container');
            /** @var ChannelInterface $channelInterface */
            $channelInterface = $this->container->get(ChannelInterface::class);
            \assert(
                $channelInterface instanceof ChannelInterface,
                'command worker AMQP channel interface must be available for app container',
            );
            $applicationDefinitions = [
                ApplicationNamespace::class => $namespace,
                ApplicationPath::class => $path,
                ApplicationPublicDir::class => $publicDir,
                Client::class => $client,
                Channel::class => $channel,
                ChannelInterface::class => $channelInterface,
            ];
            $this->appContainer = ContainerBuilder::build($path, $namespace, $applicationDefinitions);
        }

        return $this->appContainer;
    }

    #[Override]
    public function drain(Command $command, MessageScope $scope): void
    {
        try {
            $container = $this->appContainer();
            $container->set(MessageScope::class, $scope);

            /** @var AmbientMessageScopeSource $scopeSource */
            $scopeSource = $container->get(AmbientMessageScopeSource::class);
            \assert($scopeSource instanceof AmbientMessageScopeSource, 'AmbientMessageScopeSource must be resolvable from app container');

            $scopeSource->using($scope, static function () use ($container, $command, $scope): void {
                /** @var CommandBus $bus */
                $bus = $container->get(CommandBus::class);
                \assert($bus instanceof CommandBus, 'CommandBus must be resolvable from app container');
                $bus->dispatch($command);

                /** @var UnitOfWork $unitOfWork */
                $unitOfWork = $container->get(UnitOfWork::class);
                \assert($unitOfWork instanceof UnitOfWork, 'UnitOfWork must be resolvable from app container');
                $unitOfWork->complete($scope);
            });
        } finally {
            gc_collect_cycles();
        }
    }
}
