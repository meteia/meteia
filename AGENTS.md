# Meteia Engineering Principles

This project is still greenfield, so replace migrations when we change the meaning or naming of the consumers.

This project uses jj, not git.

The rules below describe how code should be shaped.

Code in this project should be shaped by Domain Driven Design, Event Sourcing, CQRS, and many practical ideas from
Elegant Objects. Treat these as one system, not four independent preferences:

- The domain model owns language and behavior.
- Events preserve what happened.
- Commands and queries stay separate.
- Objects do work, hide state, and avoid procedural coordination.
- Infrastructure adapts the outside world to the domain; it does not define the domain.

When there is tension between convenience and these principles, the code should keep domain behavior explicit, testable,
and protected from framework concerns.

# Domain Driven Design (DDD)

DDD should be the default structure for business code. The language of the school, teacher, student, curriculum, lesson,
enrollment, assessment, and account concepts should lead tables, screens, and endpoints.

## Ubiquitous language

- Names in code should match the domain language used by product decisions.
- Avoid technical names for domain behavior. `EnrollStudent` is better than `CreateStudentCourseRow`.
- A class name should explain its role in the model, not its implementation pattern.
- Do not collapse distinct concepts into generic names like `Data`, `Record`, `Manager`, `Payload`, or `Entity`.
- When language changes, rename the code. Stale names are design debt.

## Bounded contexts

- Keep contexts explicit in namespace and directory structure.
- A model term only means one thing inside a bounded context. If the same word means different things elsewhere, model
  it separately.
- Contexts communicate through commands, events, read models, or explicit adapters, not shared mutable domain objects.
- Do not create shared domain libraries until the same concept has stabilized across contexts.
- Duplication between contexts is acceptable when it protects language and behavior.

## Aggregates

- Aggregates enforce consistency boundaries. One command changes one aggregate unless a process explicitly coordinates
  multiple aggregates.
- Aggregates expose behavior methods, not state editing methods.
- Invariants live inside the aggregate. Handlers and controllers orchestrate; they do not decide.
- Prefer small aggregates with strong invariants over large object graphs.
- Reference other aggregates by identity, not by object reference.
- Aggregate methods record domain events as the result of accepted behavior.

Elegant Objects applies directly here: aggregates are not anemic state bags. They are objects with responsibilities,
names, and guarded behavior.

## Value objects

- Use value objects for meaningful domain concepts: email addresses, handles, course identifiers, school years, names,
  URLs, money, ranges, and policy choices.
- Value objects are immutable and valid at construction.
- Do not pass raw strings, arrays, or integers through domain code when a named concept exists.
- Equality is based on value, not identity.
- A value object may unwrap to a scalar only at a boundary that requires it, such as hashing, database storage, HTTP, or
  JSON.

## Domain services

- Prefer behavior on the object that owns the rule.
- Add a domain service only when the behavior genuinely spans multiple concepts and cannot belong to one object without
  lying.
- Avoid `-er` names. Name the role or outcome: `EligibleEnrollment`, `PublishedLesson`, `PasswordHash`, `SessionCookie`.
- Domain services are still domain objects. They should have interfaces, focused collaborators, and no framework
  dependencies.

## Repositories

- Repositories are collections of aggregates, not generic database helpers.
- Repository interfaces belong to the domain or application boundary; implementations belong to infrastructure.
- Write repositories should load and persist aggregates by identity and expected version.
- Avoid generic `find()`, `save()`, and `getAll()` APIs unless the language is truly generic in the domain.
- Repositories do not expose query use cases. Queries read projections.

## Layers

- Domain: aggregates, value objects, domain events, policies, and domain interfaces.
- Application: command/query handlers, transaction boundaries, authorization coordination, event dispatch, process
  managers.
- Infrastructure: databases, event stores, HTTP clients, framework integration, queues, logging, clock/random adapters.
- User interface: controllers, endpoints, React views, forms, serialization, and presentation behavior.

Dependencies point inward. Domain code must not import framework, database, HTTP, queue, or UI code.

## Testing

- Domain tests describe behavior in domain language.
- Test invariants through behavior, not by inspecting internal state.
- Prefer custom assertions or constraints over getter-heavy tests.
- Use integration tests for infrastructure mappings, projections, and end-to-end flows.
- A regression involving business language should usually add or update a domain test.

# Event Sourcing (ES)

Event-sourced code treats the ordered stream of domain events as the durable source of truth. Aggregate state is derived
by replaying those events; projections and database rows are replaceable views.

## Event design

