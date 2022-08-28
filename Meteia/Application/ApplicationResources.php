<?php

declare(strict_types=1);

namespace Meteia\Application;

use Meteia\Bluestone\Contracts\Renderable;
use Meteia\Html\Elements\Head;
use Meteia\ValueObjects\Identity\FilesystemPath;
use function Meteia\Polyfills\common_prefix_length;

class ApplicationResources
{
    private array $knownFiles = [];

    private readonly string $prefix;

    public function __construct(
        private readonly ApplicationResourcesBaseUri $applicationResourcesBaseUri,
        ApplicationPublicDir $publicDir,
        FilesystemPath $manifest,
    ) {
        $commonLen = common_prefix_length([(string) $publicDir, (string) $manifest]);
        $relativeManifest = substr((string) $manifest, $commonLen);
        $this->prefix = '/' . trim(dirname($relativeManifest), '/');
        if ($manifest->isReadable()) {
            $this->knownFiles = json_decode($manifest->getContents(), true, 512, JSON_THROW_ON_ERROR);
        }
    }

    public function requireEntryModule(Renderable $renderable, Head $head): void
    {
        $entry = '/' . str_replace('\\', '/', $renderable::class) . 'Entry.ts';
        $this->requireModule($entry, $head);
    }

    public function requireModule(string $path, Head $head): void
    {
        $path = trim($path, '/');
        if ($this->knownFiles[$path]['file'] ?? false) {
            foreach ($this->knownFiles[$path]['imports'] ?? [] as $import) {
                $this->requireModule($import, $head);
            }
            $head->scripts->module($this->prefix . '/' . $this->knownFiles[$path]['file']);

            foreach ($this->knownFiles[$path]['css'] ?? [] as $import) {
                $head->stylesheets->load($this->prefix . '/' . $import, null, null);
            }

            return;
        }
        $head->scripts->module((string) $this->applicationResourcesBaseUri->withPath($path));
    }
}
