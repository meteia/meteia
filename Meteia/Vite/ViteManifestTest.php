<?php

declare(strict_types=1);

namespace Meteia\Vite;

use Meteia\Configuration\Configuration;
use Meteia\Html\Elements\Head;
use Meteia\Html\Elements\Title;
use Meteia\Html\HtmlEncoder;
use Meteia\Html\Metadata;
use Meteia\Html\Scripts;
use Meteia\Html\Stylesheets;
use Meteia\Resources\ManifestSource;
use Meteia\Resources\ResourceBaseUri;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class ViteManifestTest extends TestCase
{
    public function testEmitsManifestEntryWhenDevelopmentServerIsDisabled(): void
    {
        $manifest = new ViteManifest($this->source(), $this->configuration());
        $head = $this->head();

        $head->addScripts($manifest->moduleScripts('/App/Pages/HomeEntry.ts'));

        static::assertSame(
            "<head><title>Untitled</title>\n\n<script src=\"/dist/App/Pages/HomeEntry-abc123.js\" type=\"module\"></script>\n</head>",
            new HtmlEncoder()->encode($head),
        );
    }

    public function testEmitsRawPathWhenDevelopmentServerIsEnabled(): void
    {
        $manifest = new ViteManifest($this->source(), $this->configuration([
            'VITE_BASE_URI' => 'https://example.test/dist/',
        ]));
        $head = $this->head();

        $head->addScripts($manifest->moduleScripts('/App/Pages/HomeEntry.ts'));

        static::assertSame(
            "<head><title>Untitled</title>\n\n<script src=\"/dist/App/Pages/HomeEntry.ts\" type=\"module\"></script>\n</head>",
            new HtmlEncoder()->encode($head),
        );
    }

    private function head(): Head
    {
        $baseUri = new ResourceBaseUri('/');

        return new Head(new Title('Untitled'), new Metadata(), new Stylesheets($baseUri), new Scripts($baseUri));
    }

    private function source(): ManifestSource
    {
        return new class implements ManifestSource {
            #[\Override]
            public function entries(): array
            {
                return [
                    'App/Pages/HomeEntry.ts' => [
                        'file' => 'App/Pages/HomeEntry-abc123.js',
                    ],
                ];
            }
        };
    }

    /**
     * @param array<string, string> $values
     */
    private function configuration(array $values = []): Configuration
    {
        return new class($values) implements Configuration {
            /**
             * @param array<string, string> $values
             */
            public function __construct(
                private readonly array $values,
            ) {}

            #[\Override]
            public function string(string $name, string|\Stringable $default): string
            {
                return $this->values[$name] ?? (string) $default;
            }

            #[\Override]
            public function int(string $name, int $default): int
            {
                return $default;
            }

            #[\Override]
            public function boolean(string $name, bool $default): bool
            {
                unset($name);

                return $default;
            }

            #[\Override]
            public function float(string $name, float $default): float
            {
                return $default;
            }
        };
    }
}
