<?php

declare(strict_types=1);

namespace Meteia\TypeScript\Fixtures\CommandPayloads;

use DateTimeImmutable;
use Meteia\Commands\Command;
use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\Uri;

final readonly class ScheduleLesson implements Command
{
    public function __construct(
        public array $tags,
        public DateTimeImmutable $requestedAt,
        public CausationId $causationId,
        public Uri $resource,
    ) {}
}
