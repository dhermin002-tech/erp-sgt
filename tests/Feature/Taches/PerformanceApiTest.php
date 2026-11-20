<?php

namespace Tests\Feature\Taches;

use App\Models\Site;
use App\Models\Tache;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PerformanceApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_le_budget_sql_reste_constant_et_les_compteurs_sont_preserves(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);
        Sanctum::actingAs($manager, ['mcp:read']);
        $site = Site::factory()->create();
        $premiere = Tache::factory()->create(['site_id' => $site->id, 'statut' => 'en_cours']);
        $premiere->responsables()->attach($manager);
        $premiere->sousTaches()->create(['titre' => 'Validation', 'termine' => true, 'ordre' => 1]);

        DB::enableQueryLog();
        $this->getJson('/api/v1/taches')->assertOk()
            ->assertJsonPath('meta.par_statut.en_cours', 1)
            ->assertJsonPath('data.0.sous_taches_total', 1)
            ->assertJsonPath('data.0.sous_taches_terminees', 1);
        $initial = count(DB::getQueryLog());
        DB::disableQueryLog();

        Tache::factory()->count(30)->create(['site_id' => $site->id, 'statut' => 'nouveau'])
            ->each(fn (Tache $tache) => $tache->responsables()->attach($manager));

        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->getJson('/api/v1/taches')->assertOk()->assertJsonCount(31, 'data');
        $this->assertSame($initial, count(DB::getQueryLog()));
        $this->assertLessThanOrEqual(5, $initial);
        DB::disableQueryLog();
        DB::flushQueryLog();

        $this->getJson('/api/v1/taches?par_page=10&page=2')->assertOk()
            ->assertJsonCount(10, 'data')->assertJsonPath('meta.total', 31)
            ->assertJsonPath('meta.par_statut.en_cours', 1)
            ->assertJsonPath('meta.par_statut.nouveau', 30)
            ->assertJsonPath('meta.derniere_page', 4);
        $this->getJson('/api/v1/taches?par_page=101')->assertUnprocessable();

        if (getenv('SGT_MESURE_LOCALE') === '1') {
            foreach (['/me', '/taches', '/dashboard/kpis', '/agents'] as $route) {
                Cache::flush();
                $this->getJson('/api/v1'.$route)->assertOk();
                $echantillons = [];
                for ($i = 0; $i < 30; $i++) {
                    $debut = hrtime(true);
                    $this->getJson('/api/v1'.$route)->assertOk();
                    $echantillons[] = (hrtime(true) - $debut) / 1_000_000;
                }
                sort($echantillons);
                fprintf(STDOUT, "%s p50=%.2f ms, p95=%.2f ms\n", $route, $echantillons[14], $echantillons[28]);
            }
        }
    }
}
