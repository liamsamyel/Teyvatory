<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Element extends Model
{
    protected $fillable = ['key', 'name', 'color'];

    public function characters(): HasMany
    {
        return $this->hasMany(Character::class);
    }
}
