<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientProfile extends Model
{
    protected $fillable = ['user_id','company_name','address','website','social_link','about'];

    public function user () : BelongsTo {
        return $this->belongsTo(User::class);
    }
}
