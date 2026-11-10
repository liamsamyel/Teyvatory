<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Weapon extends Model
{
    protected $fillable = [
        'catalog_release_id',
        'game_id',
        'name',
        'weapon_type_id',
        'rarity',
        'icon_key',
        'secondary_stat_type_id',
    ];

    public function catalogRelease(): BelongsTo
    {
        return $this->belongsTo(CatalogRelease::class);
    }

    public function weaponType(): BelongsTo
    {
        return $this->belongsTo(WeaponType::class);
    }
}
