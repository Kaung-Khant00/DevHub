<?php

namespace App\Models;

use App\Models\User;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = ['user_id','type','data','is_read','message','title'];

    protected $casts = [
        'data'=> 'array',
        'is_read' => 'boolean'
    ];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function serializeDate(DateTimeInterface $date){
        return $date->format('d M Y');
    }

}

