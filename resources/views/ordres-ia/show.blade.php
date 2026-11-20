@extends('layouts.app')
@section('title', 'Ordre IA #'.$ordre->id)

@include('ordres-ia.styles')

@section('content')
<div class="ordres-ia">
    <a href="{{ route('ordres-ia.index') }}">Retour aux ordres IA</a>
    <h1>Ordre IA #{{ $ordre->id }}</h1>
    <article class="ordres-carte">
        <h2><a href="{{ route('taches.show', $ordre->tache) }}">{{ $ordre->tache->titre }}</a></h2>
        <p>{{ $ordre->libelleStatut() }} · {{ $ordre->libelleCible() }} · {{ $ordre->projet_dossier }}</p>
        <p>Émis par {{ $ordre->emetteur?->nom_complet ?? 'Compte supprimé' }} le {{ $ordre->created_at->format('d/m/Y à H:i') }}</p>
        @if($ordre->pris_par_agent)
            <p>Pris par {{ $ordre->pris_par_agent }} le {{ $ordre->pris_le?->format('d/m/Y à H:i') }}</p>
        @endif
    </article>
    <section class="ordres-carte">
        <h2>Consigne</h2>
        <p class="ordres-texte">{{ $ordre->consigne }}</p>
    </section>
    <section class="ordres-carte">
        <h2>Compte rendu</h2>
        <p class="ordres-texte">{{ $ordre->compte_rendu ?: 'Aucun compte rendu disponible.' }}</p>
    </section>
</div>
@endsection
