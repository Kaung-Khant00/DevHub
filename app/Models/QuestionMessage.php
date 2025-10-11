<?php

namespace App\Models;

use App\Models\Question;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionMessage extends Model
{
        protected $fillable = ['question_id','type','user_id','body'];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class,'question_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function serializeDate(DateTimeInterface $date){
        return $date->format("d M Y \\a\\t h:i A");
    }
}
