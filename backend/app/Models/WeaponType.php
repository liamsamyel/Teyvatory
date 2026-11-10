<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WeaponType extends Model
{
    protected $fillable = ['key', 'name'];

    public function characters(): HasMany
    {
        return $this->hasMany(Character::class);
    }

    public function weapons(): HasMany
    {
        return $this->hasMany(Weapon::class);
    }
}
