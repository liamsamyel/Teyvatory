<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArtifactSet extends Model
{
    protected $fillable = ['catalog_release_id', 'game_id', 'name', 'icon_key'];

    public function catalogRelease(): BelongsTo
    {
        return $this->belongsTo(CatalogRelease::class);
    }
}
