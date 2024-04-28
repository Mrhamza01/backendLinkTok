<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Post;
class UpdatePostStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

   

    /**
     * Create a new job instance.
     *
     * @param int $postId
     */
    public function __construct()
    {
       
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
{
    // Get the current date and time
    $now = Carbon::now();

    // Find all posts with a scheduledAt time less than now
    $posts = Post::where('scheduledAt', '<', $now)->get();

    // Loop through each post and update the is_scheduled field
    foreach ($posts as $post) {
        $post->is_scheduled = false;
        $post->save();
    }
}
}
