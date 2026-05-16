<?php

declare(strict_types=1);

namespace Meteia\Fixtures\EventSinks\PsrConvention\FixtureOccurred;

use Meteia\Events\EventSink;
use Meteia\Events\PublishedEvent;
use Meteia\ValueObjects\Identity\MessageScope;
use Override;

final readonly class RecordFixture implements EventSink
{
    #[Override]
    public function drain(PublishedEvent $event, MessageScope $scope): void {}
}
