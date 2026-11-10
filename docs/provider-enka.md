# Public showcase provider: Enka.Network

## Verified endpoint and response scope

Enka's published API docs describe `GET https://enka.network/api/uid/{uid}/` for full player info and showcase data. The response contains `playerInfo` and `avatarInfoList`; if `avatarInfoList` is absent, the showcase is closed or contains no displayed characters. `?info` returns player info only and is not useful for character import. The docs list `400` malformed UID, `404` player absent, `424` game maintenance/post-update breakage, `429` rate limit, `500` server error, and `503` service failure.

Documented response structures include player metadata (`nickname`, `worldLevel`, displayed avatar list), character detail (`avatarId`, constellation IDs, `propMap`, `fightPropMap`, skill data, `equipList`, friendship level), weapon/artifact equipment data, and combat-property IDs. Some data is optional. Character/weapon/artifact display names and stat definitions require joining the response to Enka's datamined `store`/`flat` data and localization maps. Do not guess ID mappings or assume all requested fields exist.

## Provider rules and implementation

- Send a descriptive custom `User-Agent` identifying BuildLab and a contact/project URL.
- Enka documents dynamic UID rate limits. Repeated requests may slow and receive `429`.
- Respect each response's `ttl` (seconds until next showcase request). Cache by UID for at least this TTL; block/queue refresh until expiry. Avoid retry loops and UID enumeration.
- Public docs do not state a fixed numeric request quota or a specific attribution license. Treat exact limits as dynamic; show clear source attribution in import views and docs, and re-check provider terms before release.
- Cache raw response only on backend with bounded retention; use a normalized preview token with short expiry. Never expose API configuration or raw internals to the browser.
- Enka is a data transport, not a complete authoritative game-data catalog or a proof of UID ownership. Catalog data needs pinned source/version metadata and a refresh process.

## Import and synchronization data flow

1. Client validates basic UID shape and submits UID + selected region to `POST /imports/preview`; server repeats validation.
2. Provider adapter checks cache/TTL. If refresh is allowed, it performs a bounded-time request with user agent and maps HTTP/service errors to safe application codes.
3. Normalizer validates envelope and each entry; stable avatar/equipment IDs are joined against the pinned catalog. Missing lookup or unsupported fields become explicit nullable/unsupported values, not fabricated names or stats.
4. Preview shows account metadata, found characters, importable fields, and a field-level diff against the account's last confirmed snapshot. Nothing is persisted as current imported data at preview time.
5. Confirmation submits a signed/opaque expiring preview token and selected character IDs. Server rechecks authenticated ownership, token expiry, catalog version and selection, then commits all rows in one transaction.
6. A sync begins at the same preview path. Confirmation creates a new immutable showcase snapshot and updates only imported projection/current pointer. Existing user-authored builds remain unchanged. Changed-content hash prevents meaningless duplicate snapshots.
7. UI reports last successful sync time; failed previews leave the last good snapshot intact.

## Error behavior

Map malformed UID to `invalid_uid`; Enka `404` to `player_not_found`; missing/empty showcase to `showcase_unavailable_or_empty`; `429` to `provider_rate_limited` with retry guidance based on TTL if present; `424` to `provider_game_maintenance`; `500`/`503`, timeout, malformed JSON, and schema drift to a generic retryable provider-unavailable response. Never tell the user that a private showcase is definitively private when the API only says data is absent/unavailable.

## Sources checked 2026-11-09

- [Enka.Network API documentation](https://github.com/EnkaNetwork/API-docs/blob/master/api.md)
- [Enka.Network API documentation repository](https://github.com/EnkaNetwork/API-docs)

