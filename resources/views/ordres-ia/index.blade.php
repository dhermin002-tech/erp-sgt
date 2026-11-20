@extends('layouts.app')
@section('title', 'Ordres IA')

@include('ordres-ia.styles')

@section('content')
<div class="ordres-ia">
    <h1>Ordres IA</h1>

    <form class="ordres-filtres ordres-carte" method="get" action="{{ route('ordres-ia.index') }}">
        <label>Statut
            <select name="statut">
                <option value="">Tous les statuts</option>
                @foreach(\App\Models\OrdreIa::LIBELLES_STATUTS as $valeur => $libelle)
                    <option value="{{ $valeur }}" @selected(($filtres['statut'] ?? '') === $valeur)>{{ $libelle }}</option>
                @endforeach
            </select>
        </label>
        <label>Cible
            <select name="cible">
                <option value="">Toutes les cibles</option>
                @foreach(\App\Models\OrdreIa::LIBELLES_CIBLES as $valeur => $libelle)
                    <option value="{{ $valeur }}" @selected(($filtres['cible'] ?? '') === $valeur)>{{ $libelle }}</option>
                @endforeach
            </select>
        </label>
        <button class="ordres-bouton" type="submit">Filtrer</button>
    </form>

    <div class="ordres-liste">
        @forelse($ordres as $ordre)
            <article class="ordres-carte">
                <h2><a href="{{ route('ordres-ia.show', $ordre) }}">#{{ $ordre->id }} · {{ $ordre->tache->titre }}</a></h2>
                <p>{{ $ordre->libelleStatut() }} · {{ $ordre->libelleCible() }} · {{ $ordre->projet_dossier }}</p>
                <p>{{ $ordre->emetteur?->nom_complet ?? 'Compte supprimé' }} · {{ $ordre->created_at->format('d/m/Y H:i') }}</p>
                @if($ordre->compte_rendu)
                    <p>{{ \Illuminate\Support\Str::limit($ordre->compte_rendu, 160) }}</p>
                @endif
            </article>
        @empty
            <p class="ordres-carte">Aucun ordre IA pour ces filtres.</p>
        @endforelse
    </div>

    @if($ordres->hasPages())
        <nav class="ordres-pagination" aria-label="Pagination des ordres IA">
            @if($ordres->previousPageUrl())
                <a href="{{ $ordres->previousPageUrl() }}" rel="prev">Page précédente</a>
            @endif
            <span>Page {{ $ordres->currentPage() }} sur {{ $ordres->lastPage() }}</span>
            @if($ordres->nextPageUrl())
                <a href="{{ $ordres->nextPageUrl() }}" rel="next">Page suivante</a>
            @endif
        </nav>
    @endif
</div>
@endsection
