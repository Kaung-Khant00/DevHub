<?php

namespace App\Models;

use App\Models\File;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    protected $fillable = ['user_id', 'content', 'image', 'file', 'code', 'code_lang','tags','title'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function file():HasOne{
        return $this->hasOne(File::class);
    }
    protected $casts = [
    'tags' => 'array',
];
protected $appends = ['created_at_formatted','image_url'];

public function getCreatedAtFormattedAttribute()
{
    return $this->created_at->diffForHumans();
}
public function getImageUrlAttribute(){
    if($this->image){
        return asset('storage/'.$this->image);
    }
    return null;
}
}
