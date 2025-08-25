<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShareQuestion extends Model
{
       protected $fillable = ['shared_user_id', 'question_id','message'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shared_user_id');
    }

    public $timestamps = false;

}
