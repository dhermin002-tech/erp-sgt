<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SousTache extends Model
{
    protected $fillable = ['tache_id', 'titre', 'termine', 'ordre'];

    protected function casts(): array
    {
        return ['termine' => 'boolean'];
    }

    public function tache()
    {
        return $this->belongsTo(Tache::class);
    }
}
