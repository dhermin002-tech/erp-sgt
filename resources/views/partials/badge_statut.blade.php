@php
$cles = [
    'nouveau'    => 'todo',
    'en_cours'   => 'progress',
    'en_attente' => 'wait',
    'en_arret'   => 'stop',
    'termine'    => 'done',
];
$libelles = [
    'nouveau'    => 'Nouveau',
    'en_cours'   => 'En cours',
    'en_attente' => 'En attente',
    'en_arret'   => 'En arrêt',
    'termine'    => 'Terminé',
];
$cle = $cles[$statut] ?? 'todo';
$libelle = $libelles[$statut] ?? $statut;
@endphp
<span class="kt-status" data-st="{{ $cle }}">
    <span class="dot"></span>{{ $libelle }}
</span>
