# Authentication — Deferred Items

Tracks design choices made and items left for later. Re-evaluate when CQRS /
Event Sourcing land or when a concrete consumer requires the missing piece.

## Design choices made

### `RequestingUser` is polymorphic with templated `pick` / `fold`
Three subtypes: `AnonymousUser`, `SystemUser`, `IdentifiedUser`.
Two interface methods, both templated via PHPDoc generics:
- `pick<T>(T $whenAnonymous, T $whenAuthenticated): T` — eager two-arm choice
- `fold<T>(callable(): T, callable(UserId): T): T` — lazy; auth branch receives
  the principal's UserId

`SystemUser` collapses with `IdentifiedUser` for both methods. `SystemId`
implements `UserId` so the auth branch in `fold` receives a uniform type.
Consumers needing to distinguish system actors should accept `SystemUser`
or `SystemId` directly in their signatures, not branch on flags.

### `UserId` is a domain interface, not a concrete framework class
`UserId` is an interface with `equals(self): bool` and `asString(): string`.
Apps implement with their preferred ID type (UUID, ULID, integer, …).
`SystemId` is the only framework-owned implementation.

### `userId` exposed via accessor on `IdentifiedUser`, not on `RequestingUser`
The base interface intentionally has no `userId()` — anonymous users can't
honor it. Consumers needing a UserId either:
- Use `fold` (most cases), or
- Type-narrow the parameter to `IdentifiedUser` and call `userId()` directly

## Deferred — add when first consumer materializes

### `Attribution` for CQRS / audit
No method on `RequestingUser` yet to project identity onto commands/events.
When CQRS lands, add:
- `Attribution` polymorphic interface (`AnonymousAttribution`, `UserAttribution(UserId)`, `SystemAttribution(SystemId)`)
- `RequestingUser::attribution(): Attribution`
- Behavior on `Attribution` (e.g. `inscribedOn(EventEnvelope): EventEnvelope`)
  — avoid getter/serializer pattern; the attribution stamps the envelope itself

### Authorization Decisions
Do **not** add `canDo(): bool` style methods to `RequestingUser`. Use the
Decision pattern: the resource returns a `Decision` for a given principal:
```php
$resource->grantAccessTo(RequestingUser $user): Decision
```
Each `Decision` subtype's behavior method (`applyTo(action)`,
`requireOr(throw)`, etc.) does the work. Wire into HTTP middleware and
GraphQL field resolvers when the first authorization rule appears.

### Three-arm `fold` (anon / system / identified)
Current `fold` is two-arm; SystemUser collapses with IdentifiedUser. When
a real consumer needs to distinguish (e.g. audit trail formatting that
treats system actors specially), either:
- Add `foldAll<T>(callable(): T, callable(SystemId): T, callable(UserId): T): T`, or
- Have callers `instanceof SystemId` against the UserId from `fold`

### `SystemId` constructor takes raw string
`SystemId` accepts `string $name` directly. If system actors gain richer
identity (key, version, scope), promote the constructor input to a parser
object — don't grow named constructors.

### Removed in this pass
- `Meteia\Authentication\Oauth\AccessToken` — dead code (zero consumers in
  framework). If OAuth integrations return, build them in their own
  bounded context (`Meteia\Oauth`, alongside the existing `Meteia\WebAuthn`),
  not here. Original behavior to preserve: scope checking and JSON
  deserialization with `expires_in` → `DateTimeInterface`.
- `Meteia\Authentication\UserIdentifier` empty marker interface — replaced
  by concrete `UserId` interface with `equals` + `asString` contract.

## Migration notes for downstream apps

Old API → new API:

| Old | New |
|---|---|
| `RequestingUser::isAnonymous(): bool` | `RequestingUser::pick($anon, $auth)` or `fold($anonFn, $authFn)` |
| `RequestingUser::isSystem(): bool` | accept `SystemUser` parameter; or `instanceof SystemUser` at boundary |
| `RequestingUser::userId(): UserIdentifier` | `IdentifiedUser::userId(): UserId` (after type-narrow), or `fold($anonFn, fn(UserId $id) => …)` |
| `implements UserIdentifier` (empty marker) | `implements UserId` (must provide `equals` + `asString`) |

GraphQL `Endpoints` caller migration example (already done in framework):
```php
// before
$set = $ctx->requestingUser()->isAnonymous() ? $args['anon'] : $args['user'];
// after
$set = $ctx->requestingUser()->pick($args['anon'], $args['user']);
```
