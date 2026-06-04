<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rapport extends Model
{
    protected $fillable = ['tache_id', 'user_id', 'contenu', 'date_intervention'];

    protected function casts(): array
    {
        return ['date_intervention' => 'date'];
    }

    public function tache() { return $this->belongsTo(Tache::class); }
    public function user()  { return $this->belongsTo(User::class); }
}
