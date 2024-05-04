<?php

declare(strict_types=1);

namespace Meteia\Application;

use Meteia\Html\Elements\Head;

readonly class ApplicationResources
{
    public array $knownFiles;
    public bool $useManifest;

    private string $prefix;

    public function __construct(ApplicationResourcesManifestPath $manifest)
    {
        $this->prefix = '/dist/';

        if ($manifest->isReadable()) {
            $this->useManifest = true;
            $this->knownFiles = json_decode($manifest->read(), true, 512, JSON_THROW_ON_ERROR);
        } else {
            $this->knownFiles = [];
        }
    }

    public function requireEntryModule(mixed $target, Head $head, bool $isReact = false): void
    {
        $targetName = \is_object($target) ? $target::class : $target;
        $entry = '/' . str_replace('\\', '/', $targetName) . 'Entry.' . ($isReact ? 'tsx' : 'ts');
        $this->requireModule($entry, $head);
    }

    public function requireModule(string $path, Head $head): void
    {
        $path = trim($path, '/');
        if ($this->knownFiles[$path]['file'] ?? false) {
            foreach ($this->knownFiles[$path]['imports'] ?? [] as $import) {
                $this->requireModule($import, $head);
            }
            $head->scripts->module($this->prefix . $this->knownFiles[$path]['file']);

            foreach ($this->knownFiles[$path]['css'] ?? [] as $import) {
                $head->stylesheets->load($this->prefix . $import, null, null);
            }

            return;
        }
        $head->scripts->module($this->prefix . $path);
    }
}
