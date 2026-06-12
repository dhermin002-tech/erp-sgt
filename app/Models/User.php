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
        'username', 'nom', 'prenom', 'telephone', 'role', 'direction_ui', 'password',
        'type_compte', 'agent_code', 'agent_couleur',
    ];

    /** Ordre hiérarchique des grades humains (du plus haut au plus bas) */
    public const ORDRE_GRADE = [
        'manager'     => 1,
        'developpeur' => 2,
        'technicien'  => 3,
        'agent'       => 4,
        'stagiaire'   => 5,
    ];

    /** Ordre hiérarchique des agents IA par rôle métier */
    public const ORDRE_AGENTS = [
        'le-doyen-kt'     => 1,
        'expert-kt'       => 2,
        'project-agent'   => 3,
        'dev-agent'       => 4,
        'qa-agent'        => 5,
        'design-ui-agent' => 6,
        'audit-agent'     => 7,
    ];

    /** Rang hiérarchique (humain via grade, agent IA via rôle métier) */
    public function rangHierarchique(): int
    {
        if ($this->type_compte === 'agent_ia') {
            return self::ORDRE_AGENTS[$this->agent_code] ?? 99;
        }
        return self::ORDRE_GRADE[$this->role] ?? 99;
    }

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

    public function isManager(): bool    { return $this->role === 'manager'; }
    public function isTechnicien(): bool  { return $this->role === 'technicien'; }
    public function isDeveloppeur(): bool { return $this->role === 'developpeur'; }
    public function isAgentIa(): bool     { return $this->type_compte === 'agent_ia'; }
    public function isHumain(): bool      { return $this->type_compte === 'humain'; }

    public function getBadgeAvatarHtmlAttribute(): string
    {
        if ($this->isAgentIa()) {
            $couleur = $this->agent_couleur ?? '#64748B';
            $initiale = strtoupper(substr($this->agent_code ?? 'A', 0, 2));
            return "<span class=\"agent-badge\" style=\"background:{$couleur}\" title=\"{$this->nom_complet}\">🤖 {$initiale}</span>";
        }
        $initiales = strtoupper(substr($this->prenom ?? '', 0, 1) . substr($this->nom ?? '', 0, 1));
        return "<span class=\"avatar-initiales\" title=\"{$this->nom_complet}\">{$initiales}</span>";
    }

    public function canAccessTache(Tache $tache): bool
    {
        if ($this->isManager()) {
            return true;
        }
        return $tache->createur_id === $this->id
            || $tache->responsables()->where('users.id', $this->id)->exists();
    }

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

    public function rapportsAgents()
    {
        return $this->hasMany(RapportAgent::class);
    }

    public function sessionsAgents()
    {
        return $this->hasMany(SessionAgent::class);
    }

    public function sessionActive(): ?SessionAgent
    {
        return $this->sessionsAgents()->where('statut', 'en_cours')->latest()->first();
    }
}
