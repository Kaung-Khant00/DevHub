<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = ['user_id', 'content', 'image', 'file', 'code', 'code_lang','tags','title'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    protected $casts = [
    'tags' => 'array',
];
protected $appends = ['created_at_formatted'];

public function getCreatedAtFormattedAttribute()
{
    return $this->created_at->diffForHumans();

}
}
