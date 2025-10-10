<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionMessageLike extends Model
{
    protected $fillable = ['user_id','question_message_id'];

}
