<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Answer;
use App\Models\Question;
use App\Models\GroupPost;

use App\Models\JobProposal;
use Laravel\Sanctum\HasApiTokens;
use App\Models\DeveloperConnection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable,HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = ['name', 'email', 'password', 'oauth_provider', 'oauth_id', 'role', 'profile_url', 'phone', 'bio'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = ['password', 'remember_token'];

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

    public function developerProfile()
    {
        return $this->hasOne(DeveloperProfile::class);
    }

    public function clientProfile()
    {
        return $this->hasOne(ClientProfile::class);
    }
    public function groupPosts(): HasMany
    {
        return $this->hasMany(GroupPost::class, 'user_id', 'id');
    }
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'recipient_id', 'id');
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
        return $this->belongsToMany(DeveloperConnection::class, 'developer_connections', 'follower_id', 'following_id');
    }
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(DeveloperConnection::class, 'developer_connections', 'following_id', 'follower_id');
    }
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_members', 'user_id', 'group_id');
    }
    public function developerRatings(): HasMany
    {
        return $this->hasMany(DeveloperRating::class, 'developer_id', 'id');
    }
}
