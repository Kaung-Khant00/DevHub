<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupMember extends Model
{
    protected $fillable = ['user_id', 'group_id'];
    public $timestamps = false;

}
