<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrdreIa extends Model
{
    use SoftDeletes;

    public const CIBLES = ['claude_code', 'codex', 'tout_agent'];

    public const STATUTS = ['a_executer', 'pris', 'fait', 'rejete', 'annule'];

    public const LIBELLES_CIBLES = ['claude_code' => 'Claude Code', 'codex' => 'Codex', 'tout_agent' => 'Tout agent'];

    public const LIBELLES_STATUTS = ['a_executer' => 'En attente', 'pris' => 'Pris',
        'fait' => 'Terminé', 'rejete' => 'Rejeté', 'annule' => 'Annulé'];

    protected $table = 'ordres_ia';

    protected $fillable = ['tache_id', 'cible', 'projet_dossier', 'consigne', 'emis_par'];

    protected $attributes = ['statut' => 'a_executer'];

    protected $hidden = ['deleted_at'];

    protected function casts(): array
    {
        return ['pris_le' => 'datetime', 'termine_le' => 'datetime'];
    }

    public function scopeFiltrer(Builder $query, array $filtres): Builder
    {
        foreach (['statut' => 'statut', 'cible' => 'cible', 'projet' => 'projet_dossier'] as $filtre => $colonne) {
            if (isset($filtres[$filtre])) {
                $query->where($colonne, $filtres[$filtre]);
            }
        }

        return $query;
    }

    public function emetteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'emis_par');
    }

    public function libelleStatut(): string
    {
        return self::LIBELLES_STATUTS[$this->statut];
    }

    public function libelleCible(): string
    {
        return self::LIBELLES_CIBLES[$this->cible];
    }

    public function tache(): BelongsTo
    {
        return $this->belongsTo(Tache::class);
    }
}
