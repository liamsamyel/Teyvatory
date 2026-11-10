# BuildLab

BuildLab is a personal Genshin Impact showcase importer and build manager. It does not collect HoYoverse credentials or claim access to a player's complete inventory. Showcase imports, user-authored builds, temporary optimizer candidates, and history snapshots are separate concepts.

## Project status

Phase 2 in progress: responsive app shell, versioned game catalog, and catalog browse API. The browse catalog can be imported locally with the backend Artisan command described in [docs/catalog-data.md](docs/catalog-data.md). See also [docs/architecture.md](docs/architecture.md), [docs/data-model.md](docs/data-model.md), and [docs/provider-enka.md](docs/provider-enka.md).

## Planned structure

- `frontend/` React, TypeScript, Vite, Tailwind, and browser application.
- `backend/` Laravel REST API and domain services.
- `docs/` architecture decisions, provider notes, API/data contracts, and calculation references.

## Local prerequisites

PHP 8.2+ with PDO PostgreSQL enabled, Composer, Node.js 20+, npm, and PostgreSQL. Configure secrets only in backend environment files; never place provider or database credentials in the frontend.

