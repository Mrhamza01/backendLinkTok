<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Posts;

class PublishScheduledPosts extends Command
{
    protected $signature = 'posts:publish';
    protected $description = 'Publish scheduled posts';

    public function handle()
    {
        Posts::where('is_scheduled', true)
            ->where('scheduledAt', '<=', now())
            ->each(function ($post) {
                $post->publish(); // Ensure you have a publish method in your Posts model
            });
    }
}
