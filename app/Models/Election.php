<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Admin;

class Election extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'status',
        'created_by_admin_id',
        'updated_by_admin_id',
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

    public function createdBy()
    {
        return $this->belongsTo(Admin::class, 'created_by_admin_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(Admin::class, 'updated_by_admin_id');
    }

    /* Status Checks */
    public function isActive(): bool
    {
        $now = Carbon::now();
        $startDate = Carbon::parse($this->start_date);
        $endDate = Carbon::parse($this->end_date);
        return $startDate->lte($now) && $endDate->gte($now);
    }

    public function isUpcoming(): bool
    {
        $now = Carbon::now();
        $startDate = Carbon::parse($this->start_date);
        return $startDate->gt($now);
    }

    public function isClosed(): bool
    {
        $now = Carbon::now();
        $endDate = Carbon::parse($this->end_date);
        return $endDate->lt($now);
    }

    /* Winners */
    public function winners()
    {
        if (!$this->isClosed()) return collect();

        $candidates = $this->candidates()->withCount('votes')->get();
        if ($candidates->isEmpty()) return collect();

        $maxVotes = $candidates->max('votes_count') ?? 0;
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
            User::all()->each->refreshEligibility();
        });
    }
}
