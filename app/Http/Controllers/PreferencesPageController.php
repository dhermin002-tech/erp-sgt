<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PreferencesPageController extends Controller
{
    public function index()
    {
        return redirect()->route('profil.index', ['#tab-preferences']);
    }
}
