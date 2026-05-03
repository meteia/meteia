# PHP Style: Elegant Objects on PHP 8.5

PHP 8.5 features (readonly classes, clone-with, `#[\NoDiscard]`, pipe operator `|>`, URI extension, first-class callables, callable in const expressions) align with Elegant Objects. Use them.

Always: `declare(strict_types=1);` at top of every PHP file.

## Forbidden in domain code

- **null** — no nullable returns from domain interfaces. Use Null Object, polymorphic result object, or throw.
- **getters/setters** — expose behavior, not state. `getX()`/`setX()` are smells. Replace `setX` with `withX(): self`.
- **work in constructors** — constructors assign only (use promoted `readonly` props). Compute lazily in behavior methods.
- **mutable objects** — default to `final readonly class`. Mutation only at infrastructure edges (PDO, streams, caches, HTTP clients).
- **static methods** — wrap in objects. Static named constructors (`fromString`) tolerated at boundaries; prefer parser objects when practical.
- **`instanceof` / type casting** for branching — use polymorphism. `match` only at outer boundary (controller, CLI, deserializer) to convert raw input into polymorphic objects.
- **implementation inheritance** — classes are `final`. Share via composition/decorators, not abstract base classes or traits.
- **public methods without an interface** — every cross-object public method belongs to an interface.
- **ORM/ActiveRecord in domain** — Eloquent/Doctrine models stay in infrastructure. Map to immutable domain objects at the seam.
- **`-er` service names** (`UserValidator`, `InvoiceProcessor`, `ResponseBuilder`) — name by role/value (`ValidUserRegistration`, `PaidInvoice`, `JsonEndpoint`).
- **anemic data objects** — objects do work; they are not bags of fields.

## Required patterns

- `final readonly class` is the default. Drop `final readonly` only when framework proxying forbids it (Eloquent models, controllers Laravel reflects on, etc.).
- Constructor: promoted `readonly` properties, assignment only.
- State transitions return new instances. Use **clone-with**:
  ```php
  return clone($this, ['status' => Status::paid()]);
  ```
- Mark immutable transition methods with `#[\NoDiscard]` so callers can't drop the new instance:
  ```php
  #[\NoDiscard]
  public function with(Item $item): self { ... }
  ```
- Use the **pipe operator** `|>` for small local transformations only — not as a functional pipeline replacing objects.
- Use the **URI extension** (`Uri\Rfc3986\Uri`) instead of raw URL strings inside the domain.
- Decision methods do the work: prefer `grantAccessTo(Resource $r): Decision` over `canAccess(): bool` followed by branching.

## Boundaries

Nullable types, arrays, JSON, raw HTTP requests, ORM rows, scalar config — all permitted **at boundaries**. Convert to domain objects immediately on entry; convert back to scalars/JSON only in adapter/read-model objects (e.g. a dedicated `UserJson` encoder), never in domain objects.

Small scalar-unwrapping value objects (e.g. `PlainPassword::value()` for `password_verify`) are acceptable when crypto/db APIs require the scalar — keep them tiny and single-purpose.

## Tests

One behavioral assertion per test. Push field-level checks into custom PHPUnit constraints (`EqualsMoney`, etc.) rather than asserting on multiple getters.

## Quick checklist before committing PHP

1. `declare(strict_types=1);` present.
2. Class is `final readonly` (or justified exception).
3. No `?T` returns in domain interfaces.
4. No `getX()`/`setX()` on domain objects.
5. No `instanceof` branching outside boundary code.
6. No `extends` of a project abstract class.
7. Every cross-object public method has an interface.
8. State transitions use `clone(...)` and carry `#[\NoDiscard]`.
9. ORM models confined to infrastructure layer.
