<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Auth::user()->notifications()->paginate(20);
        Auth::user()->unreadNotifications->markAsRead();
        return view('notifications.index', compact('notifications'));
    }

    public function marquerLue(string $id)
    {
        $notif = Auth::user()->notifications()->findOrFail($id);
        $notif->markAsRead();
        return response()->json(['ok' => true]);
    }

    public function toutLire(Request $request)
    {
        Auth::user()->unreadNotifications->markAsRead();

        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }
        return back()->with('success', 'Toutes les notifications marquées comme lues.');
    }

    public function count()
    {
        return response()->json(['count' => Auth::user()->unreadNotifications()->count()]);
    }
}
