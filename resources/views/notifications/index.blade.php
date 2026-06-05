@extends('layouts.app')
@section('title', 'Notifications')

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem">
    <h1 style="font-family:var(--font-display);font-size:1.4rem;font-weight:700;color:var(--kt-navy)">Mes notifications</h1>
    <form method="POST" action="{{ route('notifications.tout-lire') }}">
        @csrf @method('PATCH')
        <button type="submit" style="background:var(--kt-navy);color:#fff;padding:.45rem .9rem;border-radius:7px;border:none;font-size:.875rem;font-weight:600;cursor:pointer">
            Tout marquer comme lu
        </button>
    </form>
</div>

<div style="background:var(--white);border-radius:12px;border:1px solid var(--slate-200);overflow:hidden">
    @forelse($notifications as $notif)
    @php $data = $notif->data; $lue = ! is_null($notif->read_at); @endphp
    <div style="display:flex;gap:.75rem;padding:1rem 1.25rem;border-bottom:1px solid var(--slate-100);background:{{ $lue ? '#fff' : 'var(--slate-50)' }};align-items:flex-start">
        <div style="width:36px;height:36px;border-radius:50%;background:{{ ($data['type'] ?? '') === 'assignation' ? 'var(--kt-navy)' : '#2563EB' }};color:#fff;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0">
            {{ ($data['type'] ?? '') === 'assignation' ? '👤' : '🔄' }}
        </div>
        <div style="flex:1">
            <div style="font-size:.875rem;color:var(--slate-700);font-weight:{{ $lue ? '400' : '600' }};line-height:1.5">
                {{ $data['message'] ?? '—' }}
            </div>
            <div style="display:flex;align-items:center;gap:.75rem;margin-top:.35rem">
                <span style="font-size:.75rem;color:var(--slate-400)">{{ $notif->created_at->diffForHumans() }}</span>
                @if(isset($data['url']))
                <a href="{{ $data['url'] }}" style="font-size:.75rem;color:var(--kt-navy);text-decoration:none;font-weight:600">Voir la tâche →</a>
                @endif
                @if(! $lue)
                <span style="background:var(--kt-navy);color:#fff;font-size:.65rem;font-weight:700;padding:.1rem .4rem;border-radius:999px">Nouveau</span>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div style="text-align:center;padding:3rem;color:var(--slate-400)">
        <div style="font-size:2.5rem;margin-bottom:.5rem">🔔</div>
        <div>Aucune notification pour l'instant.</div>
    </div>
    @endforelse
</div>

@if($notifications->hasPages())
<div style="margin-top:1rem">{{ $notifications->links('pagination::bootstrap-4') }}</div>
@endif
@endsection
