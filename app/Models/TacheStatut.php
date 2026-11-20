<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TacheStatut extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'tache_statuts';

    protected $fillable = ['tache_id', 'acteur_id', 'ancien', 'nouveau'];

    public function acteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acteur_id');
    }
}
