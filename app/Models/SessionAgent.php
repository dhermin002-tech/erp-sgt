<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SessionAgent extends Model
{
    protected $table = 'sessions_agents';

    protected $fillable = [
        'user_id', 'projet', 'contexte',
        'demarree_a', 'terminee_a', 'statut', 'resume',
    ];

    protected function casts(): array
    {
        return [
            'demarree_a'  => 'datetime',
            'terminee_a'  => 'datetime',
            'resume'      => 'array',
        ];
    }

    public function user() { return $this->belongsTo(User::class); }

    public function getDureeAttribute(): ?string
    {
        if (! $this->terminee_a) {
            return 'En cours';
        }
        $minutes = $this->demarree_a->diffInMinutes($this->terminee_a);
        return $minutes < 60
            ? "{$minutes} min"
            : round($minutes / 60, 1) . ' h';
    }

    public function scopeEnCours($query)
    {
        return $query->where('statut', 'en_cours');
    }
}
