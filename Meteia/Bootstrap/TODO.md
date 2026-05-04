# Bootstrap / Resources / Vite — Deferred Refactors

Tracks Elegant-Objects rule violations the user explicitly chose to defer.
Re-evaluate when the surrounding code is touched.

## Resolved (this PR)

- ✅ `MeteiaKernel` is `final readonly` and implements `Meteia\Bootstrap\Kernel`.
- ✅ `MeteiaKernel::run` no longer calls `send()` directly — injects
  `Meteia\Http\ResponseSink` (default `PsrResponseSink`).
- ✅ `ApplicationPath::__construct` no longer mutates `$this->value`. Path
  resolution moved to `Meteia\Bootstrap\ResolveApplicationPath::from(...)`.
  Entry points updated.
- ✅ `ApplicationResources` rename completed previously; the new
  `Meteia\Resources\Resources` interface is now a stream source
  (`scriptsFor`/`stylesheetsFor`/`moduleScripts`/`styleLinks`) returning
  `iterable<Script>` / `iterable<Link>`.
- ✅ `Meteia\Vite\ViteFileManifestSource` is `final readonly`; memoization
  pushed into `Meteia\Resources\InProcessManifestCache` (entity).
- ✅ `Head` is `final` with `public readonly` sub-entities; chainable
  `addScripts(iterable)` / `addStylesheets(iterable)` accumulators added.
- ✅ `Title::set()` renamed to `rename()` per the locator rule (behavior
  name, not setter).
- ✅ Inheritance rule narrowed in CLAUDE.md to permit *nominal subtypes*:
  `final` extends with zero added methods/properties and a `readonly`
  parent state. Real harm (`ApplicationPath` mutating inherited state) is
  now blocked at the language level.
- ✅ `MiddlewareList` value object replaces raw `array $middleware`.
- ✅ `Meteia\Application` use-case layer scaffolding landed:
  `Command`/`Query`/`CommandResult`/`Accepted`/`Rejected`/`CommandBus`/
  `QueryBus`/`CommandEndpoint`/`QueryEndpoint`. Renamed `Handler` →
  `Endpoint` per CLAUDE.md `-er` rule.

## Still deferred

### Subclasses that add methods/state — convert to composition
**Files:** `Meteia/Cryptography/Hash.php`, `Meteia/Cryptography/SecretKey.php`,
`Meteia/ValueObjects/Identity/TemporaryFilesystemPath.php`,
`Meteia/ValueObjects/Identity/EmailAddress.php`,
`Meteia/ValueObjects/Identity/ProcessId.php`,
`Meteia/ValueObjects/Identity/CausationId.php`.

These compile today (parent state is `protected readonly`, so reads still
work) but they violate the new "zero added methods" nominal-subtype rule:
- `Hash` adds 5 encoding methods.
- `SecretKey` (Cryptography) adds `hmac()` + LEN_* constants used by
  parent via late-static-binding (template-method pattern).
- `TemporaryFilesystemPath` adds `__destruct` + static factories.
- `EmailAddress` extends `ValueObject` with `getAddress`/`getDisplayName`
  getters (also a getter rule violation).
- `ProcessId` / `CausationId` add `fromCommandId`/`fromEventId` parsers —
  relocate as `final readonly` parser objects (`ProcessIdFromCommandId`,
  `CausationIdFromEventId`) per CLAUDE.md "prefer parser objects".

`SecretKey` is the awkward one — its constants tune `UniqueId`'s
template-method behavior. A clean fix needs `UniqueId` itself to take
length parameters via constructor, eliminating the template-method
pattern. Out of scope for this PR.

### `UniqueId` exposes `public string $bytes` / `$token`
The whole `UniqueId` family uses public field access (CLAUDE.md rule 11
forbids field exposure) and template-method constants. Cross-cutting
refactor — left untouched here.

### `RequestHandler` is mutable + has no interface
`Meteia/Http/RequestHandler.php` mutates an internal `$middleware` array
via `append`/`prepend`. Class isn't `final`, no interface beyond PSR
ones. Treat as entity for now; document.

### Mago lint rule for nominal-subtype enforcement
Once mago grows a primitive for "subclass of X declares no new
methods/properties", add a rule keyed off `StringLiteral`,
`FilesystemPath`, `Uri`, `UniqueId`, `PrimitiveValueObject`. The
hand-rolled architecture test was rejected as a regex hack; rely on
review + CLAUDE.md until mago can express it.

### Consolidate event-sourcing namespaces
`Meteia\EventSourcing` (existing PDO-backed) overlaps with the planned
`Meteia\EventStore`/`Meteia\Projections` greenfield namespaces. Don't
fork — refactor `EventSourcing\Contracts\EventStream` into the new
shape (`StreamId`/`StreamVersion`/`ExpectedVersion`/`RecordedEvent`)
inside the existing namespace.

### Wire `Meteia\Application\CommandBus` to `Meteia\Commands` transport
Application-layer `CommandBus` is the use-case dispatcher; the existing
`Meteia\Commands` AMQP infrastructure is the transport. Adapter that
serializes `Application\Command` onto the AMQP exchange goes in a later
PR.

### `Head`/`Body` etc. are still mutable entities (locator pattern)
The user values DX of in-place mutation across nested partials, and the
new locator rule legitimizes this — Head is an entity that animates
mutable presentation state. If/when the value-object shape becomes
desirable (e.g. for caching), revisit.

### `RequestHandler::append` mutators inside `MeteiaKernel::requestHandler`
Currently four `append(...)` calls in `MeteiaKernel::requestHandler()`.
Cleaner if `RequestHandler` had a `with(...)` value-object shape — but
since `RequestHandler::process` consumes via `array_shift` it's
intrinsically entity. Keep mutators; document as locator behavior.
