<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Console\Commands\PublishScheduledPosts;
use Illuminate\Support\Facades\Schedule;



class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        $this->commands([
            PublishScheduledPosts::class,
        ]);

        Schedule::command('posts:publish')->everyMinute();
    

    }
}
