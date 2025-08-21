<?php

namespace App\Models;

use App\Models\User;
use App\Models\Answer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Question extends Model
{
    protected $fillable = ['title', 'body', 'user_id','is_solved','best_answer_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bestAnswer(): BelongsTo
    {
        return $this->belongsTo(Answer::class, 'best_answer_id');
    }

}
