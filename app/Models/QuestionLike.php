<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class QuestionLike extends Model
{
    protected $fillable = ['user_id','question_id'];

    public $timestamps = false;

}
