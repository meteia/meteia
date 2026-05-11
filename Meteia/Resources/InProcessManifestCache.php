<?php

declare(strict_types=1);

namespace Meteia\Resources;

use Meteia\ValueObjects\Contracts\Path;
use Override;

final class InProcessManifestCache implements ManifestCache
{
    /** @var array<string, array<string, mixed>> */
    private array $cache = [];

    #[Override]
    public function entriesAt(Path $manifest): array
    {
        $key = (string) $manifest;

        return $this->cache[$key] ??= $this->load($manifest);
    }

    /**
     * @return array<string, mixed>
     */
    private function load(Path $manifest): array
    {
        if (!$manifest->isReadable()) {
            return [];
        }

        /** @var array<string, mixed> $decoded */
        $decoded = json_decode($manifest->read(), true, 512, JSON_THROW_ON_ERROR);
        \assert(\is_array($decoded));

        return $decoded;
    }
}
