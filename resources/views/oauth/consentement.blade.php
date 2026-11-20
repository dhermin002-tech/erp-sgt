@extends('layouts.app')
@section('title', 'Autoriser une application')

@section('content')
<main class="container py-4">
    <div class="card mx-auto shadow-sm" style="max-width: 36rem; border-top: 4px solid #8B0000">
        <div class="card-body p-4">
            <h1 class="h4">Autoriser {{ $client->nom }} à accéder à votre SGT</h1>
            <p>L’application pourra lire les tâches auxquelles votre compte a accès.</p>
            <form method="post" action="{{ route('oauth.autoriser') }}">
                @csrf
                <input type="hidden" name="nonce" value="{{ $demande['nonce'] }}">
                @if($demande['ecriture_disponible'])
                    <div class="form-check mb-3">
                        <input class="form-check-input" id="modifier-taches" type="checkbox" name="modifier" value="1">
                        <label class="form-check-label" for="modifier-taches">Autoriser aussi la modification de vos tâches</label>
                    </div>
                @endif
                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" name="decision" value="accepter" class="btn btn-danger">Autoriser</button>
                    <button type="submit" name="decision" value="refuser" class="btn btn-outline-secondary">Refuser</button>
                </div>
            </form>
        </div>
    </div>
</main>
@endsection
