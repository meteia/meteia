<?php

declare(strict_types=1);

namespace Meteia\Vite;

use Meteia\Html\Elements\Head;
use Meteia\Resources\EntryTarget;
use Meteia\Resources\ManifestSource;
use Meteia\Resources\Resources;

final readonly class ViteManifest implements Resources
{
    private const string PREFIX = '/dist/';

    public function __construct(
        private ManifestSource $source,
    ) {}

    #[\Override]
    public function requireEntry(EntryTarget $entry, Head $head): void
    {
        $this->requireModule($entry->path(), $head);
    }

    #[\Override]
    public function requireModule(string $path, Head $head): void
    {
        $path = trim($path, '/');
        $entries = $this->source->entries();
        if ($entries[$path]['file'] ?? false) {
            foreach ($entries[$path]['imports'] ?? [] as $import) {
                $this->requireModule($import, $head);
            }
            $head->scripts->module(self::PREFIX . $entries[$path]['file']);

            foreach ($entries[$path]['css'] ?? [] as $import) {
                $head->stylesheets->load(self::PREFIX . $import);
            }

            return;
        }
        $head->scripts->module(self::PREFIX . $path);
    }

    #[\Override]
    public function requireStyle(string $path, Head $head): void
    {
        $path = trim($path, '/');
        $entries = $this->source->entries();
        if ($entries[$path]['file'] ?? false) {
            foreach ($entries[$path]['imports'] ?? [] as $import) {
                $this->requireStyle($import, $head);
            }
            $head->stylesheets->load(self::PREFIX . $entries[$path]['file']);

            foreach ($entries[$path]['css'] ?? [] as $css) {
                $head->stylesheets->load(self::PREFIX . $css);
            }

            return;
        }
        $head->stylesheets->load(self::PREFIX . $path);
    }
}
