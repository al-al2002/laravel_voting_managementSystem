<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SmsController extends Controller
{
    // Show inbox: latest message per unique conversation (per user)
    public function index()
    {
        $messages = Message::with('user')
            ->select('messages.*')
            ->join(
                DB::raw('(SELECT MAX(id) as latest_id FROM messages GROUP BY conversation_id) latest'),
                'messages.id',
                '=',
                'latest.latest_id'
            )
            ->orderByDesc('messages.created_at')
            ->get();

        return view('admin.sms.inbox', compact('messages'));
    }

    // Show full conversation thread with a specific user
    public function conversation($conversation_id)
    {
        $messages = Message::with('user')
            ->where('conversation_id', $conversation_id)
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark unread user messages as read
        Message::where('conversation_id', $conversation_id)
            ->where('to', 'admin')
            ->where('status', 'unread')
            ->update(['status' => 'read']);

        return view('admin.sms.conversation', compact('messages', 'conversation_id'));
    }

    // Admin replies (text and/or image)
    public function reply(Request $request, $conversation_id)
    {
        $request->validate([
            'reply' => 'nullable|string|max:1000',
            'image.*' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $firstMessage = Message::where('conversation_id', $conversation_id)->first();
        if (!$firstMessage) {
            return redirect()->back()->with('error', 'Conversation not found.');
        }

        $images = [];
        if ($request->hasFile('image')) {
            foreach ($request->file('image') as $file) {
                $images[] = $file->store('messages', 'supabase');
            }
        }

        Message::create([
            'user_id' => $firstMessage->user_id,
            'admin_id' => session('auth_id'), // Track which admin replied
            'conversation_id' => $conversation_id,
            'message' => $request->reply ?? '',
            'image' => !empty($images) ? json_encode($images) : null,
            'status' => 'unread',
            'to' => 'user',
            'sender_type' => 'admin',
        ]);

        return redirect()->back()->with('success', 'Reply sent successfully.');
    }

    // Permanently delete a conversation (all messages in that conversation)
    public function destroyConversation($conversation_id)
    {
        $conversation = Message::where('conversation_id', $conversation_id)->get();

        if ($conversation->isEmpty()) {
            return redirect()->back()->with('error', 'Conversation not found.');
        }

        // Delete all associated messages permanently
        foreach ($conversation as $message) {
            // Delete images if exist
            if ($message->image) {
                $images = json_decode($message->image, true);
                foreach ($images as $img) {
                    try {
                        Storage::disk('supabase')->delete($img);
                    } catch (\Exception $e) {
                        // File doesn't exist or deletion failed, continue
                    }
                }
            }
            $message->delete();
        }

        return redirect()->route('admin.sms.index')->with('success', 'Conversation permanently deleted.');
    }
}
