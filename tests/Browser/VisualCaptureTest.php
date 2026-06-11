<?php

namespace Tests\Browser;

use App\Models\Tache;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * Captures visuelles automatiques des pages du SGT KayTech.
 * Se connecte avec le compte dédié "dusk_test" déjà créé dans la base locale erp_sgt
 * (aucune migration ni reset de données — la vraie base locale est utilisée telle quelle).
 * Exécuter : php artisan dusk --filter VisualCaptureTest
 * Screenshots → tests/Browser/screenshots/
 */
class VisualCaptureTest extends DuskTestCase
{
    private string $managerUsername = 'dusk_test';
    private string $managerPassword = 'DuskCapture2026!';

    /**
     * Capture toutes les pages principales en une seule passe.
     * Partager le dossier screenshots/ dans le chat pour que Claude corrige visuellement.
     */
    public function test_capture_toutes_les_pages(): void
    {
        $this->browse(function (Browser $browser) {

            // ── Connexion ────────────────────────────────────────────────────
            $browser->visit('/login')
                    ->waitFor('input[name=username]', 10)
                    ->screenshot('00-login')
                    ->type('username', $this->managerUsername)
                    ->type('password', $this->managerPassword)
                    ->press('button[type=submit]')
                    ->waitForLocation('/dashboard')
                    ->screenshot('01-dashboard');

            // ── Tâches ───────────────────────────────────────────────────────
            $browser->visit('/taches')
                    ->waitForText('Tâches', 5)
                    ->screenshot('02-taches-liste');

            $browser->visit('/taches/create')
                    ->screenshot('03-taches-formulaire');

            $premiereTache = Tache::withoutTrashed()->first();
            if ($premiereTache) {
                $browser->visit('/taches/' . $premiereTache->id)
                        ->pause(800)
                        ->screenshot('04-taches-detail');
            }

            $browser->visit('/taches/archives')
                    ->screenshot('05-taches-archives');

            // ── Membres ──────────────────────────────────────────────────────
            $browser->visit('/membres')
                    ->screenshot('06-membres-liste');

            // ── Sites ────────────────────────────────────────────────────────
            $browser->visit('/sites')
                    ->screenshot('07-sites-liste');
        });
    }

    /**
     * Capture ciblée d'une page spécifique avec bug visuel.
     * Usage : DUSK_PAGE=/taches/1 php artisan dusk --filter test_capture_page_ciblee
     */
    public function test_capture_page_ciblee(): void
    {
        $page = env('DUSK_PAGE', '/dashboard');

        $this->browse(function (Browser $browser) use ($page) {
            $browser->visit('/login')
                    ->pause(2000)
                    ->screenshot('debug-login-page')
                    ->type('username', $this->managerUsername)
                    ->type('password', $this->managerPassword)
                    ->press('button[type=submit]')
                    ->waitForLocation('/dashboard')
                    ->visit($page)
                    ->pause(1000)
                    ->screenshot('cible-' . str_replace(['/', ' '], ['-', '_'], ltrim($page, '/')));
        });
    }

    /**
     * Capture en viewport mobile (iPhone-like) pour valider le header/drawer responsive.
     * Usage : DUSK_PAGE=/dashboard php artisan dusk --filter test_capture_mobile
     */
    public function test_capture_mobile(): void
    {
        $page = env('DUSK_PAGE', '/dashboard');

        $this->browse(function (Browser $browser) use ($page) {
            $browser->resize(390, 844)
                    ->visit('/login')
                    ->waitFor('input[name=username]', 10)
                    ->type('username', $this->managerUsername)
                    ->type('password', $this->managerPassword)
                    ->press('button[type=submit]')
                    ->waitForLocation('/dashboard')
                    ->visit($page)
                    ->pause(800)
                    ->screenshot('mobile-fermee-' . str_replace(['/', ' '], ['-', '_'], ltrim($page, '/')))
                    ->click('.hamburger')
                    ->pause(500)
                    ->screenshot('mobile-drawer-ouvert-' . str_replace(['/', ' '], ['-', '_'], ltrim($page, '/')));
        });
    }
}
