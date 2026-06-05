@extends('layouts.app')
@section('title',$site->nom)
@section('content')
<h1 style="font-family:var(--font-display);font-size:1.4rem;font-weight:700;color:var(--kt-navy)">{{ $site->nom }} — {{ $site->ville }}</h1>
<p style="color:var(--slate-500);font-size:.875rem;margin-top:.5rem">{{ $site->taches->count() }} tâche(s) associée(s)</p>
@endsection
