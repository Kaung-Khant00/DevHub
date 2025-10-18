<?php

namespace App\Models;

use App\Models\GroupPost;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class GroupFile extends Model
{
    protected $fillable = ['path', 'size', 'name', 'type'];
    protected $appends = ['file_url'];
    public function getFileUrlAttribute()
    {
        return asset('storage/' . $this->path);
    }
    public function post():hasOne{
        return $this->hasOne(GroupPost::class);
    }
    public $timestamps = false;
}
