<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class adminProfile extends Model
{
    protected $fillable = ['user_id', 'last_login_at','office_image','address','admin_specialty'];
    protected $casts = [
        'permissions' => 'array',
        'last_login_at' => 'datetime',
    ];

    protected $appends = ['office_image_url'];

    protected function getOfficeImageUrlAttribute()
    {
        return $this->office_image ? asset('storage/'.$this->office_image) : null;
    }
}
