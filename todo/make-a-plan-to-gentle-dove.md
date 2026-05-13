# Plan: `Attribution` for CQRS

## Context

The Authentication module landed `RequestingUser` polymorphism (`AnonymousUser` / `IdentifiedUser(UserId)` / `SystemUser(SystemId)`) with `pick` / `fold` and a `UserId` domain interface. CQRS infrastructure is already in place (`CommandMessage`, `RecordedEvent`, `IssuedCommands`, `PdoEventStream`, `PdoMessageStream`, AMQ outbox/inbox), but every command and event flows through these envelopes carrying only `CausationId` + `CorrelationId`. There is no record of **who issued the command** or **who caused the event**.

`Meteia/Authentication/TODO.md:31-39` flagged this as the next deferred item — and it is the most actionable: the schemas, envelopes, and HTTP/AMQ boundary code all already exist and are shaped to receive an attribution. Adding it now closes the audit-trail gap and unblocks every downstream "who did X" query.

This change introduces a polymorphic `Attribution` value object, threads it through the ambient `MessageScope`, stamps it into both `issued_commands` and `domain_events` (and `message_streams`) rows, and carries it across HTTP middleware **and** AMQ wire format so the user identity does not get lost when commands cross process boundaries.

User-confirmed decisions:
- Envelopes will carry `MessageScope` directly instead of duplicating `(CausationId, CorrelationId)`. Cleaner; matches what `ImmediateUnitOfWork::complete` already does.
- HTTP wiring (`AttributeMessageScope` middleware) is in scope.
- AMQ wire-format carry is in scope.
- Read-side `UserId` reconstruction uses an app-bound `UserIdFromString` parser interface.

---

## Design summary

### 1. `Attribution` interface + three subtypes — `Meteia/Authentication/`

```php
// Attribution.php
interface Attribution
{
    public function inscribeOn(AttributionParameters $params): void;
    public function equals(self $other): bool;
}

// AttributionParameters.php (the sink — keeps Attribution out of getter land)
interface AttributionParameters
{
    public function asAnonymous(): void;
    public function asUser(UserId $userId): void;
    public function asSystem(SystemId $systemId): void;
}
```

- `AnonymousAttribution` — `inscribeOn` → `$p->asAnonymous()`.
- `UserAttribution(UserId)` — `inscribeOn` → `$p->asUser($this->userId)`.
- `SystemAttribution(SystemId)` — `inscribeOn` → `$p->asSystem($this->systemId)`.

Each subtype is `final readonly`, zero added state beyond the constructor-injected principal, `equals(self)` follows the existing `SystemId::equals` pattern at `Meteia/Authentication/SystemId.php:17`.

### 2. `RequestingUser::attribution(): Attribution`

Add to interface `Meteia/Authentication/RequestingUser.php:7`; implement on each of the three subtypes:
- `AnonymousUser` → `new AnonymousAttribution()`
- `IdentifiedUser` → `new UserAttribution($this->userId)`
- `SystemUser` → `new SystemAttribution($this->systemId)`

### 3. `MessageScope` carries `Attribution`

