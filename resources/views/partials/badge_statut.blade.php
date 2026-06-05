@php
$couleurs = [
    'nouveau'    => '#64748B',
    'en_cours'   => '#2563EB',
    'en_attente' => '#C97A0A',
    'en_arret'   => '#B0202E',
    'termine'    => '#15885A',
];
$libelles = [
    'nouveau'    => 'Nouveau',
    'en_cours'   => 'En cours',
    'en_attente' => 'En attente',
    'en_arret'   => 'En arrêt',
    'termine'    => 'Terminé',
];
$couleur = $couleurs[$statut] ?? '#64748B';
$libelle = $libelles[$statut] ?? $statut;
@endphp
<span style="background:{{ $couleur }};color:#fff;font-size:.72rem;font-weight:700;padding:.2rem .6rem;border-radius:999px;white-space:nowrap">{{ $libelle }}</span>
