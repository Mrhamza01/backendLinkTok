<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;
use App\Models\Post;

class schedulePost implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Retrieve scheduled posts where scheduledAt is in the past
        $scheduledPosts = Post::where('is_scheduled', true)
            ->where('scheduledAt', '<=', Carbon::now())
            ->get();

        foreach ($scheduledPosts as $post) {
            // Update scheduledAt to the current time
            $post->scheduledAt = Carbon::now();
            $post->is_scheduled = false;
            $post->save();
        }
    }
}
