<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Group extends Model
{
    protected $fillable = ['name', 'description','image', 'user_id', 'tags'];

    protected $casts = [
        'tags' => 'array',
    ];
    public function founder(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_members', 'group_id', 'user_id');
    }
}
