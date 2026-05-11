<?php

declare(strict_types=1);

namespace Meteia\MessageStreams;

use Meteia\ValueObjects\ImmutableArrayValueObject;

final readonly class RecordedMessages extends ImmutableArrayValueObject
{
    public const string TYPE = RecordedMessage::class;
}
