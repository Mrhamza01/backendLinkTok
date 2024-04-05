<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Posts extends Model
{
    protected $fillable = [
        'userId', 'caption', 'media', 'tags', 'location', 'scheduledAt', 'postType', 'is_scheduled'
    ];

    public function publish()
    {
        // Logic to handle the publishing of a post
        $this->is_scheduled = false;
        $this->save();
    }
}
