<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'username',
        'nom',
        'prenom',
        'role',
        'direction_ui',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function getNomCompletAttribute(): string
    {
        return trim("{$this->prenom} {$this->nom}");
    }

    public function isManager(): bool { return $this->role === 'manager'; }
    public function isTechnicien(): bool { return $this->role === 'technicien'; }
    public function isDeveloppeur(): bool { return $this->role === 'developpeur'; }

    public function tachesCreees()
    {
        return $this->hasMany(Tache::class, 'createur_id');
    }

    public function taches()
    {
        return $this->belongsToMany(Tache::class, 'tache_user');
    }

    public function commentaires()
    {
        return $this->hasMany(Commentaire::class);
    }
}
