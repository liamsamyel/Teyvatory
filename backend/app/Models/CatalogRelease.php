<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogRelease extends Model
{
    protected $fillable = [
        'source',
        'source_version',
        'game_version',
        'source_revision',
        'content_sha256',
        'status',
        'is_active',
        'attribution',
        'imported_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'imported_at' => 'immutable_datetime',
        ];
    }
}
