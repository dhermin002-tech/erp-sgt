@extends('layouts.app')
@section('title','Sites')
@section('content')
<div style='display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem'>
<h1 style='font-family:var(--font-display);font-size:1.4rem;font-weight:700;color:var(--kt-navy)'>Sites d\''intervention</h1>
<a href='{{ route(''sites.create'') }}' style='background:var(--kt-navy);color:#fff;padding:.5rem 1rem;border-radius:7px;text-decoration:none;font-size:.875rem;font-weight:600'>+ Nouveau site</a>
</div>
<div style='background:var(--white);border-radius:12px;border:1px solid var(--slate-200);overflow:hidden'>
<table style='width:100%;border-collapse:collapse'>
<thead><tr style='background:var(--slate-50)'><th style='padding:.65rem 1rem;text-align:left;font-size:.8rem;font-weight:700;color:var(--slate-600);border-bottom:1px solid var(--slate-200)'>Nom</th><th style='padding:.65rem 1rem;text-align:left;font-size:.8rem;font-weight:700;color:var(--slate-600);border-bottom:1px solid var(--slate-200)'>Ville</th><th style='padding:.65rem 1rem;text-align:left;font-size:.8rem;font-weight:700;color:var(--slate-600);border-bottom:1px solid var(--slate-200)'>Tâches</th><th style='padding:.65rem 1rem;border-bottom:1px solid var(--slate-200)'></th></tr></thead>
<tbody>@foreach(\ as \)<tr style='border-bottom:1px solid var(--slate-100)'><td style='padding:.75rem 1rem;font-weight:600;color:var(--slate-700)'>{{ \->nom }}</td><td style='padding:.75rem 1rem;color:var(--slate-600)'>{{ \->ville }}</td><td style='padding:.75rem 1rem;color:var(--slate-600)'>{{ \->taches_count }}</td><td style='padding:.75rem 1rem'><a href='{{ route(''sites.edit'',\) }}' style='font-size:.8rem;color:var(--kt-navy)'>Modifier</a></td></tr>@endforeach</tbody>
</table>
</div>
@endsection
