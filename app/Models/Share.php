<?php

namespace App\Models;

use App\Models\User;
use App\Models\Question;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Share extends Model
{
    protected $fillable = ['shared_user_id', 'question_id','message'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shared_user_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'question_id');
    }
}
