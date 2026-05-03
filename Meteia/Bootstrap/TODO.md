# Bootstrap / Resources / Vite — Deferred Refactors

Tracks Elegant-Objects rule violations the user explicitly chose to defer
during the `Meteia\Application` → `Bootstrap`/`Resources`/`Vite` split.
Re-evaluate when the surrounding code is touched.

## Deferred — explicit user calls

### Inheritance from project base classes
**Files:** `Meteia/Bootstrap/{ApplicationPath,ApplicationPublicDir,ApplicationNamespace,RepositoryPath}.php`,
`Meteia/Resources/{ResourceBaseUri,ResourceManifestPath}.php`

These all `extends FilesystemPath` / `Uri` / `StringLiteral`. CLAUDE.md
forbids implementation inheritance ("classes are `final`. Share via
composition/decorators, not abstract base classes or traits").

Replacement pattern (per type):
```php
final readonly class ApplicationPath {
    public function __construct(private FilesystemPath $path) {}
    // forward only what callers actually use (join, read, isReadable, …)
}
```
**Why deferred:** user is torn — convenience of inherited `FilesystemPath`
behaviour (`join`, `read`, `find`, `hash`, `mimeType`, etc.) is large; the
typed primitives are used widely as path tokens. Composition would force a
forwarding API per VO and break every direct call.

### Head mutation in `ViteManifest::require*`
**File:** `Meteia/Vite/ViteManifest.php`

`requireModule`/`requireStyle`/`requireEntry` mutate `Head` in place
(`$head->scripts->module(...)`, `$head->stylesheets->load(...)`). Field
exposure + mutation. Cleaner: return a new `Head` or push registration
into Head (`$head->withScript(...)` carrying `#[\NoDiscard]`).
**Why deferred:** user values DX — templates can hook into the same Head
across nested partials without threading a return value back. Touching
this means redesigning `Head`/`Scripts`/`Stylesheets` as immutable.

## Other deferred items (call out before next pass)

### `MeteiaKernel` is not `final`, has no interface
**File:** `Meteia/Bootstrap/MeteiaKernel.php`

`readonly class` (not `final`). Public methods `run`/`container`/`requestHandler`
have no interface (CLAUDE.md #7). Add `Kernel` interface; mark `final`
unless framework reflection forbids it.

### `MeteiaKernel::run` calls `send()` directly
Boundary I/O makes the method untestable without side effects. Inject a
`ResponseSink` interface so tests can assert the response value.

### `ApplicationPath` does work in constructor
**File:** `Meteia/Bootstrap/ApplicationPath.php`

`realpath()` + reassignment of `$this->value` violates "constructors
assign only" + "no mutation". Move resolution into a parser
(`ResolvedApplicationPath::from(string)`) or into the DI factory and
have callers in `public/index.php` / kernels pass already-real paths.

### `ApplicationResources` callers in framework consumers
The old `Meteia\Application\ApplicationResources` was renamed to
`Meteia\Vite\ViteManifest implements Meteia\Resources\Resources` and the
method `requireEntryModule(mixed $target, ...)` was replaced with
`requireEntry(EntryTarget $entry, Head $head)` (callers build either
`new NamedEntry(...)` or `new ObjectEntry(...)`).

External app code that consumed the old API must migrate:
- `$res->requireEntryModule($obj, $head, true)`
  → `$res->requireEntry(new ObjectEntry($obj, isReact: true), $head)`
- `$res->requireEntryModule('Foo/Bar', $head)`
  → `$res->requireEntry(new NamedEntry('Foo/Bar'), $head)`
- Type-hint `Meteia\Resources\Resources` (interface) instead of the
  concrete `ApplicationResources`.

### `ViteFileManifestSource` is not `readonly`
**File:** `Meteia/Vite/ViteFileManifestSource.php`

Memoizes parsed manifest in `private ?array $entries`. Mutation needed
for the cache. Consider extracting an explicit `Memoized` decorator over
a pure `entries(): array` source so the manifest object itself stays
immutable.

## DDD/ES/CQRS direction (next milestones)

The `Meteia\Application` namespace is now empty and reserved for the
DDD "Application Layer":
- `CommandHandler<T>` / `QueryHandler<T>` interfaces
- Command/Query bus contracts (separate from current `Meteia\Commands`
  message-queue handler, which is transport, not use-case orchestration)
- Use-case objects (one per command/query)
- Sagas / process managers if/when introduced

Event sourcing pieces likely want their own contexts:
- `Meteia\EventStore` — append/read streams, optimistic concurrency
- `Meteia\Projections` — read-model projectors
- Aggregates live in each domain bounded context, not here
