<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    protected $fillable = ['code', 'name', 'sort_order'];

    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class)->orderBy('id');
    }
}