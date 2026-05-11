<?php

declare(strict_types=1);

namespace Meteia\WebAuthn\Configuration;

readonly class RelyingPartyOrigins
{
    /** @var list<string> */
    public array $origins;

    public function __construct(string ...$origins)
    {
        $this->origins = array_values($origins);
    }

    public static function fromCsv(string $csv): self
    {
        /** @var list<string> $parts */
        $parts = explode(',', $csv)
            |> (static fn(array $a): array => array_map('trim', $a))
            |> (static fn(array $a): array => array_values(array_filter($a, static fn(string $v): bool => $v !== '')));

        return new self(...$parts);
    }
}
