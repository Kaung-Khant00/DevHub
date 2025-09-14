<?php

namespace App\Models;

use App\Models\Post;
use App\Models\User;
use DateTimeInterface;
use App\Models\GroupRule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Group extends Model
{
    protected $fillable = ['name', 'description','image', 'user_id', 'tags'];

    protected $casts = [
        'tags' => 'array',
        'rules'=> 'array'
    ];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_members', 'group_id', 'user_id');
    }
    public function rules(): HasMany{
        return $this->hasMany(GroupRule::class);
    }
    public function posts(): HasMany{
        return $this->hasMany(Post::class);
    }

    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        return asset('storage/' . $this->image);
    }
    public function serializeDate(DateTimeInterface $date){
        return $date->format('d M Y');
    }
}
