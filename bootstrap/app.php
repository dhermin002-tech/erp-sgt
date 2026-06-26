<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role'              => \App\Http\Middleware\RoleMiddleware::class,
            'not-agent-account' => \App\Http\Middleware\EnsureNotAgentAccount::class,
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\SecureApiHeaders::class,
            \App\Http\Middleware\TrackLastSeen::class,
            // Permet d'invalider les autres sessions après un changement de mot de passe
            \Illuminate\Session\Middleware\AuthenticateSession::class,
        ]);
        $middleware->api(append: [
            \App\Http\Middleware\SecureApiHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->wantsJson(),
        );
    })->create();
