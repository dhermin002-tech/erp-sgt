<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrdreIa extends Model
{
    use SoftDeletes;

    public const CIBLES = ['claude_code', 'codex', 'tout_agent'];

    public const STATUTS = ['a_executer', 'pris', 'fait', 'rejete', 'annule'];

    protected $table = 'ordres_ia';

    protected $fillable = ['tache_id', 'cible', 'projet_dossier', 'consigne', 'emis_par'];

    protected $attributes = ['statut' => 'a_executer'];

    protected $hidden = ['deleted_at'];

    protected function casts(): array
    {
        return ['pris_le' => 'datetime', 'termine_le' => 'datetime'];
    }

    public function tache(): BelongsTo
    {
        return $this->belongsTo(Tache::class);
    }
}
