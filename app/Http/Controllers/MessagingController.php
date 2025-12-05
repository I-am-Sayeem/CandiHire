<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Models\Message;

class MessagingController extends Controller
{
    // ---------------- LIST ALL CONVERSATIONS ---------------- //
    public function index()
    {
        $userId = session('user_id');
        $userType = session('user_type');

        $conversations = Conversation::where(function($q) use ($userId, $userType) {
            $q->where('ParticipantOneID', $userId)
              ->where('ParticipantOneType', $userType);
        })->orWhere(function($q) use ($userId, $userType) {
            $q->where('ParticipantTwoID', $userId)
              ->where('ParticipantTwoType', $userType);
        })->get();

        return view('messages.index', compact('conversations'));
    }

    // ---------------- OPEN A CHAT ---------------- //
    public function open($conversationId)
    {
        $conversation = Conversation::with('messages')->findOrFail($conversationId);
        return view('messages.chat', compact('conversation'));
    }

    // ---------------- SEND MESSAGE ---------------- //
    public function send(Request $request)
    {
        $request->validate([
            'ConversationID' => 'required',
            'MessageText' => 'required'
        ]);

        $msg = Message::create([
            'ConversationID' => $request->ConversationID,
            'SenderID'       => session('user_id'),
            'SenderType'     => session('user_type'),
            'MessageText'    => $request->MessageText,
            'IsRead'         => 0
        ]);

        // Update last message
        $conv = Conversation::find($request->ConversationID);
        $conv->update([
            'LastMessageID' => $msg->MessageID,
            'LastUpdated'   => now()
        ]);

        return back();
    }
}
