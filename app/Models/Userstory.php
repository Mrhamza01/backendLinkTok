<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Userstory extends Model
{
    use HasFactory;

    protected $table = 'userstorys';

    protected $fillable = [
        'user_id',
        'story_id',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function story()
    {
        return $this->belongsTo(Story::class, 'story_id');
    }
}
