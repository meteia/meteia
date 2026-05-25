# Meteia EventSourcing

This bounded context provides the command-side event-store primitives used by
event-sourced aggregates:

- `EventSourced` aggregates accept replayed domain events and commit pending
  domain events into a unit of work.
- `EventStream` appends, reads, and replays recorded domain events by
  `StreamId`.
- `PdoEventStream` stores events in `domain_events` and opportunistically
  stores aggregate snapshots in `domain_event_snapshots`.
- `EventSourcedRepository<TId, TAggregate>` is the preferred consumer-facing
  adapter for context repositories that only need reconstitution and commit.

The implementation is intentionally command-side only. Queries should read
projections built from events, not aggregates or the event store.

## Current Usage Shape

A consuming bounded context normally keeps its domain repository interface and
wraps this context with a small infrastructure adapter:

```php
final readonly class EventSourcedCounters implements Counters
{
    /** @var EventSourcedRepository<CounterId, Counter> */
    private EventSourcedRepository $repo;

    public function __construct(EventStream $events, UnitOfWork $unitOfWork)
    {
        $this->repo = new EventSourcedRepository(
            $events,
            $unitOfWork,
            Counter::blank(...),
            static fn(CounterId $id) => new UnknownCounter($id),
        );
    }

    public function reconstituted(CounterId $id): Counter
    {
        $counter = $this->repo->reconstituted($id);
        assert($counter instanceof Counter);

        return $counter;
    }

    public function commit(Counter $counter): void
    {
        $this->repo->commit($counter);
    }
}
```

Aggregate classes currently use the `EventSourcing` trait. The trait records
`PendingEvent` instances through `causes()`, immediately applies new events to
the aggregate, and replays historic events through `handleEventMessage()`.
Because replay mutates the target instance, aggregate implementations cannot be
`readonly` today. Treat that as the documented exception to the normal domain
default.

Events should be immutable domain facts and expose stable `EventTypeId` values:

```php
final readonly class CounterOpened implements DomainEvent
{
    public function __construct(
        public CounterId $counterId,
        public UserId $owner,
        public ?string $label,
        public DateTimeImmutable $occurredAt,
    ) {}

    public static function eventTypeId(): EventTypeId
    {
        return EventTypeId::fromHex('...');
    }
}
```

## Version Semantics

`StreamVersion` is used for two closely related ideas:

- the sequence number stored on each recorded event, starting at `0`;
- the next expected stream position used by `ExpectedVersion`.

That means a loaded stream whose latest stored event has sequence `4` has an
observed version of `5`, and the next pending event should also carry
`StreamVersion(5)`. The unit-of-work implementations rely on this convention
when they choose `EmptyStream` for version `0` and `ExactlyAt($firstVersion)`
for later appends.

This works, but the naming is easy to misread. A future cleanup should split
the concepts into separate value objects, for example `EventSequence` and
`ExpectedStreamPosition`.

## Known Gaps

### Append batches are weakly typed

`EventStream::append(StreamId, ExpectedVersion, RecordedEvent...)` accepts any
set of recorded events. The implementation does not prove that every event:

- belongs to the supplied `StreamId`;
- is contiguous from the expected stream position;
- is ordered by sequence;
- has no duplicate sequence inside the batch.

The database uniqueness constraint catches some conflicts late, but only after
the API has accepted an invalid batch shape. Introduce a `RecordedEventBatch`
or `EventsForStream` object that validates stream identity and contiguous
sequence before persistence. Unit-of-work code can then pass one object instead
of loosely grouped arrays.

### Snapshot policy is implicit

`PdoEventStream` snapshots only when replay exceeds both 15 ms and 25 events.
Those thresholds are private constants and cannot be tuned by an application or
tested directly. Extract a `SnapshotPolicy` collaborator with named decisions,
for example `snapshotAfter(ReplayStats $stats): SnapshotDecision`, and inject
it into `PdoEventStream`.

### Snapshots serialize mutable aggregate state

Snapshots currently serialize the aggregate object through `MessageSerializer`.
This couples snapshot validity to aggregate class serialization details and the
source-file hash. It is acceptable as a greenfield shortcut, but a stronger
model would store an explicit aggregate memento:

- the memento is versioned independently of PHP class layout;
- aggregate code owns converting between replay state and memento;
- snapshot invalidation does not depend on hashing source files.

### Projection support lives outside this context

The event-store schema includes a global `domain_events.id`, and comments
refer to projection runners, but this context exposes no global event feed,
checkpoint store, projector contract, or rebuild workflow. If projections are
owned here, add those concepts explicitly. If projections belong elsewhere,
remove stale comments and document the boundary.

### Event evolution is not modeled

