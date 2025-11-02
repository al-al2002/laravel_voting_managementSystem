<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Vote extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'election_id',
        'candidate_id',
    ];

    /* Relationships */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function election()
    {
        return $this->belongsTo(Election::class, 'election_id');
    }

    public function candidate()
    {
        return $this->belongsTo(Candidate::class, 'candidate_id');
    }

    /* Booted Events */
    protected static function booted()
    {
        // When a vote is deleted, refresh eligibility for the voter
        static::deleted(function ($vote) {
            if ($vote->user) {
                $vote->user->refreshEligibility();
            }
        });
    }
}
