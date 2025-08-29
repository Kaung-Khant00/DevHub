<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    protected $fillable = ['path', 'size', 'name', 'type'];
    protected $appends = ['file_url'];
    public function getFileUrlAttribute()
    {
        return asset('storage/' . $this->path);
    }
    public $timestamps = false;
}
