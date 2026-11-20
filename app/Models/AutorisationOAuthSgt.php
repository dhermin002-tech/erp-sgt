<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Sanctum\PersonalAccessToken;

class AutorisationOAuthSgt extends Model
{
    protected $table = 'oauth_sgt_autorisations';

    protected $fillable = ['client_id', 'user_id', 'redirect_uri', 'challenge', 'code_hash', 'code_expire_le',
        'abilities', 'expire_le', 'jeton_id'];

    protected $hidden = ['code_hash', 'challenge'];

    protected function casts(): array
    {
        return ['abilities' => 'array', 'expire_le' => 'datetime', 'code_expire_le' => 'datetime'];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(ClientOAuthSgt::class, 'client_id');
    }

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function jeton(): BelongsTo
    {
        return $this->belongsTo(PersonalAccessToken::class, 'jeton_id');
    }
}
