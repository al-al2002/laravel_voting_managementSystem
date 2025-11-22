<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Candidate extends Model
{
    use HasFactory;

    protected $fillable = ['election_id', 'name', 'position', 'description', 'photo'];

    protected $appends = ['photo_url'];

    public function election()
    {
        return $this->belongsTo(Election::class);
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    /**
     * Get the full URL for the candidate's photo from Supabase.
     */
    public function getPhotoUrlAttribute()
    {
        if (!$this->photo) {
            return null;
        }

        // Get Supabase configuration
        $supabaseUrl = config('filesystems.disks.supabase.url');
        $bucket = config('filesystems.disks.supabase.bucket');

        if ($supabaseUrl && $bucket) {
            return rtrim($supabaseUrl, '/') . "/storage/v1/object/public/{$bucket}/{$this->photo}";
        }

        // Fallback to local storage if Supabase not configured
        return asset('storage/' . $this->photo);
    }
}
