<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_photo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected $appends = ['profile_photo_url'];

    /* Relationships */
    public function createdElections()
    {
        return $this->hasMany(Election::class, 'created_by_admin_id');
    }

    public function updatedElections()
    {
        return $this->hasMany(Election::class, 'updated_by_admin_id');
    }

    public function createdCandidates()
    {
        return $this->hasMany(Candidate::class, 'created_by_admin_id');
    }

    public function updatedCandidates()
    {
        return $this->hasMany(Candidate::class, 'updated_by_admin_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'admin_id');
    }

    /**
     * Get the full URL for the admin's profile photo from Supabase.
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
