<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Character extends Model
{
    protected $fillable = [
        'catalog_release_id',
        'game_id',
        'name',
        'element_id',
        'weapon_type_id',
        'rarity',
        'icon_key',
    ];

    public function catalogRelease(): BelongsTo
    {
        return $this->belongsTo(CatalogRelease::class);
    }

    public function element(): BelongsTo
    {
        return $this->belongsTo(Element::class);
    }

    public function weaponType(): BelongsTo
    {
        return $this->belongsTo(WeaponType::class);
    }
}
