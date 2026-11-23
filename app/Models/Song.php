<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Song extends Model
{
    protected $fillable = ['playlist_id', 'title', 'artist', 'audio_path', 'sort_order'];

    public function playlist(): BelongsTo
    {
        return $this->belongsTo(Playlist::class);
    }
}