- Events are facts, named in the past tense: `StudentRegistered`, `CoursePublished`, `LessonScheduled`.
- Events describe meaningful domain occurrences, not technical persistence actions.
- Event names and payloads are part of the domain contract. Keep them stable and intentional.
- Events carry the minimum data needed to replay decisions, build projections, and integrate with other contexts.
- Prefer domain identities and value object concepts over raw persistence details.
- Do not put commands, intentions, or future work into event names.

Events are allowed to be simple immutable fact objects. That is their job. Keep them explicit, named, readonly, and free
of framework serialization concerns.

## Aggregate lifecycle

- Commands load an aggregate stream, rehydrate the aggregate, ask it to perform behavior, then append newly recorded
  events.
- Rehydration applies past events without re-validating the original command.
- New behavior validates current invariants before recording new events.
- Aggregate state is an implementation detail used to make decisions. It is not returned to callers as a read model.
- An aggregate with no events should still be represented by an explicit object, not `null`.

## Appending and concurrency

- Appends use expected stream version checks.
- A concurrency conflict is a domain/application outcome, not a silent retry by default.
- Event streams are append-only. Do not update, delete, or reinterpret old events in place.
- If correction is needed, append a correcting event.
- Event IDs make projectors and subscribers idempotent.

## Event evolution

- Events are long-lived. Design for old events to remain replayable.
- Add optional fields through explicit versioning or upcasting.
- Do not rename or remove event fields casually.
- Keep serialization at infrastructure boundaries. Domain event classes should not know about JSON, database columns, or
  queue payloads.
- Prefer small, boring event schemas over clever payloads that require context to understand.

## Projectors, reactors, and process managers

- Projectors build read models from events. They do not enforce business rules.
- Reactors perform side effects caused by events, such as email, webhooks, or cache invalidation.
- Process managers coordinate long-running workflows across aggregates by observing events and issuing commands.
- All subscribers must be idempotent.
- Rebuildability is a design requirement for projections.

## Consistency model

- The command side is strongly consistent within a single aggregate stream.
- Read models are eventually consistent.
- UI flows must handle pending or stale projection state.
- If a flow needs read-your-write behavior, solve it at the application boundary with acknowledgement, version tracking,
  or projection catch-up. Do not make the domain query projections to compensate.

## Forbidden

- Treating the event store as a query database.
- Emitting CRUD events like `UserUpdated` when the domain fact is more specific.
- Storing framework models, ORM rows, arrays, or JSON blobs as domain events.
- Letting projectors decide business rules.
- Replaying events by calling public command methods.
- Using event sourcing as an excuse to expose aggregate internals.

# Command Query Responsibility Segregation (CQRS)

Writes and reads should use separate models, code paths, and storage shapes. Commands ask the system to change
something. Queries ask a read model what is known.

## Core split

- Command side: accepts intent, validates invariants through aggregates, appends events, returns acknowledgement.
- Query side: reads projections built from events, never touches aggregates, returns shaped data for a specific view.

Commands and queries never share a handler, model, or return type.

## Commands

- Named in the imperative: `RegisterStudent`, `EnrollInCourse`, `PublishLesson`.
- Immutable `final readonly class` in PHP; readonly object types in TypeScript.
- Carry intent and identifiers, not derived data.
- Do not include timestamps, IDs, or metadata the server is responsible for producing.
- Handled by one handler.
- Handler loads the aggregate, calls a behavior method, persists new events, and coordinates side effects through the
  application layer.
- Handler returns `void` or a small typed acknowledgement at the boundary. It does not return read DTOs.
- Validation of business invariants lives in the aggregate. Handler validation is limited to boundary concerns and
  orchestration.

## Queries

- Named for the view or question: `StudentDashboard`, `CourseRoster`, `LessonsForTeacher`.
- Hit read models, not the event store and not aggregates.
- Return dedicated read-model objects, never domain aggregates.
- One query, one shape. Do not generalize prematurely.
- Duplicate fields across views before sharing a model that weakens the read use case.
- Query handlers may optimize for presentation and storage, but they must not invent business rules.

## Projections

- Subscribe to events and update read storage.
- Are idempotent per event ID.
- Own their storage shape.
- Can be dropped and rebuilt from events.
- Stay boring: translate events into rows/documents/search indexes.
- Do not call command handlers or aggregate behavior.

## Boundaries

- HTTP, CLI, queue, and scheduled adapters translate raw input into command or query objects.
- Bus indirection is optional. Direct handler invocation is fine when the call site is already a boundary.
- Never call a query handler from inside a command handler.
- If a command needs information, that information belongs in the command payload, aggregate state, or a
  domain/application policy designed for the command side.
