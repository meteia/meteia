<?php

declare(strict_types=1);

namespace Meteia\Database\EventSinks\Files\FileUploaded;

use Meteia\Events\EventSink;
use Meteia\Events\PublishedEvent;
use Meteia\ValueObjects\Identity\MessageScope;
use Override;
use Psr\Log\LoggerInterface;

final readonly class InsertIntoDatabase implements EventSink
{
    public function __construct(
        private LoggerInterface $log,
    ) {}

    #[Override]
    public function drain(PublishedEvent $event, MessageScope $scope): void
    {
        $this->log->info('File inserted into database', ['event' => $event->fact()::class]);
    }
}
