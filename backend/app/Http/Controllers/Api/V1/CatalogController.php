<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CatalogRelease;
use App\Models\Character;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function characters(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:80'],
            'element' => ['nullable', 'string', 'max:32'],
            'weapon_type' => ['nullable', 'string', 'max:32'],
            'rarity' => ['nullable', 'integer', 'between:1,5'],
            'per_page' => ['nullable', 'integer', 'between:1,48'],
        ]);

        $release = CatalogRelease::query()
            ->where('is_active', true)
            ->where('status', 'published')
            ->latest('imported_at')
            ->first();

        if (! $release) {
            return response()->json([
                'data' => [],
                'meta' => [
                    'catalogState' => 'not_loaded',
                    'release' => null,
                    'currentPage' => 1,
                    'lastPage' => 1,
                    'perPage' => (int) ($filters['per_page'] ?? 24),
                    'total' => 0,
                ],
            ]);
        }

        $query = Character::query()
            ->with(['element:id,key,name,color', 'weaponType:id,key,name'])
            ->where('catalog_release_id', $release->id);

        if (isset($filters['q'])) {
            $term = '%'.mb_strtolower(trim($filters['q'])).'%';
            $query->where(static function ($nestedQuery) use ($term): void {
                $nestedQuery->whereRaw('LOWER(name) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(game_id) LIKE ?', [$term]);
            });
        }

        if (isset($filters['element'])) {
            $query->whereHas('element', fn ($relation) => $relation->where('key', $filters['element']));
        }

        if (isset($filters['weapon_type'])) {
            $query->whereHas('weaponType', fn ($relation) => $relation->where('key', $filters['weapon_type']));
        }

        if (isset($filters['rarity'])) {
            $query->where('rarity', $filters['rarity']);
        }

        $characters = $query->orderBy('name')->paginate((int) ($filters['per_page'] ?? 24));

        return response()->json([
            'data' => $characters->getCollection()->map(static fn (Character $character): array => [
                'gameId' => $character->game_id,
                'name' => $character->name,
                'rarity' => $character->rarity === null ? null : (int) $character->rarity,
                'iconKey' => $character->icon_key,
                'element' => $character->element ? [
                    'key' => $character->element->key,
                    'name' => $character->element->name,
                    'color' => $character->element->color,
                ] : null,
                'weaponType' => $character->weaponType ? [
                    'key' => $character->weaponType->key,
                    'name' => $character->weaponType->name,
                ] : null,
            ])->values(),
            'meta' => [
                'catalogState' => 'ready',
                'release' => [
                    'source' => $release->source,
                    'sourceVersion' => $release->source_version,
                    'gameVersion' => $release->game_version,
                    'importedAt' => $release->imported_at?->toIso8601String(),
                    'attribution' => $release->attribution,
                ],
                'currentPage' => $characters->currentPage(),
                'lastPage' => $characters->lastPage(),
                'perPage' => $characters->perPage(),
                'total' => $characters->total(),
            ],
        ]);
    }
}
