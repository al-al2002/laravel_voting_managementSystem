<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Message;

class MessageController extends Controller
{
    // Show all conversations
   // Show all conversations for the logged-in user
public function index()
{
    $userId = Auth::id();

    // Get all messages for the user that are not deleted by the user
    $messages = Message::where(function ($query) use ($userId) {
            $query->where('user_id', $userId)
                  ->orWhere(function ($q) use ($userId) {
                      $q->where('to', 'user')
                        ->where('sender_type', 'admin')
                        ->where('user_id', $userId);
                  });
        })
        ->where('deleted_by_user', false)
        ->orderBy('created_at', 'desc')
        ->get()
        ->groupBy('conversation_id') // Group by conversation
        ->map(function ($msgs) {
            // Get the latest message in this conversation
            $latest = $msgs->sortByDesc('created_at')->first();

            // Count only unread messages sent by admin
            $unreadCountAdmin = $msgs->where('status', 'unread')
                                     ->where('sender_type', 'admin')
                                     ->count();

            // Get first image URL if exists
            $firstImageUrl = null;
            if ($latest->image) {
                $images = json_decode($latest->image, true);
                if (is_array($images) && count($images) > 0) {
                    $supabaseUrl = config('filesystems.disks.supabase.url');
                    $bucket = config('filesystems.disks.supabase.bucket');
                    if ($supabaseUrl && $bucket) {
                        $firstImageUrl = rtrim($supabaseUrl, '/') . "/storage/v1/object/public/{$bucket}/{$images[0]}";
                    } else {
                        $firstImageUrl = asset('storage/' . $images[0]);
                    }
                }
            }

            return (object)[
                'conversation_id' => $latest->conversation_id,
                'latest_message' => $latest->message ?? '',
                'latest_image' => $latest->image ?? null,
                'latest_image_url' => $firstImageUrl,
                'latest_time' => $latest->created_at,
                'sender_type' => $latest->sender_type,
                'unread_count_admin' => $unreadCountAdmin,
            ];
        })
        ->values();

    return view('user.sms.index', compact('messages'));
}


    // Show new message form
    public function create()
    {
        $conversationId = null; // always start new conversation
        return view('user.sms.create', compact('conversationId'));
    }

    // Store new message
    public function store(Request $request)
    {
        $request->validate([
            'message' => 'nullable|string|max:1000',
            'image.*' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        // Prevent empty submissions: require at least text or an image
        $hasMessage = !empty(trim((string) $request->message));
        $hasImage = $request->hasFile('image');
        if (! $hasMessage && ! $hasImage) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => ['message' => 'Please enter a message or attach an image.']
                ], 422);
            }

            return redirect()->back()->withErrors(['message' => 'Please enter a message or attach an image.'])->withInput();
        }

        $userId = Auth::id();
        $conversationId = (string) Str::uuid();

        $imagePaths = [];
        if ($request->hasFile('image')) {
            foreach ($request->file('image') as $img) {
                $imagePaths[] = $img->store('messages', 'supabase');
            }
        }

        $msg = Message::create([
            'conversation_id' => $conversationId,
            'user_id' => $userId,
            'message' => $request->message ?? '',
            'image' => !empty($imagePaths) ? json_encode($imagePaths) : null,
            'status' => 'unread',
            'to' => 'admin',
            'sender_type' => 'user',
        ]);

        // If request is AJAX, return JSON so the frontend can handle navigation without a full redirect
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'conversation_id' => $conversationId,
                'message' => $msg,
            ]);
        }

        return redirect()
            ->route('user.messages.conversation', $conversationId)
            ->with('success', 'Message sent successfully!');
    }

    // Show conversation thread
    public function conversation($conversation_id)
    {
        $userId = Auth::id();

        $messages = Message::where('conversation_id', $conversation_id)
            ->where('deleted_by_user', false)
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhere(function ($q2) use ($userId) {
                      $q2->where('to', 'user')
                         ->where('sender_type', 'admin')
                         ->where('user_id', $userId);
                  });
            })
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark unread admin messages as read
        Message::where('conversation_id', $conversation_id)
            ->where('to', 'user')
            ->where('sender_type', 'admin')
            ->where('user_id', $userId)
            ->where('status', 'unread')
            ->update(['status' => 'read']);

        return view('user.sms.conversation', compact('messages', 'conversation_id'));
    }

    // Reply to conversation
    public function reply(Request $request, $conversation_id)
    {
        $request->validate([
            'message' => 'nullable|string|max:1000',
            'image.*' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        // Prevent empty submissions on reply: require at least text or an image
        $hasMessage = !empty(trim((string) $request->message));
        $hasImage = $request->hasFile('image');
        if (! $hasMessage && ! $hasImage) {
            return response()->json([
                'success' => false,
                'errors' => ['message' => 'Please enter a message or attach an image.']
            ], 422);
        }

        $userId = Auth::id();
        $imagePaths = [];

        if ($request->hasFile('image')) {
            foreach ($request->file('image') as $img) {
                $imagePaths[] = $img->store('messages', 'supabase');
            }
        }

        $msg = Message::create([
            'conversation_id' => $conversation_id,
            'user_id' => $userId,
            'message' => $request->message ?? '',
            'image' => !empty($imagePaths) ? json_encode($imagePaths) : null,
            'status' => 'unread',
            'to' => 'admin',
            'sender_type' => 'user',
        ]);

        // Refresh the message to ensure image_urls accessor is available
        $msg->refresh();

        return response()->json([
            'success' => true,
            'conversation_id' => $conversation_id,
            'message' => $msg
        ]);
    }

    // Soft-delete entire conversation for the logged-in user
    public function destroyConversation($conversation_id)
    {
        $userId = Auth::id();

        Message::where('conversation_id', $conversation_id)
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId) // user's own messages
                  ->orWhere(function ($q2) use ($userId) {
                      $q2->where('to', 'user'); // admin messages to user
                  });
            })
            ->update(['deleted_by_user' => true]);

        return redirect()->route('user.messages.index')
                         ->with('success', 'Conversation deleted from your inbox.');
    }
}
