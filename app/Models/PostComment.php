<?php

namespace App\Models;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostComment extends Model
{
    protected $fillable = ['user_id', 'post_id', 'comment'];

    public function user(): BelongsTo{
        return $this->belongsTo(User::class);
    }
    public function post(): BelongsTo{
        return $this->belongsTo(Post::class);
    }

    protected $appends = ['created_at_formatted'];

    public function getCreatedAtFormattedAttribute(){
        return $this->created_at->diffForHumans();
    }
}
