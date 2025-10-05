<?php

namespace App\Models;

use App\Models\User;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    protected $fillable = ['reporter_id','reason','status'];

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }
    public function reportable (){
        return $this->morphTo();
    }
    public function serializeDate(DateTimeInterface $date){
        return $date->format('d/m/Y h:i A');
    }

}
