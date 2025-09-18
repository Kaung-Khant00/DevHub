<?php

namespace App\Models;

use App\Models\User;
use App\Models\GroupPost;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupPostLike extends Model
{
    protected $fillable = ['user_id', 'post_id','created_at'];

}
