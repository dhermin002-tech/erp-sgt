<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    use HasFactory;

    protected $fillable = ['nom', 'ville', 'description', 'actif'];

    public function taches()
    {
        return $this->hasMany(Tache::class);
    }
}
