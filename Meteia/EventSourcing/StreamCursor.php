<?php

declare(strict_types=1);

namespace Meteia\EventSourcing;

use InvalidArgumentException;
use JsonSerializable;
use Meteia\EventSourcing\Contracts\FromVersion;
use Override;
use SensitiveParameter;
use Stringable;
use Tuupola\Base62;

use function ctype_digit;
use function sprintf;
use function str_starts_with;
use function strlen;
use function substr;

/**
 * An opaque, position-based cursor into an aggregate's event stream.
 *
 * A cursor points just after a recorded event's {@see StreamVersion}, so the
 * next read returns the following events. The token is opaque to consumers:
 * they receive it from one page and hand it back to fetch the next, without
 * depending on its internal shape.
 */
final readonly class StreamCursor implements FromVersion, JsonSerializable, Stringable
{
    private const string PREFIX = 'evc_';

    public function __construct(
        private StreamVersion $after,
    ) {}

    /**
     * A cursor positioned immediately after the given stream version.
     */
    public static function after(StreamVersion $version): self
    {
        return new self($version);
    }

    /**
     * Reconstruct a cursor from a previously issued opaque token.
     *
     * @throws InvalidArgumentException when the token is not a valid stream cursor
     */
    public static function fromToken(#[SensitiveParameter] string $token): self
    {
        if (!str_starts_with($token, self::PREFIX)) {
            throw new InvalidArgumentException(sprintf(
                'Expected a stream cursor token prefixed with "%s" but got: %s',
                self::PREFIX,
                self::summarize($token),
            ));
        }

        $decoded = new Base62()->decode(substr($token, strlen(self::PREFIX)));
        if (!ctype_digit($decoded)) {
            throw new InvalidArgumentException(sprintf(
                'Stream cursor token does not decode to a stream position: %s',
                self::summarize($token),
            ));
        }

        return new self(new StreamVersion((int) $decoded));
    }

    public function token(): string
    {
        return self::PREFIX . new Base62()->encode((string) $this->after->asInt());
    }

    public function version(): StreamVersion
    {
        return $this->after;
    }

    #[Override]
    public function lowerBoundExclusive(): int
    {
        return $this->after->asInt();
    }

    #[Override]
    public function jsonSerialize(): string
    {
        return $this->token();
    }

    #[Override]
    public function __toString(): string
    {
        return $this->token();
    }

    private static function summarize(#[SensitiveParameter] string $token): string
    {
        return strlen($token) > 20 ? substr($token, 0, 20) . '...' : $token;
    }
}
