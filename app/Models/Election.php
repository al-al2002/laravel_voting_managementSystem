<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\User;

class Election extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'datetime:Y-m-d H:i',
        'end_date'   => 'datetime:Y-m-d H:i',
    ];

    /* Relationships */
    public function candidates()
    {
        return $this->hasMany(Candidate::class);
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    /* Status Checks */
    public function isActive(): bool
    {
        $now = Carbon::now();
        return $this->start_date <= $now && $this->end_date >= $now;
    }

    public function isUpcoming(): bool
    {
        $now = Carbon::now();
        return $this->start_date > $now;
    }

    public function isClosed(): bool
    {
        $now = Carbon::now();
        return $this->end_date < $now;
    }

    /* Winners */
    public function winners()
    {
        if (!$this->isClosed()) return collect();

        $candidates = $this->candidates()->withCount('votes')->get();
        if ($candidates->isEmpty()) return collect();

        $maxVotes = $candidates->max('votes_count');
        return $candidates->where('votes_count', $maxVotes)->values();
    }

    public function getWinnersAttribute()
    {
        return $this->winners();
    }

    public function winner()
    {
        $winners = $this->winners();
        return $winners->count() === 1 ? $winners->first() : null;
    }

    /* Booted Events */
    protected static function booted()
    {
        // When an election is deleted, refresh eligibility of all voters
        static::deleted(function ($election) {
            User::where('role', 'voter')->get()->each->refreshEligibility();
        });
    }
}
