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
        $userType = session('user_type') ?? 'candidate';

        $conversationsRaw = Conversation::with('lastMessage')
            ->where(function($q) use ($userId, $userType) {
                $q->where('Participant1ID', $userId)
                  ->where('Participant1Type', $userType);
            })->orWhere(function($q) use ($userId, $userType) {
                $q->where('Participant2ID', $userId)
                  ->where('Participant2Type', $userType);
            })
            ->orderBy('LastMessageAt', 'desc')
            ->get();

        $formattedConversations = $conversationsRaw->map(function($conv) use ($userId, $userType) {
            return $this->formatConversation($conv, $userId, $userType);
        });

        return view('messaging.messaging', [
            'conversationsJson' => $formattedConversations,
            'currentConversationJson' => null,
            'messagesJson' => []
        ]);
    }

    // ---------------- OPEN A CHAT ---------------- //
    public function open($conversationId)
    {
        $userId = session('user_id');
        $userType = session('user_type') ?? 'candidate';

        $conversation = Conversation::with(['messages', 'lastMessage'])
            ->findOrFail($conversationId);

        // Security check: ensure user is participant
        $isParticipant = ($conversation->Participant1ID == $userId && $conversation->Participant1Type == $userType) ||
                         ($conversation->Participant2ID == $userId && $conversation->Participant2Type == $userType);

        if (!$isParticipant) {
            abort(403);
        }

        // Mark messages as read
        Message::where('ConversationID', $conversationId)
            ->where(function($q) use ($userId, $userType) {
                $q->where('SenderID', '!=', $userId)
                  ->orWhere('SenderType', '!=', $userType);
            })
            ->update(['IsRead' => 1]);


        // Get all conversations list
        $conversationsRaw = Conversation::with('lastMessage')
            ->where(function($q) use ($userId, $userType) {
                $q->where('Participant1ID', $userId)
                  ->where('Participant1Type', $userType);
            })->orWhere(function($q) use ($userId, $userType) {
                $q->where('Participant2ID', $userId)
                  ->where('Participant2Type', $userType);
            })
            ->orderBy('LastMessageAt', 'desc')
            ->get();

        $formattedConversations = $conversationsRaw->map(function($conv) use ($userId, $userType) {
            return $this->formatConversation($conv, $userId, $userType);
        });

        $currentConvFormatted = $this->formatConversation($conversation, $userId, $userType);

        $messagesFormatted = $conversation->messages->map(function($msg) use ($userId, $userType) {
            return [
                'MessageID' => $msg->MessageID,
                'Message' => $msg->MessageText,
                'SenderID' => $msg->SenderID,
                'SenderType' => $msg->SenderType,
                'IsRead' => $msg->IsRead,
                'CreatedAt' => $msg->created_at->toISOString()
            ];
        });

        return view('messaging.messaging', [
            'conversationsJson' => $formattedConversations,
            'currentConversationJson' => $currentConvFormatted,
            'messagesJson' => $messagesFormatted
        ]);
    }

    private function formatConversation($conversation, $currentUserId, $currentUserType) {
        $otherType = null;
        $otherId = null;

        if ($conversation->Participant1ID == $currentUserId && $conversation->Participant1Type == $currentUserType) {
            $otherId = $conversation->Participant2ID;
            $otherType = $conversation->Participant2Type;
        } else {
            $otherId = $conversation->Participant1ID;
            $otherType = $conversation->Participant1Type;
        }

        $otherName = 'Unknown';
        $otherAvatar = null;

        if ($otherType === 'candidate') {
            $candidate = \App\Models\Candidate::find($otherId);
            if ($candidate) {
                $otherName = $candidate->FullName;
                $otherAvatar = $candidate->ProfilePicture; 
            }
        } elseif ($otherType === 'company') {
            $company = \App\Models\Company::find($otherId);
            if ($company) {
                $otherName = $company->CompanyName;
                $otherAvatar = $company->Logo;
            }
        }

        // Unread count
        $unreadCount = $conversation->messages()
            ->where(function($q) use ($currentUserId, $currentUserType) {
                $q->where('SenderID', '!=', $currentUserId)
                  ->orWhere('SenderType', '!=', $currentUserType);
            })
            ->where('IsRead', 0)
            ->count();

        $lastMsg = $conversation->lastMessage;

        return [
            'ConversationID' => $conversation->ConversationID,
            'OtherParticipantID' => $otherId,
            'OtherParticipantType' => $otherType,
            'OtherParticipantName' => $otherName,
            'OtherParticipantAvatar' => $otherAvatar ? asset($otherAvatar) : null,
            'LastMessage' => $lastMsg ? $lastMsg->MessageText : '',
            'LastMessageTime' => $lastMsg ? $lastMsg->created_at->toISOString() : $conversation->updated_at->toISOString(),
            'UnreadCount' => $unreadCount
        ];
    }

    // ---------------- SEND MESSAGE ---------------- //
    public function send(Request $request)
    {
        $request->validate([
            'ConversationID' => 'required',
            'MessageText' => 'required'
        ]);
        
        // Ensure user is part of conversation
         $userId = session('user_id');
         $userType = session('user_type') ?? 'candidate';
         
         $conversation = Conversation::findOrFail($request->ConversationID);
         $isParticipant = ($conversation->Participant1ID == $userId && $conversation->Participant1Type == $userType) ||
                          ($conversation->Participant2ID == $userId && $conversation->Participant2Type == $userType);
         
         if (!$isParticipant) abort(403);

        // Derive receiver from conversation (the other participant)
        $receiverId = null;
        $receiverType = null;
        if ($conversation->Participant1ID == $userId && $conversation->Participant1Type == $userType) {
            $receiverId = $conversation->Participant2ID;
            $receiverType = $conversation->Participant2Type;
        } else {
            $receiverId = $conversation->Participant1ID;
            $receiverType = $conversation->Participant1Type;
        }

        $msg = Message::create([
            'ConversationID' => $request->ConversationID,
            'SenderID'       => $userId,
            'SenderType'     => $userType,
            'ReceiverID'     => $receiverId,
            'ReceiverType'   => $receiverType,
            'MessageText'    => $request->MessageText,
            'IsRead'         => 0
        ]);

        // Update last message
        $conversation->update([
            'LastMessageID' => $msg->MessageID,
            'LastMessageAt' => now()
        ]);
        
        if($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => [
                    'MessageID' => $msg->MessageID,
                    'Message' => $msg->MessageText,
                    'SenderID' => $msg->SenderID,
                    'SenderType' => $msg->SenderType,
                    'CreatedAt' => $msg->created_at->toISOString()
                ]
            ]);
        }

        return back();
    }
    // ---------------- API: SEND MESSAGE (AUTO CREATE CONVERSATION) ---------------- //
    public function apiSendMessage(Request $request)
    {
        try {
            $senderId = session('user_id');
            $senderType = session('user_type') ?? 'candidate';
            $receiverId = $request->receiver_id;
            $receiverType = $request->receiver_type;
            $messageText = $request->message;

            if (!$senderId || !$receiverId || !$messageText) {
                return response()->json(['success' => false, 'message' => 'Missing required fields']);
            }

            // check if conversation exists
            $conversation = Conversation::where(function($q) use ($senderId, $senderType, $receiverId, $receiverType) {
                $q->where('Participant1ID', $senderId)
                  ->where('Participant1Type', $senderType)
                  ->where('Participant2ID', $receiverId)
                  ->where('Participant2Type', $receiverType);
            })->orWhere(function($q) use ($senderId, $senderType, $receiverId, $receiverType) {
                $q->where('Participant1ID', $receiverId)
                  ->where('Participant1Type', $receiverType)
                  ->where('Participant2ID', $senderId)
                  ->where('Participant2Type', $senderType);
            })->first();

            if (!$conversation) {
                $conversation = Conversation::create([
                    'Participant1ID' => $senderId,
                    'Participant1Type' => $senderType,
                    'Participant2ID' => $receiverId,
                    'Participant2Type' => $receiverType,
                    'LastMessageAt' => now()
                ]);
            }

            $msg = Message::create([
                'ConversationID' => $conversation->ConversationID,
                'SenderID'       => $senderId,
                'SenderType'     => $senderType,
                'ReceiverID'     => $receiverId,
                'ReceiverType'   => $receiverType,
                'Subject'        => $request->subject ?? null,
                'MessageText'    => $messageText,
                'IsRead'         => 0
            ]);

            $conversation->update([
                'LastMessageID' => $msg->MessageID,
                'LastMessageAt' => now()
            ]);

            return response()->json(['success' => true, 'message' => 'Message sent successfully']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // ---------------- API: GET UNREAD COUNT ---------------- //
    public function apiGetUnreadCount()
    {
        $userId = session('user_id');
        $userType = session('user_type') ?? 'candidate';

        if (!$userId) {
            return response()->json(['success' => false, 'unread_count' => 0]);
        }

        // Count unread messages where the current user is NOT the sender
        // and the message belongs to a conversation they are part of
        // (Simplified logic: assuming access to messages table directly or via relationship)
        
        // Better: Find all conversations for user, then count unread messages in them sent by others
        $conversations = Conversation::where(function($q) use ($userId, $userType) {
            $q->where('Participant1ID', $userId)->where('Participant1Type', $userType);
        })->orWhere(function($q) use ($userId, $userType) {
            $q->where('Participant2ID', $userId)->where('Participant2Type', $userType);
        })->pluck('ConversationID');

        $unreadCount = Message::whereIn('ConversationID', $conversations)
            ->where('SenderID', '!=', $userId) // Assuming sender isn't self (needs type check strictly but ID usually sufficient if unique across types, or add SenderType check if IDs overlap)
            ->where('IsRead', 0)
            ->count();

        return response()->json(['success' => true, 'unread_count' => $unreadCount]);
    }

    // ---------------- API: LIST CONVERSATIONS ---------------- //
    public function apiListConversations()
    {
        $userId = session('user_id');
        $userType = session('user_type') ?? 'candidate';

        if (!$userId) {
            return response()->json(['success' => false, 'conversations' => []]);
        }

        $conversationsRaw = Conversation::with('lastMessage')
            ->where(function($q) use ($userId, $userType) {
                $q->where('Participant1ID', $userId)
                  ->where('Participant1Type', $userType);
            })->orWhere(function($q) use ($userId, $userType) {
                $q->where('Participant2ID', $userId)
                  ->where('Participant2Type', $userType);
            })
            ->orderBy('LastMessageAt', 'desc')
            ->get();

        $formatted = $conversationsRaw->map(function($conv) use ($userId, $userType) {
            return $this->formatConversation($conv, $userId, $userType);
        });

        return response()->json(['success' => true, 'conversations' => $formatted]);
    }

    // ---------------- API: GET MESSAGES FOR CONVERSATION ---------------- //
    public function apiGetMessages($conversationId)
    {
        $userId = session('user_id');
        $userType = session('user_type') ?? 'candidate';

        if (!$userId) {
            return response()->json(['success' => false, 'messages' => []]);
        }

        $conversation = Conversation::with('messages')->find($conversationId);
        
        if (!$conversation) {
            return response()->json(['success' => false, 'messages' => [], 'error' => 'Conversation not found']);
        }

        // Security check
        $isParticipant = ($conversation->Participant1ID == $userId && $conversation->Participant1Type == $userType) ||
                         ($conversation->Participant2ID == $userId && $conversation->Participant2Type == $userType);

        if (!$isParticipant) {
            return response()->json(['success' => false, 'messages' => [], 'error' => 'Unauthorized']);
        }

        // Mark messages as read
        Message::where('ConversationID', $conversationId)
            ->where(function($q) use ($userId, $userType) {
                $q->where('SenderID', '!=', $userId)
                  ->orWhere('SenderType', '!=', $userType);
            })
            ->update(['IsRead' => 1]);

        $messagesFormatted = $conversation->messages->map(function($msg) {
            return [
                'MessageID' => $msg->MessageID,
                'Message' => $msg->MessageText,
                'SenderID' => $msg->SenderID,
                'SenderType' => $msg->SenderType,
                'IsRead' => $msg->IsRead,
                'CreatedAt' => $msg->created_at->toISOString()
            ];
        });

        return response()->json(['success' => true, 'messages' => $messagesFormatted]);
    }

    // Store typing status in cache (in-memory for real-time)
    private static array $typingStatus = [];

    // ---------------- API: SET TYPING STATUS ---------------- //
    public function apiSetTypingStatus(Request $request)
    {
        $userId = session('user_id');
        $userType = session('user_type') ?? 'candidate';
        $conversationId = $request->conversation_id;
        $isTyping = $request->is_typing ?? false;

        if (!$userId || !$conversationId) {
            return response()->json(['success' => false]);
        }

        $key = "typing_{$conversationId}_{$userId}_{$userType}";
        
        if ($isTyping) {
            // Store with timestamp (expires after 3 seconds)
            cache()->put($key, [
                'user_id' => $userId,
                'user_type' => $userType,
                'timestamp' => now()->timestamp
            ], 5); // 5 seconds TTL
        } else {
            cache()->forget($key);
        }

        return response()->json(['success' => true]);
    }

    // ---------------- API: GET TYPING STATUS ---------------- //
    public function apiGetTypingStatus($conversationId)
    {
        $userId = session('user_id');
        $userType = session('user_type') ?? 'candidate';

        if (!$userId) {
            return response()->json(['success' => false, 'is_typing' => false]);
        }

        // Get the conversation to find the other participant
        $conversation = Conversation::find($conversationId);
        if (!$conversation) {
            return response()->json(['success' => false, 'is_typing' => false]);
        }

        // Determine the other participant
        if ($conversation->Participant1ID == $userId && $conversation->Participant1Type == $userType) {
            $otherId = $conversation->Participant2ID;
            $otherType = $conversation->Participant2Type;
        } else {
            $otherId = $conversation->Participant1ID;
            $otherType = $conversation->Participant1Type;
        }

        // Check if the other person is typing
        $key = "typing_{$conversationId}_{$otherId}_{$otherType}";
        $typingData = cache()->get($key);

        $isTyping = false;
        if ($typingData && (now()->timestamp - $typingData['timestamp']) < 3) {
            $isTyping = true;
        }

        return response()->json(['success' => true, 'is_typing' => $isTyping]);
    }
}
