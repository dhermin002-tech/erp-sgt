{{-- Partial : une ligne dans la vue Historique & Archives --}}
@php
    $isArchivee = (bool) $tache->archived_at;
    $couleur    = $couleurProjet ?? \App\Models\Tache::couleurProjet($tache->projet);
    $resps      = $tache->responsables->pluck('nom')->implode(', ') ?: '—';
@endphp
<div class="hist-row" style="--proj-color:{{ $couleur }}">
    <div class="hist-row-body">
        <div class="hist-row-title">{{ $tache->titre }}</div>
        <div class="hist-row-meta">
            @if($isArchivee)
                <span class="badge-archive">📦 Archivée</span>
            @else
                <span class="badge-termine">✅ Terminée</span>
            @endif
            @if($tache->projet)
            <span>📂 {{ $tache->projet }}</span>
            @endif
            <span>👤 {{ $resps }}</span>
            <span>📅 {{ $tache->updated_at?->format('d/m/Y') ?? '—' }}</span>
        </div>
    </div>
    <div class="hist-row-actions">
        <a href="{{ route('taches.show', $tache) }}" class="btn btn-sm btn-view">
            <i class="bi bi-eye"></i>
        </a>
        @if($isArchivee && auth()->user()->isManager())
        <form method="POST" action="{{ route('taches.restaurer', $tache) }}" style="display:inline">
            @csrf @method('PATCH')
            <button type="submit" class="btn btn-sm btn-restore" title="Restaurer">
                <i class="bi bi-arrow-counterclockwise"></i>
            </button>
        </form>
        @endif
    </div>
</div>
