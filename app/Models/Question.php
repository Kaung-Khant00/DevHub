<?php

namespace App\Models;

use App\Models\User;
use App\Models\Answer;
use DateTimeInterface;
use App\Models\QuestionMessage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
    public function questionMessages(): HasMany
    {
        return $this->hasMany(QuestionMessage::class, 'question_id', 'id');
    }
    public function serializeDate(DateTimeInterface $date){
        return $date->format('d M Y');
    }
    public function isOwner($userId)
{
    return $this->user_id === $userId;
}
protected $appends = ['image_url'];
public function getImageUrlAttribute(){
    return $this->image_path ? asset('storage/' . $this->image_path) : null;
}
}
