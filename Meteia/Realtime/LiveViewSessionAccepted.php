<?php

declare(strict_types=1);

namespace Meteia\Realtime;

use Override;

final readonly class LiveViewSessionAccepted implements LiveViewSessionVerification
{
    /**
     * @param list<LiveViewTopic> $topics
     */
    public function __construct(
        public string $subject,
        public TabId $tab,
        public array $topics,
    ) {}

    #[Override]
    public function accepted(): bool
    {
        return true;
    }
}
