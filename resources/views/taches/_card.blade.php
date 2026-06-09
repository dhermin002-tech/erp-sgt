<div class="kt-task-row {{ $isMine ? 'mine' : '' }}" style="--rail-color: {{ $railVar[$tache->priorite] ?? 'var(--slate-300)' }}">
    <span class="rail"></span>
    <div class="content">
        <a href="{{ route('taches.show', $tache) }}" class="task-row-link">
            <div class="task-top">
                <div>
                    <div class="task-titre">{{ $tache->titre }}</div>
                    @if($tache->sousTaches->count() > 0)
                    <div class="task-sub">{{ $tache->sousTaches->where('termine',true)->count() }}/{{ $tache->sousTaches->count() }} sous-tâches terminées</div>
                    @endif
                </div>
                <div class="task-badges">
                    @if($isMine)
                    <span class="badge-mine">👤 Moi</span>
                    @endif
                    @if($tache->estEnRetard())<span class="badge-retard">⚠ En retard</span>@endif
                    @include('partials.badge_statut', ['statut' => $tache->statut])
                    @include('partials.badge_priorite', ['priorite' => $tache->priorite])
                </div>
            </div>

            <div class="task-meta">
                @if($tache->site)
                <span class="meta-item kt-site">📍 {{ $tache->site->nom }}</span>
                @endif
                @if($tache->date_echeance)
                <span class="meta-item echeance {{ $tache->estEnRetard() ? 'late' : '' }}">
                    🗓 {{ $tache->date_echeance->format('d/m/Y') }}
                </span>
                @endif
                <span class="meta-item kt-prog">
                    <span class="track"><span class="fill" style="width:{{ $tache->progression }}%"></span></span>
                    <span class="val">{{ $tache->progression }}%</span>
                </span>
                @if($tache->responsables->count())
                <span class="meta-item avatar-stack">
                    @foreach($tache->responsables->take(4) as $i => $r)
                    <span class="kt-avatar" style="background:{{ $avatarBg[$i % count($avatarBg)] }}" title="{{ $r->prenom }} {{ $r->nom }}">{{ $initiales($r) }}</span>
                    @endforeach
                    @if($tache->responsables->count() > 4)
                    <span class="kt-avatar" style="background:var(--slate-400)">+{{ $tache->responsables->count() - 4 }}</span>
                    @endif
                </span>
                @endif
            </div>
        </a>

        <div class="task-actions">
            <a href="{{ route('taches.show', $tache) }}" class="btn btn-ghost btn-sm">Voir</a>
            <a href="{{ route('taches.edit', $tache) }}" class="btn btn-ghost btn-sm">Éditer</a>
            @if(auth()->user()->isManager())
            <form method="POST" action="{{ route('taches.destroy', $tache) }}" onsubmit="return confirm('Supprimer cette tâche ?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">✕</button>
            </form>
            @endif
        </div>
    </div>
</div>
