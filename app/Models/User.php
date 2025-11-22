<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Election;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'voter_id',
        'profile_photo',
        'is_eligible',
        'eligibility_overridden',
        'role',
    ];

    protected $casts = [
        'is_eligible' => 'boolean',
        'eligibility_overridden' => 'boolean',
    ];

    protected $appends = ['profile_photo_url'];

    /* Relationships */
    public function votes()
    {
        return $this->hasMany(Vote::class, 'user_id');
    }

    public function elections()
    {
        return $this->belongsToMany(Election::class, 'votes', 'user_id', 'election_id')
                    ->withTimestamps();
    }

    /* Skipped Elections Logic */
    public function skippedElections(): array
    {
        // Elections that have ended after voter creation
        $endedElections = Election::where('end_date', '>', $this->created_at)
                                  ->where('end_date', '<=', now())
                                  ->get();

        $skipped = [];
        foreach ($endedElections as $election) {
            $voted = $this->votes()->where('election_id', $election->id)->exists();
            if (!$voted) $skipped[] = $election->title;
        }

        return $skipped;
    }

    public function skippedElectionsCount(): int
    {
        return count($this->skippedElections());
    }

    /* Eligibility Logic */

    /**
     * Calculate auto-eligibility based on skipped elections
     */
    public function isAutoEligible(): bool
    {
        return $this->skippedElectionsCount() < 5;
    }

    /**
     * Final eligibility
     */
    public function finalEligibility(): bool
    {
        // Admin override takes priority
        if ($this->eligibility_overridden) {
            return $this->is_eligible;
        }

        // Auto-eligibility logic
        return $this->isAutoEligible();
    }

    /**
     * Refresh eligibility automatically
     */
    public function refreshEligibility(): void
    {
        // If admin override exists, respect it
        if ($this->eligibility_overridden) {
            // Only remove override if auto rules allow it
            if ($this->isAutoEligible()) {
                $this->eligibility_overridden = false;
                $this->is_eligible = true;
                $this->save();
            }
            return;
        }

        // No override: auto-adjust eligibility based on skipped elections
        $autoEligible = $this->isAutoEligible();
        if ($this->is_eligible !== $autoEligible) {
            $this->is_eligible = $autoEligible;
            $this->save();
        }
    }

    /* Admin Override Methods */
    public function overrideEligibility(bool $status): void
    {
        $this->is_eligible = $status;
        $this->eligibility_overridden = true;
        $this->save();
    }

    public function removeOverride(): void
    {
        $this->eligibility_overridden = false;
        $this->save();
    }
    // User.php


public function skippedElectionsWithId(): array
{
    $endedElections = Election::where('end_date', '>', $this->created_at)
                              ->where('end_date', '<=', now())
                              ->get();

    $skipped = [];

    foreach ($endedElections as $election) {
        $voted = $this->votes()->where('election_id', $election->id)->exists();
        if (!$voted) {
            $skipped[] = [
                'id' => $election->id,
                'title' => $election->title
            ];
        }
    }

    return $skipped;
}

    /**
     * Get the full URL for the user's profile photo from Supabase.
     */
    public function getProfilePhotoUrlAttribute()
    {
        if (!$this->profile_photo) {
            return null;
        }

        // Get Supabase configuration
        $supabaseUrl = config('filesystems.disks.supabase.url');
        $bucket = config('filesystems.disks.supabase.bucket');

        if ($supabaseUrl && $bucket) {
            return rtrim($supabaseUrl, '/') . "/storage/v1/object/public/{$bucket}/{$this->profile_photo}";
        }

        // Fallback to local storage if Supabase not configured
        return asset('storage/' . $this->profile_photo);
    }

}
