<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategorySuggestion extends Model
{
    protected $fillable = ['name', 'description', 'user_id', 'status', 'is_read'];
}
