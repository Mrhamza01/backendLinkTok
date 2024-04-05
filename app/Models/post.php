<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class post extends Model
{
    use HasFactory;
    protected $fillable = [
        'userId', 'caption', 'media', 'tags', 'location', 'scheduledAt', 'postType', 'is_scheduled', 'likes', 'comments', 'shares', 'impressions'
    ];

}
