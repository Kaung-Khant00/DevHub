<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeveloperConnection extends Model
{
    protected $fillable = ['follower_id', 'following_id'];

}
