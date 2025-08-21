<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeveloperProfile extends Model
{
    protected $fillable = ['user_id', 'skills','address', 'github_url','linkedin_url', 'portfolio_url'];

    public function user () : BelongsTo {
        return $this->belongsTo(User::class);
    }
}
