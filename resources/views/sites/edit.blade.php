@extends('layouts.app')
@section('title','Modifier site')
@section('content')
<div style="margin-bottom:1rem"><a href="{{ route('sites.index') }}" style="color:var(--slate-500);font-size:.875rem;text-decoration:none">← Sites</a>
<h1 style="font-family:var(--font-display);font-size:1.4rem;font-weight:700;color:var(--kt-navy);margin-top:.5rem">Modifier : {{ $site->nom }}</h1></div>
<div style="background:var(--white);border-radius:12px;border:1px solid var(--slate-200);padding:1.5rem;max-width:500px">
<form method="POST" action="{{ route('sites.update',$site) }}">@csrf @method('PUT')
<div style="margin-bottom:1rem"><label style="display:block;font-size:.85rem;font-weight:600;color:var(--slate-700);margin-bottom:.35rem">Nom *</label>
<input type="text" name="nom" value="{{ old('nom',$site->nom) }}" style="width:100%;padding:.55rem .875rem;border:1.5px solid var(--slate-200);border-radius:8px;font-size:.875rem;box-sizing:border-box"></div>
<div style="margin-bottom:1rem"><label style="display:block;font-size:.85rem;font-weight:600;color:var(--slate-700);margin-bottom:.35rem">Ville *</label>
<input type="text" name="ville" value="{{ old('ville',$site->ville) }}" style="width:100%;padding:.55rem .875rem;border:1.5px solid var(--slate-200);border-radius:8px;font-size:.875rem;box-sizing:border-box"></div>
<button type="submit" style="background:var(--kt-navy);color:#fff;padding:.55rem 1.1rem;border-radius:7px;border:none;font-size:.875rem;font-weight:600;cursor:pointer">Enregistrer</button>
</form></div>
@endsection
