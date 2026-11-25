<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PageParite;
use Illuminate\Http\Request;

class NotificationsApiController extends Controller
{
    public function index(Request $request)
    {
        return PageParite::reponse($request->user()->notifications()->paginate(20), fn ($notification) => [
            'id' => $notification->id,
            'message' => $notification->data['message'] ?? $notification->data['titre'] ?? 'Notification',
            'tache_id' => $notification->data['tache_id'] ?? null,
            'read_at' => $notification->read_at,
            'created_at' => $notification->created_at,
        ]);
    }

    public function lire(Request $request, string $notification)
    {
        $request->user()->notifications()->findOrFail($notification)->markAsRead();

        return response()->noContent();
    }

    public function toutLire(Request $request)
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->noContent();
    }

    public function compteur(Request $request)
    {
        return response()->json(['data' => ['count' => $request->user()->unreadNotifications()->count()]]);
    }
}
