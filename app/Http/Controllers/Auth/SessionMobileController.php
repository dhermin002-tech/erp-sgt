<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class SessionMobileController extends Controller
{
    public function store(Request $request)
    {
        $identifiants = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ]);
        if (! Auth::guard('web')->attemptWhen($identifiants, fn (User $user) => ! $user->isAgentIa())) {
            throw ValidationException::withMessages(['username' => ['Identifiants incorrects.']]);
        }
        $request->session()->regenerate();

        return response()->noContent();
    }

    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->noContent();
    }
}
