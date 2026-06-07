@php
$cles = [
    'urgente' => 'stop',
    'haute'   => 'wait',
    'normale' => 'progress',
    'basse'   => 'todo',
];
$icones = [
    'urgente' => '🔴',
    'haute'   => '🟠',
    'normale' => '🔵',
    'basse'   => '⚪',
];
$cle = $cles[$priorite] ?? 'todo';
@endphp
<span class="kt-status" data-st="{{ $cle }}">
    <span class="dot"></span>{{ ucfirst($priorite) }}
</span>
