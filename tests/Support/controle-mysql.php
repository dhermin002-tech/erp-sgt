<?php

declare(strict_types=1);

use App\Http\Resources\TacheApiResource;
use App\Models\OrdreIa;
use App\Models\Site;
use App\Models\Tache;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel as Console;
use Illuminate\Contracts\Http\Kernel as Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\Process\Process;

require dirname(__DIR__, 2).'/vendor/autoload.php';

$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
if ($app->configurationIsCached()) {
    fwrite(STDERR, "La recette exige une configuration locale non mise en cache.\n");
    exit(1);
}
$app->loadEnvironmentFrom('.env.recette-sgt-inexistant');
foreach (['APP_ENV' => 'testing', 'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array',
    'DB_CONNECTION' => 'mysql', 'DB_HOST' => '127.0.0.1', 'DB_PORT' => '33384',
    'DB_DATABASE' => 'sgt_cp_s2_test', 'DB_USERNAME' => 'root', 'DB_PASSWORD' => '',
    'DB_URL' => '', 'LOG_CHANNEL' => 'null'] as $nom => $valeur) {
    $_ENV[$nom] = $_SERVER[$nom] = $valeur;
}
$app->make(Console::class)->bootstrap();
$config = $app['config'];
$config->set([
    'app.env' => 'testing',
    'app.debug' => true,
    'app.key' => 'base64:'.base64_encode(str_repeat(chr(0), 32)),
    'cache.default' => 'array',
    'session.driver' => 'array',
    'logging.default' => 'null',
    'database.default' => 'mysql',
    'database.connections.mysql' => [
        'driver' => 'mysql', 'host' => '127.0.0.1', 'port' => 33384,
        'database' => 'sgt_cp_s2_test', 'username' => 'root', 'password' => '',
        'charset' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '', 'strict' => true,
    ],
]);
$http = $app->make(Http::class);
if (($argv[1] ?? '') === 'prise') {
    [, , $agent, $ordre, $barriere, $index] = $argv;
    Sanctum::actingAs(User::findOrFail((int) $agent), ['mcp:write']);
    touch($barriere.'.'.$index);
    $limite = microtime(true) + 60;
    while (! is_file($barriere)) {
        if (microtime(true) > $limite) {
            throw new RuntimeException('Délai de la barrière de concurrence.');
        }
        clearstatcache();
        usleep(1000);
    }
    $requete = Request::create('/api/v1/ordres-ia/'.$ordre.'/prendre', 'POST', ['session_id' => 'recette-'.$index],
        server: ['HTTP_ACCEPT' => 'application/json']);
    $response = $http->handle($requete);
    $http->terminate($requete, $response);
    if ($response->getStatusCode() >= 500) { echo $response->getStatusCode()." ".(json_decode($response->getContent(), true)["message"] ?? "Erreur HTTP"); exit(1); }
    echo $response->getStatusCode();
    exit(0);
}

if (! in_array($argv[1] ?? '', ['concurrence', 'profil'], true)) {
    throw new RuntimeException('Choisir concurrence ou profil.');
}
if ($app->make(Console::class)->call('migrate', ['--force' => true]) !== 0) {
    fwrite(STDERR, "Migration de recette en échec.\n");
    exit(1);
}

if ($argv[1] === 'concurrence') {
    $manager = User::factory()->create(['role' => 'manager']);
    $agents = collect([
        User::factory()->create(['role' => 'agent', 'type_compte' => 'agent_ia', 'agent_code' => 'codex']),
        User::factory()->create(['role' => 'agent', 'type_compte' => 'agent_ia', 'agent_code' => 'dev-agent']),
    ]);
    $tache = Tache::factory()->create(['createur_id' => $manager->id]);
    $tache->responsables()->attach($agents->pluck('id'));
    $ordre = OrdreIa::create(['tache_id' => $tache->id, 'cible' => 'tout_agent', 'projet_dossier' => 'erp.sgt',
        'consigne' => 'Vérifier le rapport local.', 'emis_par' => $manager->id]);

    $barriere = storage_path('framework/sgt-cp-s2-mysql/barriere-'.$ordre->id);
    $processus = $agents->map(fn (User $agent, int $i) => new Process(
        [PHP_BINARY, __FILE__, 'prise', (string) $agent->id, (string) $ordre->id, $barriere, (string) $i],
        timeout: 120,
    ));
    $echec = null;
    try {
        $processus->each(fn (Process $processus) => $processus->start());
        $limite = microtime(true) + 60;
        do {
            clearstatcache();
            if (microtime(true) > $limite) {
                throw new RuntimeException('Les deux requêtes ne sont pas prêtes.');
            }
            usleep(1000);
        } while (! is_file($barriere.'.0') || ! is_file($barriere.'.1'));
        touch($barriere);
        $codes = $processus->map(function (Process $processus): int {
            if ($processus->wait() !== 0) {
                throw new RuntimeException('Processus de recette : '.$processus->getOutput());
            }

            return (int) trim($processus->getOutput());
        })->sort()->values()->all();

        if ($codes !== [200, 409] || $ordre->refresh()->statut !== 'pris' || ! $agents->pluck('id')->contains($ordre->pris_par_user_id)) {
            throw new RuntimeException('Concurrence invalide : '.implode(', ', $codes));
        }
        echo "MySQL : deux processus synchronisés, HTTP 200 et 409.\n";
    } catch (Throwable $erreur) {
        $echec = $erreur;
    } finally {
        $processus->each(fn (Process $processus) => $processus->stop());
        foreach ([$barriere, $barriere.'.0', $barriere.'.1'] as $fichier) {
            if (is_file($fichier)) {
                unlink($fichier);
            }
        }
    }
    if ($echec) {
        fwrite(STDERR, $echec->getMessage().PHP_EOL);
        exit(1);
    }
    exit(0);
}

