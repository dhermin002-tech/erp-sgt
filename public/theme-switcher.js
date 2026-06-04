/* ============================================================================
   KayTechnologie — Système de Gestion des Tâches
   theme-switcher.js  ·  Choix multiple de la charte (Direction A / B)
   ----------------------------------------------------------------------------
   - Applique la direction sur <html data-direction="A|B">
   - Mémorise le choix (localStorage) → persistant entre les sessions
   - Expose window.KTTheme.set('A'|'B') et .get()
   - Branche automatiquement tout élément [data-kt-direction] (boutons/radios)

   INTÉGRATION :
     <script src="theme-switcher.js"></script>
     <!-- bouton/segment de choix : -->
     <button data-kt-direction="A">Corporate Sobre</button>
     <button data-kt-direction="B">Opérations Modernes</button>
   ============================================================================ */
(function () {
  var KEY = 'kt-direction';
  var DEFAULT = 'A';
  var VALID = ['A', 'B'];

  function get() {
    var v = null;
    try { v = localStorage.getItem(KEY); } catch (e) {}
    return VALID.indexOf(v) !== -1 ? v : (document.documentElement.dataset.direction || DEFAULT);
  }

  function set(dir) {
    if (VALID.indexOf(dir) === -1) dir = DEFAULT;
    document.documentElement.dataset.direction = dir;
    try { localStorage.setItem(KEY, dir); } catch (e) {}
    syncControls(dir);
    document.dispatchEvent(new CustomEvent('kt:direction-change', { detail: { direction: dir } }));
  }

  // Met à jour l'état visuel des contrôles de choix
  function syncControls(dir) {
    document.querySelectorAll('[data-kt-direction]').forEach(function (el) {
      var active = el.getAttribute('data-kt-direction') === dir;
      el.setAttribute('aria-pressed', active ? 'true' : 'false');
      el.classList.toggle('is-active', active);
      if (el.matches('input[type="radio"]')) el.checked = active;
    });
  }

  // Délégation de clic sur tout élément [data-kt-direction]
  function bind() {
    document.addEventListener('click', function (e) {
      var t = e.target.closest('[data-kt-direction]');
      if (t) set(t.getAttribute('data-kt-direction'));
    });
    document.addEventListener('change', function (e) {
      var t = e.target;
      if (t.matches && t.matches('input[type="radio"][data-kt-direction]') && t.checked) {
        set(t.getAttribute('data-kt-direction'));
      }
    });
  }

  // Application immédiate (évite le flash) puis branchement
  document.documentElement.dataset.direction = get();
  if (document.readyState !== 'loading') { syncControls(get()); bind(); }
  else document.addEventListener('DOMContentLoaded', function () { syncControls(get()); bind(); });

  window.KTTheme = { get: get, set: set, valid: VALID.slice() };
})();
