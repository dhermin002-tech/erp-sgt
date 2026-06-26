<?php

namespace App\Models;

use App\Observers\TacheObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([TacheObserver::class])]
class Tache extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'titre', 'projet', 'description', 'createur_id', 'site_id',
        'date_debut', 'date_echeance', 'statut', 'progression',
        'priorite', 'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'date_debut'    => 'date',
            'date_echeance' => 'date',
            'archived_at'   => 'datetime',
            'progression'   => 'integer',
        ];
    }

    // Statuts considérés comme "terminaux" — extensible sans toucher aux scopes
    const STATUTS_TERMINAUX = ['termine'];

    // Palette de 8 couleurs pour identifier les projets visuellement (crc32 % 8)
    const PROJET_COULEURS = [
        '#003366', '#CC5500', '#8B0000', '#1D4ED8',
        '#16A34A', '#7E22CE', '#B45309', '#0E7490',
    ];

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActives($query)
    {
        return $query->whereNull('archived_at')
                     ->whereNotIn('statut', self::STATUTS_TERMINAUX);
    }

    public function scopeHistorique($query)
    {
        return $query->where(function ($q) {
            $q->whereNotNull('archived_at')
              ->orWhereIn('statut', self::STATUTS_TERMINAUX);
        });
    }

    public function scopeEnRetard($query)
    {
        return $query->where('date_echeance', '<', now()->toDateString())
                     ->where('statut', '!=', 'termine')
                     ->whereNull('archived_at');
    }

    public function scopeVisiblePar($query, User $user)
    {
        if ($user->isManager()) {
            return $query;
        }
        return $query->where(function ($q) use ($user) {
            $q->whereHas('responsables', fn($r) => $r->where('users.id', $user->id))
              ->orWhere('createur_id', $user->id);
        });
    }

    // ── Relations ─────────────────────────────────────────────────────────────

    public function createur()
    {
        return $this->belongsTo(User::class, 'createur_id');
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function responsables()
    {
        return $this->belongsToMany(User::class, 'tache_user');
    }

    public function sousTaches()
    {
        return $this->hasMany(SousTache::class)->orderBy('ordre');
    }

    public function commentaires()
    {
        return $this->hasMany(Commentaire::class)->orderBy('created_at');
    }

    public function rapports()
    {
        return $this->hasMany(Rapport::class)->orderByDesc('created_at');
    }

    public function actionsSuivi()
    {
        return $this->hasMany(ActionSuivi::class)->orderByDesc('created_at');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function recalculerProgression(): void
    {
        $total = $this->sousTaches()->count();
        if ($total === 0) return;
        $terminees = $this->sousTaches()->where('termine', true)->count();
        $this->update(['progression' => (int) round(($terminees / $total) * 100)]);
    }

    public function estEnRetard(): bool
    {
        return $this->date_echeance
            && $this->date_echeance->isPast()
            && $this->statut !== 'termine';
    }

    public static function couleurStatut(string $statut): string
    {
        return match($statut) {
            'nouveau'     => '#64748B',
            'en_cours'    => '#2563EB',
            'en_attente'  => '#C97A0A',
            'en_arret'    => '#B0202E',
            'termine'     => '#15885A',
            default       => '#64748B',
        };
    }

    public static function libelleStatut(string $statut): string
    {
        return match($statut) {
            'nouveau'    => 'Nouveau',
            'en_cours'   => 'En cours',
            'en_attente' => 'En attente',
            'en_arret'   => 'En arrêt',
            'termine'    => 'Terminé',
            default      => $statut,
        };
    }

    public static function couleurProjet(?string $projet): string
    {
        if (! $projet) return '#64748B';
        return self::PROJET_COULEURS[abs(crc32($projet)) % count(self::PROJET_COULEURS)];
    }
}