Domain events are serialized and unserialized directly. There is no upcaster,
event schema version, event type registry health check, or replay audit. Add an
explicit event evolution boundary before renaming event classes, moving
properties, or changing value-object serialization.

### Test fakes are useful but not first-class

`Fixtures\EmptyEventStream`, `ReplayingEventStream`, and
`CapturingUnitOfWork` are currently under `Fixtures`. Consumers already use
them in tests. If that is intended, promote them into a public testing namespace
with documented behavior and stability. If it is not intended, provide a small
supported fake event stream.

## Likely Bugs

### `EventSourcing::handleCommandMessage()` trims with `strpos()` incorrectly

The method checks:

```php
$trimMethodFrom = strpos($aggName, $eventName);
if ($trimMethodFrom >= 0) {
    ...
}
```

When `strpos()` returns `false`, the comparison still passes because `false`
coerces to `0`. This can derive the wrong method name and fail at reflection
time. If this command-dispatch path remains, compare with `!== false` and add
tests around command names that do not appear in the aggregate class name.

### Direct `PdoEventStream::append()` calls can create invalid streams

With `AnyVersion`, callers can append non-contiguous event sequences or events
whose internal stream does not match the method `StreamId`. The unit-of-work
path usually creates correct events, but the public event-stream API permits
bad streams. The batch value object described above should be treated as a
correctness fix, not only ergonomics.

### Multi-event append is not atomic inside `PdoEventStream`

`PdoEventStream::append()` inserts each event one by one and does not start its
own transaction. Current unit-of-work implementations wrap persistence in a
transaction, but direct callers can partially append a batch if a later insert
fails. Either document that transaction ownership is always outside
`EventStream`, or make appending a batch atomic at this boundary.

### Snapshot table uniqueness hides hash history

`domain_event_snapshots` is unique only by `aggregate_root_id`, while reads
filter by both `aggregate_root_id` and `aggregate_hash`. A new aggregate hash
causes old snapshots to be ignored until replay creates a new one, and
`REPLACE` removes the previous row. That may be the desired policy, but the
schema should make it explicit: unique by stream only means "one current
snapshot per stream", not "one snapshot per aggregate implementation version".

## Simplification and Type-Safety Opportunities

### Replace trait reflection with explicit aggregate plumbing

The `EventSourcing` trait reduces boilerplate, but it hides several
conventions:

- the first constructor parameter must be an `AggregateRootId`;
- a private property with the same name must exist;
- event appliers must be named `apply<EventName>`;
- replay mutates internal trait state.

An explicit base collaborator or small aggregate-owned event recorder would
make failures earlier and reduce reflection. Keep business behavior on the
aggregate; only the event-recording mechanics should move.

### Give repositories a typed reconstitution contract

`EventSourcedRepository` uses PHPDoc generics, but consumers still need runtime
`assert($aggregate instanceof Counter)` because the concrete return type is
`EventSourced`. A typed repository interface per context is still the right
boundary, but the generic adapter could accept a class-string or typed role
object so it can validate and report type mismatches with a domain-specific
exception.

### Promote recorded-event grouping to an object

Both framework unit-of-work implementations and the Metadochi worker
implementation build arrays shaped like:

```php
array<string, array{StreamId, list<RecordedEvent>}>
```

This shape is repeated and easy to misuse. A named object such as
`RecordedEventsByStream` should own grouping, iteration, non-empty guarantees,
and conversion from `PendingEvents` plus message scope metadata.

### Split event identity from serializer mechanics

`RecordedEvent::eventTypeId()` asks the event class for its static id, while
`PdoEventStream` stores both the type id and serialized object. Add an event
catalog or serializer role that can verify every replayable event class has a
stable id and can deserialize old rows. This would also make "list all event
types" and migration checks possible.

### Rename ambiguous roles

Some names are technically accurate but easy to confuse:

- `EmptyStream` is an expected-version policy, not an event stream.
- `AnyVersion` is an expected-version policy.
- `FromFirst` and `FromAfter` are read ranges, not versions.

Consider names under a clearer namespace, for example
`Expected\Empty`, `Expected\Any`, `ReadRange\FromFirst`, and
`ReadRange\After`.

## Near-Term Work Order

1. Fix and test `handleCommandMessage()` if it is still part of the intended
   aggregate API; otherwise remove it from the trait.
2. Introduce a typed event batch/grouping object and use it in unit-of-work
   persistence and outbox recording.
3. Make append atomicity an explicit contract, either by requiring callers to
   provide a transaction boundary or by moving transaction ownership into
   `PdoEventStream`.
4. Split `StreamVersion` into stored event sequence and expected stream
   position concepts.
5. Extract snapshot policy and decide whether snapshots are implementation
   caches or versioned aggregate mementos.
6. Document or implement the projection boundary: global event feed,
   checkpoints, rebuilds, and idempotent projector contract.
