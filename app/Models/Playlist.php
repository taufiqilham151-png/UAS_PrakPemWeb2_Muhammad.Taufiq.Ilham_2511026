<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Playlist extends Model
{
    protected $fillable = ['name', 'description', 'cover_path'];

    public function songs(): HasMany
    {
        return $this->hasMany(Song::class)->orderBy('sort_order')->orderBy('id');
    }
}
