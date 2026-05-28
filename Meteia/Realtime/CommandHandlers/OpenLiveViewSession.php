<?php

declare(strict_types=1);

namespace Meteia\Realtime\CommandHandlers;

use Bunny\Channel;
use Bunny\Protocol\MethodQueueDeclareOkFrame;
use Meteia\AdvancedMessageQueuing\Management\ExchangeName;
use Meteia\AdvancedMessageQueuing\Management\QueueName;
use Meteia\AdvancedMessageQueuing\Management\RabbitMqManagement;
use Meteia\AdvancedMessageQueuing\Management\RoutingKey;
use Meteia\AdvancedMessageQueuing\Management\VHostName;
use Meteia\Commands\Command;
use Meteia\Commands\CommandHandler;
use Meteia\Commands\Exceptions\MissingReplyDestination;
use Meteia\Commands\ReplyDestination;
use Meteia\Commands\ReplyDestinationSource;
use Meteia\Realtime\Commands\OpenLiveViewSession as OpenLiveViewSessionCommand;
use Meteia\Realtime\LiveViewExchange;
use Meteia\Realtime\LiveViewSessionAccepted;
use Meteia\Realtime\LiveViewSessionRejected;
use Meteia\Realtime\LiveViewSessionTokens;
use Meteia\Realtime\LiveViewTopic;
use Meteia\Realtime\LiveViewTopicPolicy;
use Override;
use Psr\Log\LoggerInterface;

/**
 * @implements CommandHandler<OpenLiveViewSessionCommand, void>
 */
final readonly class OpenLiveViewSession implements CommandHandler
{
    private const int QUEUE_EXPIRES_MS = 60_000;

    public function __construct(
        private LiveViewSessionTokens $tokens,
        private ReplyDestinationSource $replyDestinations,
        private RabbitMqManagement $broker,
        private Channel $channel,
        private VHostName $vhost,
        private LiveViewExchange $exchange,
        private LiveViewTopicPolicy $policy,
        private LoggerInterface $log,
    ) {}

    #[Override]
    public function handle(Command $command): void
    {
        \assert($command instanceof OpenLiveViewSessionCommand, 'handler dispatched for matching command');

        try {
            $replyDestination = $this->replyDestinations->current();
        } catch (MissingReplyDestination) {
            $this->log->warning('live view session rejected: missing reply destination');

            return;
        }

        $verification = $this->tokens->verify($command->token);
        if ($verification instanceof LiveViewSessionRejected) {
            $this->log->info('live view session rejected', ['reason' => $verification->reason]);
            $this->publishOpenError($replyDestination, 'rejected');

            return;
        }

        \assert($verification instanceof LiveViewSessionAccepted, 'verification is accepted or rejected');

        $queue = $this->declareQueue($verification);
        $exchange = new ExchangeName($this->exchange->toNative());

        $topics = $this->withUserTopic($verification);
        foreach ($topics as $topic) {
            if (!$this->policy->allows($verification, $topic)) {
                $this->log->warning('live view bind denied by policy', [
                    'topic' => $topic->toNative(),
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
                $this->log->error('live view topic bind failed', ['detail' => $result->describe()]);
            }
        }

        $this->log->info('live view session opened', [
            'subject' => $verification->subject,
            'tab' => $verification->tab->token(),
            'queue' => $queue->toNative(),
            'topicCount' => \count($topics),
        ]);

        $this->publishOpenReply($replyDestination, $queue);
        $this->channel->publish(
            '<!-- live-view-ready -->',
            ['content-type' => 'text/html'],
            '',
            $queue->toNative(),
        );
    }

    /**
     * @return list<LiveViewTopic>
     */
    private function withUserTopic(LiveViewSessionAccepted $session): array
    {
        $userTopic = LiveViewTopic::forUserSubject($session->subject);
        $userTopicValue = $userTopic->toNative();
        foreach ($session->topics as $topic) {
            if ($topic->toNative() === $userTopicValue) {
                return $session->topics;
            }
        }

        return [...$session->topics, $userTopic];
    }

    private function declareQueue(LiveViewSessionAccepted $session): QueueName
    {
        $name = 'live-view.' . $session->tab->token();
        $result = $this->channel->queueDeclare(
            queue: $name,
            durable: false,
            exclusive: false,
            autoDelete: true,
            arguments: ['x-expires' => self::QUEUE_EXPIRES_MS],
        );
        \assert($result instanceof MethodQueueDeclareOkFrame, 'RabbitMQ must return the declared live view queue name');

        return new QueueName($result->queue);
    }

    private function publishOpenReply(ReplyDestination $replyDestination, QueueName $queue): void
    {
        $payload = json_encode([
            'destination' => '/amq/queue/' . $queue->toNative(),
        ], JSON_THROW_ON_ERROR);

        $this->channel->publish(
            $payload,
            ['content-type' => 'application/json'],
            '',
            $replyDestination->queueName(),
        );
    }

    private function publishOpenError(ReplyDestination $replyDestination, string $reason): void
    {
        $payload = json_encode([
            'error' => $reason,
        ], JSON_THROW_ON_ERROR);

        $this->channel->publish(
            $payload,
            ['content-type' => 'application/json'],
            '',
            $replyDestination->queueName(),
        );
    }
}
