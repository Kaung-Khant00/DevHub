<?php

namespace App\Models;

use App\Models\User;
use App\Models\JobPost;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobProposal extends Model
{
    protected $fillable = ['job_post_id','developer_id', 'user_id', 'proposal_text','expected_salary', 'status'];

    public function jobPost(): BelongsTo
    {
        return $this->belongsTo(JobPost::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class,'developer_id');
    }
}
