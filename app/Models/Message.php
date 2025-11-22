<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'conversation_id',
        'message',
        'image',
        'status',
        'to',
        'sender_type',
    ];

    protected $appends = ['image_urls'];

    /**
     * 🔗 Each message belongs to a user (sender)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 🕓 Get the latest message in a conversation
     */
    public function latestMessage()
    {
        return $this->hasOne(Message::class, 'conversation_id', 'conversation_id')->latestOfMany();
    }

    /**
     * 📦 Scope for a specific conversation
     */
    public function scopeInConversation($query, $conversation_id)
    {
        return $query->where('conversation_id', $conversation_id);
    }

    /**
     * 📦 Scope for unread messages
     */
    public function scopeUnread($query)
    {
        return $query->where('status', 'unread');
    }

    /**
     * 🖼 Full image URLs accessor
     */
    public function getImageUrlsAttribute()
    {
        if (!$this->image) {
            return [];
        }

        $images = json_decode($this->image, true);
        if (!is_array($images)) {
            return [];
        }

        // Get Supabase configuration
        $supabaseUrl = config('filesystems.disks.supabase.url');
        $bucket = config('filesystems.disks.supabase.bucket');

        return array_map(function($img) use ($supabaseUrl, $bucket) {
            if ($supabaseUrl && $bucket) {
                return rtrim($supabaseUrl, '/') . "/storage/v1/object/public/{$bucket}/{$img}";
            }
            // Fallback to local storage if Supabase not configured
            return asset('storage/' . $img);
        }, $images);
    }
}
