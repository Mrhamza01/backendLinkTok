<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Story;
use Carbon\Carbon;

class DeleteOldStories extends Command
{
    protected $signature = 'stories:delete-old {minutes}';
    protected $description = 'Delete stories older than a specified number of minutes';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Get the minutes argument
        $minutes = $this->argument('minutes');
        
        // Calculate the cutoff time
        $cutoffTime = Carbon::now()->subMinutes($minutes);

        // Find and delete stories older than the cutoff time
        $deletedRows = Story::where('created_at', '<', $cutoffTime)->delete();

        $this->info("Deleted $deletedRows old stories.");
    }
}
