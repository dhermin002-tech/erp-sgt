<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Commentaire extends Model
{
    use SoftDeletes;

    protected $fillable = ['tache_id', 'user_id', 'contenu', 'photo_path'];

    public function tache()
    {
        return $this->belongsTo(Tache::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
