<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Post;
use App\Models\Answer;
use DateTimeInterface;

use App\Models\Question;
use App\Models\GroupPost;
use App\Models\JobProposal;
use Illuminate\Support\Str;
use App\Models\adminProfile;
use Laravel\Sanctum\HasApiTokens;
use App\Models\GroupCreationRequest;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = ['main_career', 'name', 'email', 'password', 'oauth_provider', 'oauth_id', 'role', 'profile_url', 'phone', 'bio','age','gender'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = ['password', 'remember_token', 'oauth_id', 'user_id'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    /*
    |-------------------------------------------------------------------------
    |
    |
    |   ----------   DATABASE RELATIONSHIPS   ----------
    |
    |
    |--------------------------------------------------------------------------
  */

    public function developerProfile()
    {
        return $this->hasOne(DeveloperProfile::class, 'user_id', 'id');
    }

    public function clientProfile()
    {
        return $this->hasOne(ClientProfile::class, 'user_id', 'id');
    }
    public function adminProfile()
    {
        return $this->hasOne(adminProfile::class, 'user_id', 'id');
    }
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'user_id', 'id');
    }
    public function groupPosts(): HasMany
    {
        return $this->hasMany(GroupPost::class, 'user_id', 'id');
    }
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class,'user_id');
    }
    public function groupCreationRequests(): HasMany
    {
        return $this->hasMany(GroupCreationRequest::class);
    }
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class, 'user_id', 'id');
    }
    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class, 'user_id', 'id');
    }
    public function jobProposals(): HasMany
    {
        return $this->hasMany(JobProposal::class, 'user_id', 'id');
    }
    public function followings(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'developer_connections', 'follower_id', 'following_id');
    }
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'developer_connections', 'following_id', 'follower_id');
    }
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_members', 'user_id', 'group_id');
    }
    public function developerRatings(): HasMany
    {
        return $this->hasMany(DeveloperRating::class, 'developer_id', 'id');
    }
    public function likedPosts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_likes');
    }
    /*
    |-------------------------------------------------------------------------
    |
    |
    |                  APPENDED ATTRIBUTES
    |
    |
    |--------------------------------------------------------------------------
  */
    protected $appends = ['profile_image_url', 'posts_count', 'followers_count', 'followings_count', 'groups_count'];

    public function getPostsCountAttribute()
    {
        return $this->posts()->count();
    }

    public function getFollowersCountAttribute()
    {
        return $this->followers()->count();
    }

    public function getFollowingsCountAttribute()
    {
        return $this->followings()->count();
    }

    public function getGroupsCountAttribute()
    {
        return $this->groups()->count();
    }
    protected function serializeDate(DateTimeInterface $date)
    {
        // Example: "27 Aug 2025"
        return $date->format('d M Y');
    }
    public function getProfileImageUrlAttribute()
    {
        /*  I return if the profile link is URL not path */
        if (Str::startsWith($this->profile_url, ['http://', 'https://'])) {
            return $this->profile_url;
        }
        /*  I return the asset URL  */
        return $this->profile_url ? asset('/storage/' . $this->profile_url) : asset('/defaultImages/profileImage.jpg');
    }
    public function toggleFollowingUser($userId){
        if($this->followings()->where('id',$userId)->exists()){
            $this->followings()->detach($userId);
            return false;
        }
        else{
            $this->followings()->syncWithoutDetaching([$userId]);
            return true;
        }
    }
    public function toggleJoinGroup($groupId){

        if($this->groups()->where('group_id',$groupId)->exists()){
            $this->groups()->detach($groupId);
            return false;
        }
        else{
            $this->groups()->syncWithoutDetaching([$groupId]);
            return true;
        }
    }

}
