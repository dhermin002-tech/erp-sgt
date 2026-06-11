<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

// Modèle pour les rapports de session des agents IA (table rapports_agents)
class RapportAgent extends Model
{
    use SoftDeletes;

    protected $table = 'rapports_agents';

    protected $fillable = [
        'user_id', 'projet', 'type', 'titre',
        'contenu', 'statut', 'fichier_md', 'meta',
    ];

    protected function casts(): array
    {
        return ['meta' => 'array'];
    }

    public function user() { return $this->belongsTo(User::class); }

    public function scopeAujourdhui($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeParAgent($query)
    {
        return $query->whereHas('user', fn($q) => $q->where('type_compte', 'agent_ia'));
    }
}
