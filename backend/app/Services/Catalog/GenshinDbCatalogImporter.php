<?php

namespace App\Services\Catalog;

use App\Models\CatalogRelease;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class GenshinDbCatalogImporter
{
    private const SOURCE = 'genshin-db';

    private const ATTRIBUTION = 'Catalog data from genshin-db by theBowja (MIT); the project attributes GenshinData and the Genshin Impact Wiki as upstream data sources. Genshin Impact names and intellectual property belong to their respective rights holders. Character art is not bundled.';

    public function import(): array
    {
        $config = config('catalog.genshin_db');
        $client = Http::acceptJson()
            ->withUserAgent('BuildLab/0.1 (personal build manager catalog importer)')
            ->withOptions(array_filter(['verify' => $config['ca_bundle'] ?: true]))
            ->timeout($config['timeout_seconds']);

        $characters = $this->fetchCategory($client, $config['api_base'], 'characters');
        $weapons = $this->fetchCategory($client, $config['api_base'], 'weapons');
        $artifacts = $this->fetchCategory($client, $config['api_base'], 'artifacts');

        $normalized = [
            'characters' => $this->normalizeCharacters($characters),
            'weapons' => $this->normalizeWeapons($weapons),
            'artifactSets' => $this->normalizeArtifacts($artifacts),
        ];
        $hash = hash('sha256', json_encode($normalized, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
        $sourceVersion = (string) $config['source_version'];
        $gameVersion = (string) $config['game_version'];

        return DB::transaction(function () use ($normalized, $hash, $sourceVersion, $gameVersion): array {
            $existing = CatalogRelease::query()
                ->where('source', self::SOURCE)
                ->where('source_version', $sourceVersion)
                ->first();

            if ($existing && hash_equals((string) $existing->content_sha256, $hash)) {
                return ['unchanged' => true];
            }

            if ($existing) {
                throw new RuntimeException('The upstream response changed without a source-version change. Review the source and bump GENSHIN_DB_SOURCE_VERSION before importing.');
            }

            $release = CatalogRelease::query()->create([
                'source' => self::SOURCE,
                'source_version' => $sourceVersion,
                'game_version' => $gameVersion,
                'source_revision' => 'api-v5; English; query=names; matchCategories=true; verboseCategories=true',
                'content_sha256' => $hash,
                'status' => 'staging',
                'is_active' => false,
                'attribution' => self::ATTRIBUTION,
            ]);

            $elementIds = $this->upsertLookups('elements', $normalized['characters'], 'element');
            $weaponTypeValues = array_merge(
                array_column($normalized['characters'], 'weaponType'),
                array_column($normalized['weapons'], 'weaponType'),
            );
            $weaponTypeIds = $this->upsertNamedLookups('weapon_types', $weaponTypeValues);
            $statValues = array_column($normalized['weapons'], 'secondaryStat');
            $statValues = array_values(array_filter($statValues));
            $statTypeIds = $this->upsertNamedLookups('stat_types', $statValues);

            foreach ($normalized['characters'] as $character) {
                DB::table('characters')->insert([
                    'catalog_release_id' => $release->id,
                    'game_id' => (string) $character['id'],
                    'name' => $character['name'],
                    'element_id' => $elementIds[$character['element']['key']] ?? null,
                    'weapon_type_id' => $weaponTypeIds[$character['weaponType']['key']] ?? null,
                    'rarity' => $character['rarity'],
                    'icon_key' => $character['iconKey'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ($normalized['weapons'] as $weapon) {
                DB::table('weapons')->insert([
                    'catalog_release_id' => $release->id,
                    'game_id' => (string) $weapon['id'],
                    'name' => $weapon['name'],
                    'weapon_type_id' => $weaponTypeIds[$weapon['weaponType']['key']] ?? null,
                    'rarity' => $weapon['rarity'],
                    'icon_key' => $weapon['iconKey'],
                    'secondary_stat_type_id' => $weapon['secondaryStat'] ? ($statTypeIds[$weapon['secondaryStat']['key']] ?? null) : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ($normalized['artifactSets'] as $artifact) {
                DB::table('artifact_sets')->insert([
                    'catalog_release_id' => $release->id,
                    'game_id' => (string) $artifact['id'],
                    'name' => $artifact['name'],
                    'icon_key' => $artifact['iconKey'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            CatalogRelease::query()->where('is_active', true)->update(['is_active' => false]);
            $release->forceFill([
                'status' => 'published',
                'is_active' => true,
                'imported_at' => now(),
            ])->save();

            return [
                'unchanged' => false,
                'source_version' => $sourceVersion,
                'game_version' => $gameVersion,
                'characters' => count($normalized['characters']),
                'weapons' => count($normalized['weapons']),
                'artifact_sets' => count($normalized['artifactSets']),
            ];
        });
    }

    private function fetchCategory(PendingRequest $client, string $base, string $category): array
    {
        $response = $client->get(rtrim($base, '/').'/'.$category, [
            'query' => 'names',
            'matchCategories' => 'true',
            'verboseCategories' => 'true',
            'resultLanguage' => 'English',
        ])->throw();

        $payload = $response->json();
        if (! is_array($payload) || ! array_is_list($payload) || $payload === []) {
            throw new RuntimeException("The {$category} response was empty or had an unexpected shape.");
        }

        return $payload;
    }

    private function normalizeCharacters(array $rows): array
    {
        return $this->normalizeRows($rows, 'characters', function (array $row): array {
            return [
                'id' => $this->positiveId($row, 'characters'),
                'name' => $this->requiredString($row, 'name', 'characters', 100),
                'rarity' => $this->rarity($row, 'characters'),
                'element' => [
                    'key' => $this->enumKey($this->requiredString($row, 'elementType', 'characters', 40)),
                    'name' => $this->optionalString($row['elementText'] ?? null, 40),
                ],
                'weaponType' => $this->weaponType($row, 'characters'),
                'iconKey' => $this->optionalString($row['images']['filename_icon'] ?? null, 120),
            ];
        });
    }

    private function normalizeWeapons(array $rows): array
    {
        return $this->normalizeRows($rows, 'weapons', function (array $row): array {
            $type = $this->requiredString($row, 'weaponType', 'weapons', 40);
            $statKey = $this->optionalString($row['mainStatType'] ?? null, 48);

            return [
                'id' => $this->positiveId($row, 'weapons'),
                'name' => $this->requiredString($row, 'name', 'weapons', 100),
                'rarity' => $this->rarity($row, 'weapons'),
                'weaponType' => ['key' => $this->weaponTypeKey($type), 'name' => $this->weaponTypeName($type)],
                'iconKey' => $this->optionalString($row['images']['filename_icon'] ?? null, 120),
                'secondaryStat' => $statKey === null ? null : [
                    'key' => $this->enumKey($statKey),
                    'name' => $this->optionalString($row['mainStatText'] ?? $statKey, 80) ?? $statKey,
                ],
            ];
        });
    }

    private function normalizeArtifacts(array $rows): array
    {
        return $this->normalizeRows($rows, 'artifacts', function (array $row): array {
            return [
                'id' => $this->positiveId($row, 'artifacts'),
                'name' => $this->requiredString($row, 'name', 'artifacts', 100),
                'iconKey' => $this->optionalString($row['images']['filename_flower'] ?? null, 120),
            ];
        });
    }

    private function normalizeRows(array $rows, string $category, callable $normalizer): array
    {
        $normalized = [];
        $seen = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                throw new RuntimeException("The {$category} response contains a non-object record.");
            }
            $record = $normalizer($row);
            if (isset($seen[$record['id']])) {
                throw new RuntimeException("The {$category} response contains duplicate ID {$record['id']}.");
            }
            $seen[$record['id']] = true;
            $normalized[] = $record;
        }

        usort($normalized, static fn (array $a, array $b): int => $a['id'] <=> $b['id']);

        return $normalized;
    }

    private function upsertLookups(string $table, array $records, string $field): array
    {
        $values = [];
        foreach ($records as $record) {
            $value = $record[$field];
            $values[$value['key']] = ['key' => $value['key'], 'name' => $value['name'] ?? $value['key']];
        }

        return $this->persistLookups($table, array_values($values));
    }

    private function upsertNamedLookups(string $table, array $values): array
    {
        $unique = [];
        foreach ($values as $value) {
            if ($value) {
                $unique[$value['key']] = $value;
            }
        }

        return $this->persistLookups($table, array_values($unique));
    }

    private function persistLookups(string $table, array $values): array
    {
        foreach ($values as $value) {
            DB::table($table)->updateOrInsert(['key' => $value['key']], [
                'name' => $value['name'],
                'updated_at' => now(),
                'created_at' => now(),
            ]);
        }

        return DB::table($table)->whereIn('key', array_column($values, 'key'))->pluck('id', 'key')->all();
    }

    private function positiveId(array $row, string $category): int
    {
        if (! isset($row['id']) || ! is_numeric($row['id']) || (int) $row['id'] <= 0) {
            throw new RuntimeException("A {$category} record has no valid positive game ID.");
        }

        return (int) $row['id'];
    }

    private function rarity(array $row, string $category): ?int
    {
        $rarity = $row['rarity'] ?? null;
        if ($rarity === null) {
            return null;
        }
        if (! is_numeric($rarity) || (int) $rarity < 1 || (int) $rarity > 5) {
            throw new RuntimeException("A {$category} record has an invalid rarity.");
        }

        return (int) $rarity;
    }

    private function requiredString(array $row, string $field, string $category, int $max): string
    {
        $value = $this->optionalString($row[$field] ?? null, $max);
        if ($value === null) {
            throw new RuntimeException("A {$category} record is missing a valid {$field}.");
        }

        return $value;
    }

    private function optionalString(mixed $value, int $max): ?string
    {
        if (! is_string($value)) {
            return null;
        }
        $value = trim($value);

        return $value !== '' && mb_strlen($value) <= $max ? $value : null;
    }

    private function weaponType(array $row, string $category): array
    {
        $type = $this->requiredString($row, 'weaponType', $category, 40);

        return ['key' => $this->weaponTypeKey($type), 'name' => $this->weaponTypeName($type)];
    }

    private function weaponTypeKey(string $type): string
    {
        $normalized = strtoupper($type);
        $keys = [
            'WEAPON_BOW' => 'bow',
            'WEAPON_CATALYST' => 'catalyst',
            'WEAPON_CLAYMORE' => 'claymore',
            'WEAPON_POLE' => 'polearm',
            'WEAPON_SWORD_ONE_HAND' => 'sword',
            'WEAPON_SWORD' => 'sword',
        ];
        if (! isset($keys[$normalized])) {
            throw new RuntimeException("Unsupported weapon type '{$type}' in source catalog.");
        }

        return $keys[$normalized];
    }

    private function weaponTypeName(string $type): string
    {
        return match ($this->weaponTypeKey($type)) {
            'bow' => 'Bow',
            'catalyst' => 'Catalyst',
            'claymore' => 'Claymore',
            'polearm' => 'Polearm',
            'sword' => 'Sword',
        };
    }

    private function enumKey(string $value): string
    {
        return Str::of($value)->lower()->replaceMatches('/[^a-z0-9]+/', '_')->trim('_')->value();
    }
}
