<?php

namespace App\Models;

use DateTimeInterface;
use App\Models\Question;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
    public function likedUsers():BelongsToMany{
        return $this->belongsToMany(User::class,'question_message_likes','question_message_id','user_id')
        ->withPivot('feedback');
    }
    public function likes(): HasMany{
        return $this->hasMany(QuestionMessageLike::class);
    }
    public function serializeDate(DateTimeInterface $date){
        return $date->format("d M Y \\a\\t h:i A");
    }
public function toggleLike($user_id): string
{
    $existing = $this->likedUsers()->where("user_id",$user_id)->first();
    if($existing){
        /*  I check if the user is already disliked the post so I need to change to like message */
        if($existing->pivot->feedback == 0){
            $this->likedUsers()->updateExistingPivot($user_id, ['feedback' => 1]);
            return "liked";
        }else{
            /*  in this case the user is already liked the post so I need to remove it (the user is toggling) */
            $this->likedUsers()->detach($user_id);
            return "removed_liked";
        }
    }else{
        /*  the user didn't like or dislike the message in this case so I need to create like message */
        $this->likedUsers()->syncWithoutDetaching([
            $user_id => ['feedback' => 1],
        ]);
        return "liked";
    }
}
public function toggleDislike($user_id): string{
    $existing = $this->likedUsers()->where("user_id",$user_id)->first();
    if($existing){
        if($existing->pivot->feedback == 1){
            $this->likedUsers()->updateExistingPivot($user_id, ['feedback' => 0]);
            return "disliked";
        }else{
            $this->likedUsers()->detach($user_id);
            return "removed_disliked";
        }
    }else{
        $this->likedUsers()->syncWithoutDetaching([
            $user_id => ['feedback' => 0],
        ]);
        return "disliked";
    }
}
}
