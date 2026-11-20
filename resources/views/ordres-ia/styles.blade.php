@once
@push('styles')
<style>
.ordres-ia { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1rem; width: 100%; max-width: 70rem; }
.ordres-ia h1 { color: var(--kt-navy); }
.ordres-ia h2 { color: var(--kt-navy); font-size: 1.05rem; margin: 0 0 .4rem; }
.ordres-carte { background: var(--white); border: 1px solid var(--slate-200); border-radius: 12px; padding: 1rem; min-width: 0; }
.ordres-carte p { margin: .35rem 0; overflow-wrap: anywhere; }
.ordres-liste { display: grid; grid-template-columns: minmax(0, 1fr); gap: .75rem; }
.ordres-filtres, .ordres-pagination { display: flex; align-items: end; gap: .75rem; flex-wrap: wrap; }
.ordres-filtres label, .ordres-champ { display: grid; gap: .35rem; font-weight: 600; }
.ordres-filtres select, .ordres-champ select, .ordres-champ textarea {
    padding: .65rem; border: 1px solid var(--slate-300); border-radius: 7px;
    background: var(--white); color: var(--kt-navy); min-height: 48px; font: inherit;
}
.ordres-bouton {
    border: 0; border-radius: 7px; min-height: 48px; padding: .65rem 1rem; font: inherit;
    font-weight: 600; background: var(--kt-navy); color: #fff; cursor: pointer;
}
.ordres-bouton:focus-visible, .ordres-filtres select:focus-visible, .ordres-champ :focus-visible {
    outline: 2px solid var(--kt-navy); outline-offset: 3px;
}
.ordres-texte { white-space: pre-wrap; }
.ordres-formulaire { margin-bottom: 1rem; }
.ordres-formulaire summary { cursor: pointer; display: list-item; padding: .7rem 0; color: var(--kt-navy); font-weight: 700; }
.ordres-formulaire form { display: grid; gap: .75rem; }
.ordres-formulaire .ordres-bouton { justify-self: start; }
.ordres-erreur { color: #8B0000; }
@media (max-width: 640px) {
    .ordres-filtres { display: grid; grid-template-columns: minmax(0, 1fr); }
    .ordres-filtres select { width: 100%; min-width: 0; }
    .ordres-filtres .ordres-bouton { justify-self: start; }
}
</style>
@endpush
@endonce
