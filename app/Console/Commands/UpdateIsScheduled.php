<?php
// app/Console/Commands/UpdateIsScheduled.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Post; // Make sure to use your actual model
use Carbon\Carbon;

class UpdateIsScheduled extends Command
{
    protected $signature = 'schedule:update';
    protected $description = 'Updates the is_scheduled field every minute.';

    public function handle()
    {
        Post::where('is_scheduled', true)
            ->where('scheduledAt', '<=', Carbon::now())
            ->update(['is_scheduled' => false]);

        $this->info('is_scheduled field updated.');
    }
}