`Meteia/ValueObjects/Identity/MessageScope.php:9` — add fourth constructor parameter `Attribution $attribution` (defaulted to `new AnonymousAttribution()` so the ~12 construction sites don't all need updating). Add:
- `attribution(): Attribution`
- `#[\NoDiscard] attributedTo(Attribution $a): self` (clone-with)

Existing `causedBy()` and `inheriting()` preserve the attribution untouched via clone semantics. A consumer inheriting a correlation must not fabricate identity.

### 4. Envelope refactor — replace `(CausationId, CorrelationId)` with `MessageScope`

Files (signature changes only — existing `causationId()` / `correlationId()` accessors stay as thin delegators so callers and tests don't churn):

- `Meteia/Domain/CommandMetadata.php:12` — `(AggregateRootId, MessageScope, DateTimeImmutable)`.
- `Meteia/Domain/CommandMessage.php:17` — store `MessageScope`; keep accessors.
- `Meteia/Domain/PendingCommand.php:30` — `issuedWith(MessageScope $scope, DateTimeImmutable $issuedAt): CommandMessage`.
- `Meteia/EventSourcing/PendingEvent.php:35` — `recordedWith(MessageScope $scope, DateTimeImmutable $occurredAt): RecordedEvent`.
- `Meteia/EventSourcing/RecordedEvent.php:13` — store `MessageScope`.
- `Meteia/MessageStreams/RecordedMessage.php:13` — store `MessageScope`.
- `Meteia/Domain/ImmediateUnitOfWork.php:56,71` — drop `->causationId()/->correlationId()` extraction, pass `$scope` straight through.
- **`Meteia/Domain/DeferredUnitOfWork.php:97-101,129`** — same change. **Now the default `UnitOfWork` binding** (commit `80b7adb6` switched `Meteia/Domain/DependencyInjection.php` from `ImmediateUnitOfWork` to `DeferredUnitOfWork`). `DeferredUnitOfWork::complete()` runs **after `fastcgi_finish_request()`** from `MeteiaKernel::flushUnitOfWork()` (`Meteia/Bootstrap/MeteiaKernel.php`). It reads `MessageScope` from the container — so the post-auth `MessageScope` set by `AttributeMessageScope` (§7) is what gets used at flush time. Confirmed safe: middleware already rebinds `MessageScope::class` in the container.

### 5. Persistence

**New migration: `Meteia/migrations/20260512120000.i.add_attribution_to_envelopes.sql`**

Schema for all three message stores:
- `attribution_kind TINYINT UNSIGNED NOT NULL DEFAULT 0` — values map to the `AttributionKind` backed enum (`0=Anonymous`, `1=User`, `2=System`).
- `attribution_principal VARBINARY(255) NOT NULL DEFAULT ''` — `UserId::asString()` for users, `SystemId::asString()` (e.g. `system:foo`) for system, empty for anonymous. `VARBINARY(255)` matches existing `command_type` width and accommodates any string-encoded `UserId`.
- Composite index `(attribution_kind, attribution_principal)` for "everything actor X did" audit queries.

`ALTER TABLE` covers `domain_events`, `issued_commands`, `message_streams`. `DEFAULT` values handle pre-existing dev rows as `AnonymousAttribution` — no backfill needed.

**Backed enum — `Meteia/Authentication/AttributionKind.php`** — used everywhere the kind appears in code (sink, row parser, headers, bind params). DB column stays TINYINT UNSIGNED; only `->value` crosses the persistence/wire boundary.

```php
enum AttributionKind: int {
    case Anonymous = 0;
    case User      = 1;
    case System    = 2;
}
```

**Bind sink — `Meteia/Authentication/PdoAttributionParameters.php`** — mutable boundary helper (CLAUDE.md sanctions mutation at infrastructure edges):

```php
final class PdoAttributionParameters implements AttributionParameters {
    public AttributionKind $kind = AttributionKind::Anonymous;
    public string $principal = '';
    public function asAnonymous(): void {}
    public function asUser(UserId $u): void { $this->kind = AttributionKind::User; $this->principal = $u->asString(); }
    public function asSystem(SystemId $s): void { $this->kind = AttributionKind::System; $this->principal = $s->asString(); }
}
```

Bind sites convert to scalar at the SQL boundary only: `'attributionKind' => $attr->kind->value`.

Used in:
- `Meteia/Domain/PdoIssuedCommands.php:31` — write `:attributionKind`, `:attributionPrincipal`.
- `Meteia/EventSourcing/PdoEventStream.php` — append path.
- `Meteia/MessageStreams/PdoMessageStream.php` — append path.

### 6. Read side — `UserIdFromString` parser

**New interface — `Meteia/Authentication/UserIdFromString.php`:**
```php
interface UserIdFromString { public function parse(string $serialized): UserId; }
```

Each app binds its concrete impl in DI. (Framework cannot — `UserId` is intentionally open per `TODO.md:20-23`.)

**New boundary parser — `Meteia/Authentication/AttributionRow.php`:**
```php
final readonly class AttributionRow {
    public function __construct(private AttributionKind $kind, private string $principal) {}
    public static function fromColumns(int $kind, string $principal): self {
        return new self(AttributionKind::from($kind), $principal);
    }
    public function asAttribution(UserIdFromString $parser): Attribution {
        return match ($this->kind) {
            AttributionKind::Anonymous => new AnonymousAttribution(),
            AttributionKind::User      => new UserAttribution($parser->parse($this->principal)),
            AttributionKind::System    => new SystemAttribution(new SystemId(substr($this->principal, 7))), // strip "system:" prefix
        };
    }
}
```

`AttributionKind::from()` at the column-cast seam catches stray ints from the DB at the boundary. The `match` in `asAttribution` is exhaustive over the enum — explicitly boundary code per CLAUDE.md "match only at outer boundary." Bare ints never appear in code outside `fromColumns()` and the SQL bind sites.

**Update SELECT projections + hydrators:**
- `Meteia/EventSourcing/PdoEventStream.php:77` — add the two columns; pass to hydrator.
- `Meteia/EventSourcing/PdoGlobalEventStream.php:31` — same.
- `Meteia/MessageStreams/PdoMessageStream.php:60-65` — same.
- Each hydrator gains a `UserIdFromString` constructor dependency.

### 7. HTTP wiring — `AttributeMessageScope` middleware

`Meteia/Http/Middleware/SeedMessageScope.php` continues to seed `MessageScope` with `AnonymousAttribution` (it runs before authentication can resolve a user).

**New middleware — `Meteia/Http/Middleware/AttributeMessageScope.php`** — runs after the auth resolver, before any handler that issues commands/events:
```php
final readonly class AttributeMessageScope implements MiddlewareInterface {
    public function __construct(private Container $container) {}
    public function process(ServerRequestInterface $request, RequestHandlerInterface $next): ResponseInterface {
        $user = $request->getAttribute(RequestingUser::class);
        \assert($user instanceof RequestingUser);
        $scope = $request->getAttribute(MessageScope::class)->attributedTo($user->attribution());
        $request = $request->withAttribute(MessageScope::class, $scope);
        $this->container->set(MessageScope::class, $scope);
        $this->container->set(MessageScopeSource::class, new RequestMessageScopeSource($request));
        return $next->handle($request);
    }
}
```

### 8. AMQ wire format

`Meteia/AdvancedMessageQueuing/MessageContext.php` gains an `Attribution` field. Two new header keys:
- `attribution-kind` (int as string: `"0"` / `"1"` / `"2"`)
- `attribution-principal` (raw string, empty for anonymous)

**Outbox path** — `BunnyCommandOutbox.php:34`, `BunnyEventOutbox.php`, `BunnyDelayedCommandOutbox.php` — already call `MessageContext::fromScope($scope)`. The factory pulls `$scope->attribution()` into the context; `headersWithMessageId()` emits the two extra headers (use `PdoAttributionParameters` reuse or a sibling `HeaderAttributionParameters` sink that writes into a header array).

**Inbox path** — `BunnyCommandInbox.php:50-53`, `BunnyEventInbox.php` — currently rebuild `MessageScope` from headers. Add parse step:
```php
$attrRow = AttributionRow::fromColumns(
    (int) ($message->headers['attribution-kind'] ?? 0),
    (string) ($message->headers['attribution-principal'] ?? ''),
);
$scope = (new MessageScope($correlationId, $causationId, $processId))
    ->attributedTo($attrRow->asAttribution($this->userIdParser));
```

Inbox classes gain a `UserIdFromString` constructor dependency.

### 9. Defaults at remaining construction sites

- `Meteia/ValueObjects/DependencyInjection.php` — `MessageScope` factory uses `new AnonymousAttribution()`.
- `Meteia/Http/Middleware/SeedMessageScope.php:35` — `new MessageScope($correlationId, $causationId, $this->processId, new AnonymousAttribution())`.
- `Meteia/AdvancedMessageQueuing/AmbientMessageScopeSource.php` and its tests — pass through default.

---

## Critical files to modify

| Area | Files |
|---|---|
| New types | `Meteia/Authentication/{Attribution,AttributionKind,AttributionParameters,AnonymousAttribution,UserAttribution,SystemAttribution,AttributionRow,UserIdFromString,PdoAttributionParameters}.php` |
| RequestingUser projection | `Meteia/Authentication/{RequestingUser,AnonymousUser,IdentifiedUser,SystemUser}.php` |
| Scope | `Meteia/ValueObjects/Identity/MessageScope.php` |
| Envelopes | `Meteia/Domain/{CommandMetadata,CommandMessage,PendingCommand,ImmediateUnitOfWork,DeferredUnitOfWork}.php`; `Meteia/EventSourcing/{PendingEvent,RecordedEvent}.php`; `Meteia/MessageStreams/RecordedMessage.php` |
| Persistence write | `Meteia/Domain/PdoIssuedCommands.php`; `Meteia/EventSourcing/PdoEventStream.php`; `Meteia/MessageStreams/PdoMessageStream.php` |
| Persistence read | Same three above (hydrators + SELECT lists); `Meteia/EventSourcing/PdoGlobalEventStream.php` |
| HTTP boundary | `Meteia/Http/Middleware/SeedMessageScope.php`; new `Meteia/Http/Middleware/AttributeMessageScope.php` |
| AMQ wire | `Meteia/AdvancedMessageQueuing/MessageContext.php`; `Meteia/AdvancedMessageQueuing/Bunny/{BunnyCommandOutbox,BunnyEventOutbox,BunnyDelayedCommandOutbox,BunnyCommandInbox,BunnyEventInbox}.php` |
| DI | `Meteia/ValueObjects/DependencyInjection.php`; `Meteia/AdvancedMessageQueuing/DependencyInjection.php` (UserIdFromString binding stub or doc) |
| Migration | `Meteia/migrations/20260512120000.i.add_attribution_to_envelopes.sql` |
| Docs | `Meteia/Authentication/TODO.md` — move Attribution from "Deferred" to "Done"; add follow-up TODOs |

## Reused / referenced

- `SystemId::equals` pattern at `Meteia/Authentication/SystemId.php:17` (for `Attribution::equals`).
- `MessageScope::causedBy()` clone-with pattern at `Meteia/ValueObjects/Identity/MessageScope.php:33` (for `attributedTo()`).
- `MessageContext::fromScope` factory at `Meteia/AdvancedMessageQueuing/MessageContext.php:20` (extended, not replaced).
- `RequestMessageScopeSource` at `Meteia/Http/RequestMessageScopeSource.php:13` (reused by new middleware).

## Implementation sequencing

1. New types: `Attribution` + `AttributionParameters` + three subtypes + `AttributionRow` + `UserIdFromString` + `PdoAttributionParameters`.
2. Extend `RequestingUser` interface + three impls.
3. Extend `MessageScope` (4th field + `attributedTo()`).
4. Refactor envelopes to carry `MessageScope` (keep delegating accessors).
5. Update `ImmediateUnitOfWork::complete`.
6. Migration file.
7. Update three Pdo store `append()` methods; update their hydrators + SELECTs (inject `UserIdFromString`).
8. HTTP: seed `AnonymousAttribution` in `SeedMessageScope`; new `AttributeMessageScope` middleware.
9. AMQ: extend `MessageContext` with attribution headers; update outboxes (header emit) and inboxes (header parse + `UserIdFromString` injection).
10. Fix breaking tests; add new tests.
11. `mago format` + `mago lint` on every touched file.

## Verification

**Static checks**
- `mago format` and `mago lint` clean on every touched file.
- `phpstan` / `psalm` (whichever is wired) clean.

**Test suite**
- `vendor/bin/phpunit` green. Targets that must pass:
  - `Meteia/Authentication/AttributionTest.php` (new) — one behavior per test for `inscribeOn` and `equals` per subtype, using a fake `AttributionParameters` recorder.
  - `Meteia/Authentication/RequestingUserAttributionTest.php` (new) — each `RequestingUser` impl projects the right `Attribution`.
  - `Meteia/ValueObjects/Identity/MessageScopeTest.php` (extend or new) — `attributedTo()` is clone-with; `causedBy()` / `inheriting()` preserve attribution.
  - `Meteia/Domain/PdoIssuedCommandsTest.php` — add round-trip cases: append with `AnonymousAttribution`, `UserAttribution`, `SystemAttribution`; read back; assert equality. Update existing schema + `CommandMetadata` constructions (lines 34, 52, schema block 89-100).
  - **Kernel flush path test** — `DeferredUnitOfWork::complete()` reads `MessageScope` from the container after the response is sent. Add a test that verifies the post-auth scope (set by `AttributeMessageScope`) is the one used at flush time, and that the resulting `issued_commands` row carries the user's attribution.
  - `Meteia/EventSourcing/PdoEventStreamTest.php` — same round-trip; update schema block 188-200, envelope constructions 47-51 and 155-157.
  - `Meteia/MessageStreams/PdoMessageStreamTest.php` — same.
  - `Meteia/AdvancedMessageQueuing/AmbientMessageScopeSourceTest.php:76` — pass anonymous attribution (or rely on defaulted ctor).
  - `Meteia/Http/Middleware/SeedMessageScopeTest.php` — assert seeded scope carries `AnonymousAttribution`.
  - New `Meteia/Http/Middleware/AttributeMessageScopeTest.php` — given `IdentifiedUser($userId)` on request, `MessageScope::class` ends up rebound with `UserAttribution($userId)`.
  - New AMQ round-trip test — publish via outbox, simulate inbox parse, assert `Attribution` survives.

**Custom PHPUnit constraint**
- Add `EqualsAttribution($expected)` to push attribution equality into one place (one behavioral assertion per test, per CLAUDE.md line 63).

**Manual smoke test**
- Run a real migration against a dev DB: `meteia db:migrate`.
- Issue a command via an HTTP endpoint while authenticated; query `issued_commands` and confirm `attribution_kind=1`, `attribution_principal` matches the logged-in user's `UserId::asString()`.
- Issue a command via an AMQ-driven worker; confirm headers carry attribution and the resulting row preserves it.
- Issue a system-driven command (e.g. from a scheduled task); confirm `attribution_kind=2`, `attribution_principal=system:{name}`.

## Follow-ups (out of scope)

- `Decision` pattern for authorization (TODO.md line 41).
- Three-arm `foldAll` on `RequestingUser` (TODO.md line 51).
- Implement `PdoIssuedCommands::pending()` projection (currently empty; comment at `Meteia/Domain/PdoIssuedCommands.php:24-26`) — must now also project attribution.
- Audit-trail read model / queries.
