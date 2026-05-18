<?php

declare(strict_types=1);

namespace Meteia\TypeScript\Fixtures\CommandPayloads;

use Meteia\Commands\Command;
use Meteia\Http\Cookies\SameSite;
use Meteia\TypeScript\Fixtures\UnsupportedPayload;

/**
 * @implements Command<void>
 */
final readonly class ConfigureCookie implements Command
{
    public function __construct(
        public SameSite $sameSite,
        public UnsupportedPayload $unsupportedPayload,
        public ?string $replyTo = null,
    ) {}
}
