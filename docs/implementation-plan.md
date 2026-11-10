# Implementation plan

## Phase 1 – foundation

- [x] Inspect workspace and available toolchain.
- [x] Define application boundaries, import/sync data flow, ERD, provider behavior, and initial layout.
- [x] Initialize Git and create working frontend/backend application skeletons.
- [x] Configure PostgreSQL environment examples and Laravel Sanctum API scaffolding.
- [x] Add project run instructions and conventions.
- [x] Start PostgreSQL locally, enable PHP's `pdo_pgsql` extension, and confirm Laravel can connect and migrate.

## Prioritized roadmap

1. ~~Complete Phase 1 skeletons and confirm PostgreSQL service/configuration.~~
2. Establish visual shell, routing, reusable layout, accessibility, and responsive behavior.
3. Add versioned game catalog ingestion and read API before building character cards.
4. Implement Enka preview/confirm sync flow with TTL caching, robust normalization, and transactional snapshots.
5. Implement owned build CRUD, then one canonical calculator with documented supported rules.
6. Add comparisons and history based on immutable snapshots.
7. Add temporary candidate optimizer with bounded search and worker execution; save only selected results.
8. Add team presets, then hardening, automated test layers, deployment, and documentation.

## Phase 2 progress

- [x] Add desktop sidebar and responsive mobile navigation across the planned sections.
- [x] Add shared page heading and truthful loading, error, and empty states.
- [x] Add versioned catalog release schema and core character/weapon/artifact lookup tables.
- [x] Add validated, filterable character catalog API and frontend query contract.
- [ ] Verify data-source redistribution terms, pin a source revision, import real catalog records, and complete catalog browsing.

## Coding conventions

- Keep backend provider/domain/calculation rules in services, not controllers.
- Use stable IDs, validated request objects, explicit nullable values, and typed API resources.
- Keep public catalog separate from user-owned/imported entities.
- Build responsive loading, empty, error, and success states in every data flow.
- Tests and CI are planned release requirements; add them when the user authorizes implementation/testing stages.