- Interfaces describe roles. Concrete classes describe implementation strategy. Anything inferred from PSR-4 path
  conventions must be implemented by a concrete `Psr...` class. Domain/application ports must not hide path convention
  magic behind generic names. Current command/event conventions are `PsrCommands` for `*/Commands/*.php`,
  `PsrCommandHandlers` for `Context\Commands\Foo` to `Context\CommandHandlers\Foo`, `PsrEvents` for replayable
  `*/Events/*.php` domain events, and `PsrEventSinks` for
  `ReactingContext\EventSinks\SourceContext\EventName\Action`.

## Forbidden

- Sharing a model between read and write.
- Returning aggregates from queries.
- Mutating state inside query handlers.
- Querying projections inside command handlers.
- Generic read-side repositories with `find()`/`getAll()` as the main API.
- Using CQRS names while still moving arrays through procedural services.

# Elegant Objects

Elegant Objects is a design pressure applied across the codebase: objects should be named, responsible, immutable by
default, and behavior-oriented. Avoid code where data is passed through procedural coordinators that inspect it, branch
on it, and mutate it from the outside.

## General rules

- Objects do work. They are not bags of fields.
- Hide state. Expose behavior.
- Prefer composition and decorators over inheritance.
- Prefer polymorphism over `instanceof`, type switches, boolean flags, and nullable branches.
- Use interfaces for cross-object collaboration.
- Keep constructors simple. Construct valid objects; perform work in named behavior methods.
- Keep framework and serialization details at boundaries.
- Name objects by domain role, policy, result, or capability.
- Avoid `Manager`, `Helper`, `Util`, `Processor`, `Handler` inside the domain model. Application handlers are the
  exception because they are architectural adapters.
- Model the smallest domain thing that can honestly own the behavior. If a class cannot be named without a technical
  suffix, the responsibility probably belongs elsewhere.
- Keep interfaces small. Prefer one essential capability plus decorators or smart wrappers over broad interfaces full of
  convenience methods.
- Model behavioral variations with named objects and decorators, not boolean flags, options arrays, or procedural helper
  calls.
- Respect the Law of Demeter: do not expose internals and then make callers keep working on them. Returned objects
  should be real results or collaborators, not disguised fields.

## Construction and composition

- A constructor fully initializes a valid object. Do not use `init()`, setter injection, or two-step construction.
- Constructors assign dependencies and check invariants only. Move substantial work to named behavior methods or
  prestructor objects.
- Avoid builders in domain and application code. If construction needs a builder, split the object into smaller objects
  with direct constructors.
- Compose collaborators explicitly with constructors. Avoid service locators, dependency injection containers,
  singletons, global registries, and static loggers in domain/application behavior.
- Avoid creating replaceable collaborators inside behavior methods. Inject interfaces or compose concrete collaborators
  at the application boundary.
- Use decorators for validation, caching, logging, retries, conditionals, and collection transformations when those
  concerns are not the core object's job.

## PHP

PHP 8.5 features such as readonly classes, clone-with, `#[\NoDiscard]`, the pipe operator, the URI extension,
first-class callables, and callable expressions in constants align with this style. Use them when they clarify the
model.

Always put `declare(strict_types=1);` at the top of every PHP file.

### Forbidden in domain code

- `null` returns from domain interfaces. Use a Null Object, polymorphic result object, or exception.
- Getters and setters. `getX()`/`setX()` expose storage instead of behavior. Replace `setX()` with behavior or
  `withX(): self`.
- Work in constructors beyond assignment and invariant checks.
- Mutable domain objects. Default to `final readonly class`.
- Static methods as behavior buckets. Static named constructors are tolerated only for trivial boundary parsing;
  parser/factory objects are better when behavior grows.
- `instanceof` or type casting for business branching. Use polymorphism.
- Implementation inheritance. Classes are `final`; share behavior through composition.
- Public cross-object methods without an interface.
- ORM/ActiveRecord models in the domain.
- `-er` service names such as `UserValidator`, `InvoiceProcessor`, or `ResponseBuilder`.
- Anemic DTOs in the domain.
- Builders, service locators, singletons, static registries, and public static state.
- Public properties or public static literals used as shared domain data. Encapsulate repeated data with the behavior
  that uses it.
- PHP attributes that inject behavior. Attributes are metadata only; behavior is explicit composition.

### Required patterns

- `final readonly class` should be the default. Drop it only when framework proxying or reflection requires it.
- Constructor parameters should usually be promoted readonly properties.
- State transitions return new instances.
- Use clone-with for immutable transitions:

  ```php
  return clone($this, ['status' => Status::paid()]);
  ```

