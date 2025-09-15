<?php

namespace App\Models;

use App\Models\File;
use App\Models\User;
use App\Models\Group;
use App\Models\DeveloperConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class GroupPost extends Model
{
    protected $fillable = ['group_id', 'user_id','title', 'content', 'image','file_id','code','code_lang','tags','created_at'];

    protected $casts = [
        'tags'=> 'array',
    ];

    protected $appends = ['image_url','created_at_formatted'];

    public function getImageUrlAttribute(){
        if($this->image != null){
            return asset('storage/'.$this->image);
        }
    }
    public function getCreatedAtFormattedAttribute(){
        return $this->created_at->diffForHumans();
    }
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function file()  : BelongsTo{
        return $this->belongsTo(File::class,'file_id');
    }
    public function likedUsers(): BelongsToMany{
        return $this->belongsToMany(User::class, 'group_post_likes', 'post_id', 'user_id');
    }
    public function postFollowers(): HasManyThrough{
        return $this->hasManyThrough(
            DeveloperConnection::class,
            User::class,
            'id',
            'following_id',
            'user_id',
            'id');
    }

    public function toggleGroupPostLike($user_id){
        if($this->likedUsers()->where('user_id',$user_id)->exists()){
            $this->likedUsers()->detach($user_id);
            return false;
        }else{
            $this->likedUsers()->syncWithoutDetaching([$user_id]);
            return true;
        }
    }
}
