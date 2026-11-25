<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationsApiController extends Controller
{
    public function index(Request $request)
    {
        return response()->json($request->user()->notifications()->paginate(20));
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
        return response()->json(['count' => $request->user()->unreadNotifications()->count()]);
    }
}
