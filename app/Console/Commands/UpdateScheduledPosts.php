<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Post;
use Carbon\Carbon;

class UpdateScheduledPosts extends Command
{
    protected $signature = 'posts:update-scheduled';
    protected $description = 'Update scheduled posts to be published if their scheduled time has passed';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Get the current time
        $currentTime = Carbon::now();

        // Find all posts where scheduledAt is less than current time and is_scheduled is true
        $posts = Post::where('scheduledAt', '<=', $currentTime)
                     ->where('is_scheduled', true)
                     ->update(['is_scheduled' => false]);

        $this->info('Scheduled posts updated successfully.');
    }
}
