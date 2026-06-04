<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActionSuivi extends Model
{
    protected $table = 'actions_suivi';

    protected $fillable = ['tache_id', 'user_id', 'description', 'fait'];

    protected function casts(): array
    {
        return ['fait' => 'boolean'];
    }

    public function tache() { return $this->belongsTo(Tache::class); }
    public function user()  { return $this->belongsTo(User::class); }
}
