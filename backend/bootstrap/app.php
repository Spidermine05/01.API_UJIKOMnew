<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\IsPetugas;
use App\Http\Middleware\IsPeminjam;
use App\Http\Middleware\IsStaff;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
   // Middleware web
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

    // // Middleware API
    // ->withMiddleware(function (Middleware $middleware) {
    //     $middleware->alias([
    //         'role.admin' => IsAdmin::class,
    //         'role.petugas' => IsPetugas::class,
    //         'role.peminjam' => IsPeminjam::class,
    //         'role.staff' => IsStaff::class,
    //     ]);
    // })
    //     ->withExceptions(function (Exceptions $exceptions): void {
    //     $exceptions->shouldRenderJsonWhen(
    //         fn (\Illuminate\Http\Request $request, \Throwable $e) => $request->is('api/*') || $request->expectsJson()
    //     );
    // })->create();