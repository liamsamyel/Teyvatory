# Game catalog data

## Browse catalog source

BuildLab uses the public `genshin-db` API for the initial character, weapon, and artifact-set browse catalog. Its README documents the API v5 endpoint format, the `names` query with category matching and verbose records, English as the default result language, and warns that its JSON format may change between releases. It also notes the API can lag the main package by one or two days and does not include character stat functions.

The importer is deliberately an explicit backend Artisan command. The app serves imported records from PostgreSQL and does not call the upstream API during page requests. It validates all three categories before opening a database transaction, stores source/game version, source revision, attribution, and a SHA-256 hash of normalized content, then publishes the release atomically. Re-running the same pinned version and content is a no-op. If content differs under the same pinned source version, the import stops; inspect the upstream change and bump `GENSHIN_DB_SOURCE_VERSION` in `backend/.env` only after review.

Current defaults are `genshin-db` package version `5.2.15`, game-data version `7.2`, and API schema `v5`. These values are pinned configuration, not live version discovery. The API can be temporarily behind that game-data version; the returned content hash and actual import time are recorded to make the imported snapshot identifiable. Do not treat this catalog release as a source for stat curves or a complete calculation dataset.

### Import

From `backend/` run:

```powershell
php artisan catalog:import-genshin-db
```

The API must be reachable from the PHP process. No API key is required. For a reviewed source update, edit `GENSHIN_DB_SOURCE_VERSION` and `GENSHIN_DB_GAME_VERSION` in `.env`, then run the command again. Previous releases remain stored; the newest successful import becomes active.

### PHP TLS trust on Windows

Keep TLS certificate verification enabled. If PHP reports `cURL error 60` while PowerShell can reach the same HTTPS endpoint, configure a trusted CA bundle for the command rather than turning verification off. With the standalone PHP install used during development, download the public CA bundle from [curl.se](https://curl.se/ca/cacert.pem) to `C:\Users\liam\Tools\php\extras\ssl\cacert.pem`, then set this local-only value in `backend/.env`:

```dotenv
GENSHIN_DB_CA_BUNDLE=C:/Users/liam/Tools/php/extras/ssl/cacert.pem
```

Restart the terminal or clear Laravel's config cache with `php artisan config:clear`, then retry the import. The same CA setting can be used for future backend integrations that make outbound HTTPS requests. Keep the bundle updated from its publisher. Do not commit the bundle or place it in the frontend.

## Data and attribution

The `genshin-db` GitHub repository identifies its license as MIT and describes its data as sourced from GenshinData and the Genshin Impact Wiki. BuildLab stores the provider attribution with each catalog release. The game and its names, marks, and creative assets remain the property of their respective rights holders; this project does not bundle character artwork. Icon keys are identifiers only and are not themselves image files.

This attribution records the upstream project's stated sources. It is not a claim that all underlying game data or intellectual property is owned by the package author or that the game publisher endorses BuildLab.

## Verified source contract and limitations

The endpoint pattern is:

```text
https://genshin-db-api.vercel.app/api/v5/{characters|weapons|artifacts}?query=names&matchCategories=true&verboseCategories=true&resultLanguage=English
```

Responses were checked for non-empty arrays of objects and key fields (`id`, `name`, rarity, type/element, and icon data where available). The importer rejects missing required identity fields, invalid rarity, duplicate IDs, and unknown weapon-type enums before writing. Optional icon/stat fields remain nullable. Source format changes are expected to require code review.

This first catalog import does not provide ascension curves, full weapon passive definitions, artifact set bonus mechanics, or accurate final character stats. Those require separately verified datasets and calculation rules; no stat calculator should infer them from browse records.

## References

- [genshin-db repository, API usage, versioning, and data notes](https://github.com/theBowja/genshin-db)
- [genshin-db MIT license](https://github.com/theBowja/genshin-db/blob/main/LICENSE)
