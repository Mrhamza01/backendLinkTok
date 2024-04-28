<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\CheckIsAdmin;
use App\Http\Middleware\ExtractTokenFromCookie;
use Illuminate\Console\Scheduling\Schedule;

use App\Console\Commands\UpdateIsScheduled;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
       
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    
    ->withMiddleware(function (Middleware $middleware) {
        //
        $middleware->append(ExtractTokenFromCookie::class);
        // $middleware->append(CheckIsAdmin::class);
        // $middleware->append(checkIsBlocked::class);

        // $middleware->append(CheckIsAdmin::class);

    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command(UpdateIsScheduled::class)->everyMinute();
    })
    
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
