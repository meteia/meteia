<?php

declare(strict_types=1);

namespace Meteia\Vite;

use Meteia\Configuration\Configuration;
use Meteia\Html\Elements\Link;
use Meteia\Html\Elements\Script;
use Meteia\Resources\EntryTarget;
use Meteia\Resources\ManifestSource;
use Meteia\Resources\Resources;
use Override;

final readonly class ViteManifest implements Resources
{
    private const string PREFIX = '/dist/';

    public function __construct(
        private ManifestSource $source,
        private Configuration $configuration,
    ) {}

    #[Override]
    public function scriptsFor(EntryTarget $entry): iterable
    {
        yield from $this->moduleScripts($entry->path());
    }

    #[Override]
    public function stylesheetsFor(EntryTarget $entry): iterable
    {
        yield from $this->styleLinks($entry->path());
    }

    #[Override]
    public function moduleScripts(string $path): iterable
    {
        $path = trim($path, '/');
        $entries = $this->entries();
        $entry = $entries[$path] ?? null;
        if (!\is_array($entry) || ($entry['file'] ?? false) === false) {
            yield new Script(self::PREFIX . $path, type: 'module');

            return;
        }
        $imports = $entry['imports'] ?? [];
        \assert(\is_array($imports));
        foreach ($imports as $import) {
            \assert(\is_string($import));
            yield from $this->moduleScripts($import);
        }
        yield new Script(self::PREFIX . (string) $entry['file'], type: 'module');
    }

    #[Override]
    public function styleLinks(string $path): iterable
    {
        $path = trim($path, '/');
        $entries = $this->entries();
        $entry = $entries[$path] ?? null;
        if (!\is_array($entry) || ($entry['file'] ?? false) === false) {
            yield new Link('stylesheet', self::PREFIX . $path);

            return;
        }
        $imports = $entry['imports'] ?? [];
        \assert(\is_array($imports));
        foreach ($imports as $import) {
            \assert(\is_string($import));
            yield from $this->styleLinks($import);
        }
        yield new Link('stylesheet', self::PREFIX . (string) $entry['file']);
        $css = $entry['css'] ?? [];
        \assert(\is_array($css));
        foreach ($css as $cssPath) {
            yield new Link('stylesheet', self::PREFIX . (string) $cssPath);
        }
    }

    private function entries(): array
    {
        if ($this->configuration->string('VITE_BASE_URI', '') !== '') {
            return [];
        }

        return $this->source->entries();
    }
}
