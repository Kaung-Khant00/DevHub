<?php

namespace App\Models;

use App\Models\File;
use App\Models\PostLike;
use App\Models\PostComment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Post extends Model
{
    // ───── Class Properties ─────
    protected $fillable = [
        'user_id',
        'content',
        'image',
        'file_id',
        'code',
        'code_lang',
        'tags',
        'title'
    ];

    protected $casts = [
        'tags' => 'array',
    ];

    protected $appends = [
        'created_at_formatted',
        'image_url',
    ];

    // ───── Relationships ─────
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function file(): HasOne
    {
        return $this->hasOne(File::class, 'id', 'file_id');
    }

    public function likedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, "post_likes");
    }

    public function comments(): HasMany
    {
        return $this->hasMany(PostComment::class);
    }

    // ───── Accessors ─────
    public function getCreatedAtFormattedAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    public function toggleLike($userId){
        if($this->likedUsers()->where('user_id',$userId)->exists()){
            $this->likedUsers()->detach($userId);
            return false;
        }else{
            $this->likedUsers()->syncWithoutDetaching([$userId]);
            return true;
        }
    }
}
