@include('ordres-ia.styles')

<details class="ordres-formulaire ordres-carte" @if($errors->getBag('ordreIa')->any()) open @endif>
    <summary>Confier à un agent IA</summary>
    <form method="post" action="{{ route('ordres-ia.store', $tache) }}">
        @csrf
        <label class="ordres-champ">Cible
            <select name="cible" required>
                <option value="">Choisir une cible</option>
                @foreach(\App\Models\OrdreIa::LIBELLES_CIBLES as $valeur => $libelle)
                    <option value="{{ $valeur }}" @selected(old('cible') === $valeur)>{{ $libelle }}</option>
                @endforeach
            </select>
        </label>
        @error('cible', 'ordreIa') <span class="ordres-erreur" role="alert">{{ $message }}</span> @enderror
        <label class="ordres-champ">Dossier projet
            <select name="projet_dossier" required>
                <option value="">Choisir un dossier</option>
                @foreach(config('ordres_ia.projets') as $dossier => $libelle)
                    <option value="{{ $dossier }}" @selected(old('projet_dossier') === $dossier)>{{ $libelle }} ({{ $dossier }})</option>
                @endforeach
            </select>
        </label>
        @error('projet_dossier', 'ordreIa') <span class="ordres-erreur" role="alert">{{ $message }}</span> @enderror
        <label class="ordres-champ">Consigne
            <textarea name="consigne" rows="5" maxlength="10000" required>{{ old('consigne') }}</textarea>
        </label>
        @error('consigne', 'ordreIa') <span class="ordres-erreur" role="alert">{{ $message }}</span> @enderror
        <button class="ordres-bouton" type="submit">Créer l’ordre</button>
    </form>
</details>
