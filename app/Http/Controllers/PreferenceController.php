<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class PreferenceController extends Controller
{
    public function setLocale(Request $request)
    {
        $locale = $request->get('locale', 'fr');
        abort_unless(in_array($locale, ['fr', 'en']), 400);

        Session::put('locale', $locale);
        Auth::user()?->update(['direction_ui' => Auth::user()->direction_ui]); // touch session

        return back();
    }

    public function setDirection(Request $request)
    {
        $dir = $request->get('direction', 'A');
        abort_unless(in_array($dir, ['A', 'B']), 400);

        Auth::user()->update(['direction_ui' => $dir]);

        return response()->json(['ok' => true, 'direction' => $dir]);
    }
}
