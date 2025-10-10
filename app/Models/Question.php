<?php

namespace App\Models;

use App\Models\User;
use App\Models\Answer;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Question extends Model
{
    protected $fillable = ['title', 'body', 'user_id','is_solved','code_snippet','image_path','is_anonymous','tags'];

    protected $casts = [
        'is_solved' => 'boolean',
        'is_anonymous' => 'boolean',
        'tags' => 'array'
    ];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bestAnswer(): BelongsTo
    {
        return $this->belongsTo(Answer::class, 'best_answer_id');
    }

    public function serializeDate(DateTimeInterface $date){
        return $date->format('d M Y');
    }
}
