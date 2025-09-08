<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupCreationRequest extends Model
{
    protected $fillable = ['name','user_id','description','image','tags','status'];
    protected $casts = [
        'tags'=> 'array',
    ];
    public function user(): BelongsTo{
        return $this->belongsTo(User::class);
    }

    protected $appends = ['image_url'];

    public function getImageUrlAttribute(){
        return $this->image ? asset('storage/' . $this->image) : null;
    }
}
