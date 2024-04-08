<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Story extends Model
{
    use HasFactory;

    protected $table = 'storys';

    protected $fillable = ['user_Id', 'media', 'expiresAt'];

    // public function user()
    // {
    //     return $this->belongsTo(User::class, 'user_Id');
    // }
}