$manager = User::factory()->create(['role' => 'manager']);
Sanctum::actingAs($manager, ['mcp:read']);
$site = Site::factory()->create();
Tache::factory()->count(101)->create(['site_id' => $site->id, 'createur_id' => $manager->id, 'statut' => 'en_cours'])
    ->each(function (Tache $tache) use ($manager) {
        $tache->responsables()->attach($manager);
        $tache->sousTaches()->create(['titre' => 'Recette', 'termine' => true]);
    });

$chemin = '/api/v1/taches?site_id='.$site->id;
$mesures = $sql = $horsSql = [];
foreach (range(0, 34) as $i) {
    DB::enableQueryLog();
    DB::flushQueryLog();
    $requete = Request::create($chemin, 'GET', server: ['HTTP_ACCEPT' => 'application/json']);
    $debut = hrtime(true);
    $response = $http->handle($requete);
    $total = (hrtime(true) - $debut) / 1000000;
    $http->terminate($requete, $response);
    if ($response->getStatusCode() !== 200) {
        fwrite(STDERR, 'HTTP '.$response->getStatusCode().PHP_EOL);
        exit(1);
    }
    if ($i >= 5) {
        $dureeSql = array_sum(array_column(DB::getQueryLog(), 'time'));
        $mesures[] = $total;
        $sql[] = $dureeSql;
        $horsSql[] = max(0, $total - $dureeSql);
    }
}
foreach (['HTTP' => $mesures, 'SQL' => $sql, 'hors SQL' => $horsSql] as $nom => $serie) {
    sort($serie);
    printf("%s p50=%.2f ms p95=%.2f ms\n", $nom, $serie[14], $serie[28]);
}

$requete = Request::create($chemin, 'GET', server: ['HTTP_ACCEPT' => 'application/json']);
$taches = Tache::where('site_id', $site->id)->with(['site', 'createur', 'responsables'])
    ->withCount(['sousTaches', 'sousTaches as sous_taches_terminees_count' => fn ($q) => $q->where('termine', true)])
    ->limit(50)->get();
$mesures = ['ressource JSON' => [], 'estEnRetard (50 tâches)' => [], 'HTTP sans middleware' => []];
$app->instance('middleware.disable', true);
foreach (range(1, 30) as $i) {
    $debut = hrtime(true);
    response()->json(['data' => TacheApiResource::collection($taches)]);
    $mesures['ressource JSON'][] = (hrtime(true) - $debut) / 1000000;

    $debut = hrtime(true);
    foreach ($taches as $tache) {
        $tache->estEnRetard();
    }
    $mesures['estEnRetard (50 tâches)'][] = (hrtime(true) - $debut) / 1000000;

    $debut = hrtime(true);
    $response = $http->handle($requete);
    $mesures['HTTP sans middleware'][] = (hrtime(true) - $debut) / 1000000;
    $http->terminate($requete, $response);
    if ($response->getStatusCode() !== 200) {
        fwrite(STDERR, "La requête instrumentée a échoué.\n");
        exit(1);
    }
}
foreach ($mesures as $nom => $serie) {
    sort($serie);
    printf("%s p50=%.2f ms p95=%.2f ms\n", $nom, $serie[14], $serie[28]);
}
printf("Charge utile JSON : %d octets.\n", strlen($response->getContent()));
