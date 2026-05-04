# Outstanding Elegant-Objects design debt

Each entry is a known violation of CLAUDE.md that we've chosen not to fix
yet. Re-evaluate when the surrounding code is touched. Resolved items
live in `git log`, not here.

## `UniqueId` template-method LEN_\* constants
**Files:** `Meteia/ValueObjects/Identity/UniqueId.php`,
`Meteia/Cryptography/SecretKey.php`.

`UniqueId` reads `static::EPOCH`, `static::LEN_TIMESTAMP`,
`static::LEN_RANDOM`, `static::LEN_ENCODED` via late static binding;
`SecretKey` overrides them to 0/32/43. Strict reading of CLAUDE.md says
no template methods. We've ruled this *parameterisation* (data, not
behavior) and accepted it. Fix when we want truly nominal subtypes:
introduce a parser object `Identifiers::random(string $prefix, IdShape
$shape): UniqueId` so subtypes don't carry shape, and drop
`MyId::random()`/`fromHex()`/`fromToken()` ergonomics.

## Subclasses that add methods — convert to composition
**Files:** `Meteia/Cryptography/Hash.php`,
`Meteia/ValueObjects/Identity/TemporaryFilesystemPath.php`,
`Meteia/ValueObjects/Identity/EmailAddress.php`.

- `Hash` extends `StringLiteral` with 5 encoding methods (`fromBase62`,
  `fromBinary`, `base62`, `base64`, `binary`, `hex`). Convert to
  `final readonly class Hash implements Text` composing a string.
- `TemporaryFilesystemPath` extends `FilesystemPath` with a
  `__destruct` cleanup + static factories. Legitimate entity (lifecycle
  via destructor); convert to composition over `Path` with the
  forwarders we'd need for the call sites.
- `EmailAddress` extends `ValueObject` (not one of our 4 bases) with
  `getAddress`/`getDisplayName` getters — getter rule violation
  separate from the inheritance rule.

## `RequestHandler` mutator entity has no interface
**File:** `Meteia/Http/RequestHandler.php`. `final` now, but
`append`/`prepend` aren't part of any contract. Document as locator
behavior or extract a `Middlewares` interface used by
`MeteiaKernel::requestHandler()` and `MiddlewareList::appendInto()`.

## `EventSourcing` namespace cleanup
**Files:** `Meteia/EventSourcing/Contracts/EventStream.php`,
`Meteia/EventSourcing/PdoEventStream.php`,
`Meteia/EventSourcing/EventMessage.php`,
`Meteia/EventSourcing/EventMetadata.php`.

- Tighten `EventStream` to `append(StreamId, ExpectedVersion,
  RecordedEvent...)` / `read(StreamId, FromVersion = First):
  EventStream`.
- Add `StreamId` (nominal `UniqueId`), `StreamVersion`
  (`IntegerLiteral`), `ExpectedVersion` polymorphic value:
  `Any` / `Empty` / `ExactlyAt(StreamVersion)`.
- Fold `EventMetadata` into a single `RecordedEvent` carrying causation,
  correlation, occurredAt, sequence.
- Add `Meteia\Projections`: `Projection` / `Checkpoint` /
  `CheckpointStore` (+ `PdoCheckpointStore`).
- Make the snapshot policy explicit (current 15ms+25ev threshold is
  opaque magic).

## Wire `Meteia\Application\CommandBus` to AMQP
**Files:** `Meteia/Application/CommandBus.php`,
`Meteia/Commands/CommandOutbox.php`,
`Meteia/AdvancedMessageQueuing/Bunny/`.

- `Meteia\Commands\Command` is the AMQP-published transport marker
  (existing implementers); leave it.
- Add `InProcessCommandBus` (DI-resolved `CommandEndpoint<T>`,
  invoke synchronously).
- Add `AmqpCommandBus` / `OutboxedCommandBus` that serialises
  `Application\Command` to the existing `CommandOutbox`. Receiving
  worker invokes the matching `CommandEndpoint`.
- Decide whether to rename `Meteia\Commands\Command` (e.g. to
  `TransportEnvelope`) so `Application\Command` owns the user-facing
  name.

## Mago lint primitive for nominal subtypes
When mago supports "subclass of X declares no new methods/properties",
add a rule keyed off `StringLiteral` / `FilesystemPath` / `Uri` /
`UniqueId` / `PrimitiveValueObject`. Reject hand-rolled regex/test
hacks until then.
