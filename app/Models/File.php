<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    protected $fillable = [
        "path",'size','name'
    ];

    public $timestamps = false;
}
