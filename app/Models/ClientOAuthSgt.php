<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ClientOAuthSgt extends Model
{
    use HasUuids;

    protected $table = 'oauth_sgt_clients';

    protected $fillable = ['nom', 'redirect_uris'];

    protected function casts(): array
    {
        return ['redirect_uris' => 'array'];
    }
}
