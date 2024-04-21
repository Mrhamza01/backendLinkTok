<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    use HasFactory;
    protected $fillable = [
        'reported_for', 'reported_by', 'post_id', 'reason', 'location', 'scheduledAt', 'postType', 'is_scheduled', 'likes', 'comments', 'shares', 'impressions'
    ];

}
