<?php

declare(strict_types=1);

namespace Meteia\Realtime;

use DateTimeImmutable;
use SensitiveParameter;

final readonly class IssuedLiveViewSession
{
    public function __construct(
        public TabId $tab,
        #[SensitiveParameter]
        public LiveViewSessionToken $token,
        public DateTimeImmutable $expiresAt,
    ) {}
}
