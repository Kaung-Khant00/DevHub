<?php

namespace App\Models;

use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobPost extends Model
{
    protected $fillable = ['client_id','contact_email','title','description','requirements','salary','location','status','position'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

}
