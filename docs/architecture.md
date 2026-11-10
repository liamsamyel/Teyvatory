# BuildLab architecture

## Recommended application shape

Use a same-origin deployment where practical: a React/Vite single-page client in `frontend/`, a Laravel 12 REST API in `backend/`, and PostgreSQL for durable relational data. Sanctum handles authenticated first-party sessions. Laravel owns authorization, validation, provider access, catalog normalization, import transactions, and canonical calculations. React owns presentation and ephemeral editor state. TanStack Query handles server state; Zustand is reserved for cross-page client preferences or a focused unsaved-workspace state.

Keep domain logic in services/actions with typed request/response resources. Suggested boundaries are `ShowcaseProvider` (external transport), `EnkaShowcaseProvider` (provider-specific adapter), `ShowcaseNormalizer` (validated provider-to-domain mapping), `ImportPreviewService`, `ShowcaseSyncService`, `BuildCalculator`, `BuildSnapshotService`, and `OptimizerSearch`. Provider-specific fields must not leak into user-facing components or persistent domain records except a separately access-controlled raw payload for troubleshooting.

### Trust boundaries

1. Browser submits a UID and explicit server region; backend validates format and region before network access.
2. Provider response is untrusted. Validate envelope and individual identifiers/ranges; tolerate optional/missing fields.
3. Normalized preview is returned without persistence. Only the user's explicit confirm request writes records.
4. All user-owned reads/writes are scoped through authenticated ownership checks. Never accept ownership IDs as authority from the client.

## Main entities and boundaries

- **Catalog**: versioned, shared character/weapon/artifact/stat definitions and progressions.
- **Game account**: an optional user-owned UID reference and region; no game credentials.
- **Imported showcase**: latest normalized provider view, per-character equipment, and synchronization metadata.
- **User build**: editable user-owned build configuration, independent of imported data.
- **Optimizer workspace**: transient candidate inputs; no inventory side effect until an explicit save creates a build.
- **Snapshot**: immutable point-in-time copy for showcase sync or an explicit saved revision.
- **Team**: user-owned composition referencing characters and optionally builds.

The calculator should consume one normalized `BuildInput` contract regardless of whether it originates from a showcase, saved build, snapshot, or optimizer candidate. Build data and calculation implementation live in domain modules, not React. Server calculations are canonical for persisted comparisons and optimizer results; client may preview recalculations using a generated/shared contract, with parity covered later by tests.

## Proposed API surface

- `POST /api/v1/imports/preview` – validate UID/region, fetch/cache, normalize, return selectable character preview and current-vs-proposed changes.
- `POST /api/v1/imports/confirm` – accept preview token plus selected stable character IDs; revalidate token/ownership and transactionally apply import.
- `GET /api/v1/accounts/{account}/showcase` – latest imported view.
- `POST /api/v1/accounts/{account}/synchronizations` – fetch a fresh preview; never mutate immediately.
- `GET|POST /api/v1/characters`, `GET|POST /api/v1/builds`, `GET|PATCH|DELETE /api/v1/builds/{build}` – catalog browsing and owned build CRUD.
- `POST /api/v1/builds/{build}/calculate`, `POST /api/v1/build-comparisons`, `POST /api/v1/optimizer/search` – calculation workflows.
- `GET /api/v1/catalog/...`, `GET|POST /api/v1/teams`, `GET /api/v1/history/...` – read catalog, manage teams/history.

Use versioned JSON resources and stable game IDs. Return field-level validation errors and provider-specific safe error codes, not stack traces.

## Important limitations and decisions

- Enka returns characters in the player's public in-game showcase only, not their full roster or full artifact inventory.
- UID lookup does not prove that the requesting BuildLab user owns the UID. Treat imported data as public third-party data and never imply ownership. If BuildLab later permits private saved account data, require a separate verified ownership flow.
- Showcase fields are not a complete source for character passives, weapon passives, every game rule, or every catalog localization. Initial calculation scope must be explicitly versioned and documented.
- PostgreSQL and a standalone PHP installation must be available for a real backend run. The PHP binary currently on PATH comes from XAMPP; BuildLab's setup guide uses a separate PHP installation with `pdo_pgsql` enabled.

