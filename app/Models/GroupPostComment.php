<?php

namespace App\Models;

use App\Models\User;
use App\Models\GroupPost;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupPostComment extends Model
{
    protected $fillable = ['post_id', 'user_id', 'comment'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(GroupPost::class, 'post_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