- Mark immutable transition methods with `#[\NoDiscard]`:

  ```php
  #[\NoDiscard]
  public function with(Item $item): self { ... }
  ```

- Use the pipe operator for small local transformations only. Do not turn object collaboration into a functional
  pipeline.
- Use `Uri\Rfc3986\Uri` instead of raw URL strings inside the domain.
- Decision methods should do the work: prefer `grantAccessTo(Resource $resource): Decision` over `canAccess(): bool`
  followed by caller branching.

### Boundaries

Nullable types, arrays, JSON, raw HTTP requests, ORM rows, scalar config, and framework primitives are permitted at
boundaries. Convert them to domain objects immediately on entry; convert back to scalars or JSON only in adapter and
read-model code.

Small scalar-unwrapping value objects are acceptable when external APIs require scalars. Keep those methods tiny and
single-purpose.

Wrap external APIs, vendor SDKs, protocol clients, loggers, clocks, and randomness behind narrow project interfaces.
Keep vendor types and procedural APIs out of domain/application code, and provide fakes for tests.

When an object must reveal state to the outside world, prefer a printer/output/media object, a dedicated read model, or
a boundary serializer over getters that leak storage layout.

### Tests

- One behavioral assertion per test where practical.
- Each test method owns its data. Avoid shared fixtures, shared mutable state, and class constants that couple tests
  together.
- Push field-level checks into custom PHPUnit constraints such as `EqualsMoney`.
- Test object behavior, not storage layout.
- Domain tests should not need databases, queues, HTTP, or framework bootstrapping.
- Use fakes that implement project interfaces instead of exposing internals or over-mocking behavior.
- Tests assert. Do not leave `echo`, `var_dump`, debug logging, or visual inspection in tests.
- Parse HTML/XML with DOM/XPath assertions instead of matching markup as raw strings.

### PHP domain rules

1. `declare(strict_types=1);` is present.
2. Domain class is `final readonly`, or the exception is justified.
3. Construction is complete in one constructor call; no builder or `init()` path is needed.
4. Domain interfaces do not accept or return nullable values.
5. Domain objects do not expose `getX()`/`setX()`.
6. Business branching does not use `instanceof`.
7. Project abstract classes are avoided in the domain. Traits are acceptable for sharing small, mechanical behavior (
   event recording, rehydration plumbing) when composition would be noisier without changing the design; do not use
   traits to share business behavior.
8. Cross-object public behavior is represented by an interface.
9. Immutable transitions use clone-with and carry `#[\NoDiscard]`.
10. ORM models are confined to infrastructure.
11. Loggers, clocks, randomness, and external clients are constructor-injected interfaces, never static/global access.
12. The name is a domain role, not a technical service label.

## TypeScript / React

Frontend code is part of the product model. It should preserve domain language and object boundaries instead of becoming
a layer of untyped JSON and boolean flags.

### TypeScript

- Use explicit domain and read-model types. Avoid `any`, broad `object`, and untyped records.
- Prefer `readonly` object shapes and `readonly` arrays for values that should not mutate.
- Convert raw API responses at the boundary before passing data through the UI.
- Model alternatives with discriminated unions or polymorphic objects instead of nullable fields and flag combinations.
- Keep domain decisions out of components when they belong to application/domain code.
- Name functions by behavior or question, not technical plumbing.
- Avoid exporting primitive obsession. If a value has a domain meaning, give it a named type or object.

### React

- Components are UI objects with a clear role. Keep names concrete: `CourseRoster`, `StudentDashboard`, `LoginButton`.
- Container components may orchestrate data and commands; leaf components should be presentation-focused.
- Do not duplicate business rules in JSX conditionals. Ask a typed object or view model for the decision.
- Props should be shaped for the component's job, not mirror backend tables.
- Keep state local only when it is truly UI state. Domain state comes from commands, events, and projections.
- Shared UI primitives and helpers should be reused before new ones are introduced.
- Prefer clear, accessible controls with stable layout over decorative complexity.

### Frontend boundaries

- API clients translate transport payloads into typed read models and command inputs.
- Forms collect raw user input, then build command objects.
- Views render read models. They do not mutate domain concepts directly.
- Optimistic UI must still respect eventual consistency and command acknowledgement.

# Final verification

Once all planned edits across files are complete, run these as the last step on the changed/added PHP files:

- `vendor/bin/mago lint <files>`
- `vendor/bin/mago analyze <files>`

Do not run them mid-task. A change to one file frequently depends on edits still pending in another (resolver wiring,
interface drops, schema renames). Running early surfaces false positives that disappear once the rest of the change
lands.
