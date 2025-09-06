<?php

declare(strict_types=1);

namespace Meteia\Http\Cookies;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

trait CookieString
{
    public function toString(
        string $name,
        string $value,
        UriInterface $url,
        ?\DateTimeInterface $expiresAt = null,
        bool $httpOnly = true,
        SameSite $sameSite = SameSite::Lax,
    ): string {
        $maxAge = $expiresAt ? $expiresAt->getTimestamp() - time() : null;
        $kvParts = array_filter([
            sprintf('%s=%s', $name, $value),
            'Expires' => $expiresAt?->format(DATE_RFC7231),
            'Max-Age' => $maxAge,
            'Domain' => '.' . $url->getHost(),
            'Path' => $url->getPath(),
            'SameSite' => $sameSite->value,
        ]);

        $kvParts = array_map(static fn($key, $val) => $key . '=' . $val, array_keys($kvParts), $kvParts);

        $secure = $url->getScheme() === 'https' ? true : null;
        $flagParts = array_filter([$secure, $httpOnly]);
        $parts = array_merge($kvParts, $flagParts);

        return implode('; ', $parts);
    }
}
