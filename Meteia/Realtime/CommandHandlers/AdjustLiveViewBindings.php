<?php

declare(strict_types=1);

namespace Meteia\Realtime\CommandHandlers;

use Meteia\AdvancedMessageQueuing\Management\ExchangeName;
use Meteia\AdvancedMessageQueuing\Management\QueueName;
use Meteia\AdvancedMessageQueuing\Management\RabbitMqManagement;
use Meteia\AdvancedMessageQueuing\Management\RoutingKey;
use Meteia\AdvancedMessageQueuing\Management\VHostName;
use Meteia\Commands\Command;
use Meteia\Commands\CommandHandler;
use Meteia\Realtime\Commands\AdjustLiveViewBindings as AdjustLiveViewBindingsCommand;
use Meteia\Realtime\LiveViewExchange;
use Meteia\Realtime\LiveViewSessionAccepted;
use Meteia\Realtime\LiveViewSessionRejected;
use Meteia\Realtime\LiveViewSessionTokens;
use Meteia\Realtime\LiveViewTopic;
use Meteia\Realtime\LiveViewTopicPolicy;
use Override;
use Psr\Log\LoggerInterface;

/**
 * @implements CommandHandler<AdjustLiveViewBindingsCommand, void>
 */
final readonly class AdjustLiveViewBindings implements CommandHandler
{
    public function __construct(
        private LiveViewSessionTokens $tokens,
        private RabbitMqManagement $broker,
        private VHostName $vhost,
        private LiveViewExchange $exchange,
        private LiveViewTopicPolicy $policy,
        private LoggerInterface $log,
    ) {}

    #[Override]
    public function handle(Command $command): void
    {
        \assert($command instanceof AdjustLiveViewBindingsCommand, 'handler dispatched for matching command');

        $verification = $this->tokens->verify($command->token);
        if ($verification instanceof LiveViewSessionRejected) {
            $this->log->info('live view bindings adjust rejected', ['reason' => $verification->reason]);

            return;
        }

        \assert($verification instanceof LiveViewSessionAccepted, 'verification is accepted or rejected');

        $queue = new QueueName('live-view.' . $verification->tab->token());
        $exchange = new ExchangeName($this->exchange->toNative());

        foreach ($command->add as $rawTopic) {
            $topic = new LiveViewTopic($rawTopic);
            if (!$this->policy->allows($verification, $topic)) {
                $this->log->warning('live view bind denied by policy', [
                    'topic' => $rawTopic,
                    'subject' => $verification->subject,
                ]);

                continue;
            }

            $result = $this->broker->bindQueueToExchange(
                $this->vhost,
                $queue,
                $exchange,
                new RoutingKey($topic->toNative()),
            );
            if (!$result->accepted()) {
                $this->log->error('live view bind failed', ['detail' => $result->describe()]);
            }
        }

        foreach ($command->remove as $rawTopic) {
            $topic = new LiveViewTopic($rawTopic);
            if (!$this->policy->allows($verification, $topic)) {
                $this->log->warning('live view unbind denied by policy', [
                    'topic' => $rawTopic,
                    'subject' => $verification->subject,
                ]);

                continue;
            }

            $result = $this->broker->unbindQueueFromExchange(
                $this->vhost,
                $queue,
                $exchange,
                new RoutingKey($topic->toNative()),
            );
            if (!$result->accepted()) {
                $this->log->error('live view unbind failed', ['detail' => $result->describe()]);
            }
        }
    }
}
