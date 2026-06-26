<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class TrackLastSeen
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            // Mise à jour toutes les 3 min — requête directe pour ne pas altérer l'instance Eloquent
            if (! $user->last_seen_at || now()->diffInMinutes($user->last_seen_at) >= 3) {
                DB::table('users')->where('id', $user->id)->update(['last_seen_at' => now()]);
                $user->last_seen_at = now();
            }
        }

        return $next($request);
    }
}
