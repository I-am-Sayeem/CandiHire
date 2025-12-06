<?php
// messaging_handler.php - Handles all messaging operations
require_once 'database_config.php';
require_once 'session_manager.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, DB_OPTIONS);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

// Get action from various sources
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// If no action found in GET/POST, try to get it from JSON input
$jsonInput = null;
if (empty($action)) {
    $jsonInput = json_decode(file_get_contents('php://input'), true);
    $action = $jsonInput['action'] ?? '';
}

switch ($action) {
    case 'send_message':
        sendMessage($pdo);
        break;
    case 'get_conversations':
        getConversations($pdo);
        break;
    case 'get_messages':
        getMessages($pdo);
        break;
    case 'mark_as_read':
        markAsRead($pdo);
        break;
    case 'get_unread_count':
        getUnreadCount($pdo);
        break;
    case 'search_conversations':
        searchConversations($pdo);
        break;
    case 'delete_message':
        deleteMessage($pdo);
        break;
    case 'delete_conversation':
        deleteConversation($pdo);
        break;
    case 'debug':
        echo json_encode([
            'success' => true, 
            'debug' => [
                'action' => $action,
                'method' => $_SERVER['REQUEST_METHOD'],
                'get' => $_GET,
                'post' => $_POST,
                'input' => json_decode(file_get_contents('php://input'), true)
            ]
        ]);
        break;
    default:
        echo json_encode([
            'success' => false, 
            'message' => 'Invalid action: ' . $action,
            'debug' => [
                'received_action' => $action,
                'method' => $_SERVER['REQUEST_METHOD'],
                'available_actions' => ['send_message', 'get_conversations', 'get_messages', 'mark_as_read', 'get_unread_count', 'search_conversations', 'delete_message', 'delete_conversation']
            ]
        ]);
        break;
}

function sendMessage($pdo) {
    global $jsonInput;
    
    // Get input data
    $input = null;
    
    // Use the already parsed JSON input if available
    if ($jsonInput && isset($jsonInput['sender_id'])) {
        $input = $jsonInput;
    } else {
        // Try to get from JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }
    }
    
    // Debug: Log what we received
    error_log("SendMessage Debug: " . json_encode($input));
    
    $senderId = $input['sender_id'] ?? null;
    $senderType = $input['sender_type'] ?? null;
    $receiverId = $input['receiver_id'] ?? null;
    $receiverType = $input['receiver_type'] ?? null;
    $subject = $input['subject'] ?? null;
    $message = $input['message'] ?? null;
    
    // Validate input
    if (!$senderId || !$senderType || !$receiverId || !$receiverType || !$message) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        return;
    }
    
    // Validate sender type
    if (!in_array($senderType, ['candidate', 'company'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid sender type']);
        return;
    }
    
    // Validate receiver type
    if (!in_array($receiverType, ['candidate', 'company'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid receiver type']);
        return;
    }
    
    try {
        $pdo->beginTransaction();
        
        // Insert message
        $stmt = $pdo->prepare("
            INSERT INTO messages (SenderID, SenderType, ReceiverID, ReceiverType, Subject, Message) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$senderId, $senderType, $receiverId, $receiverType, $subject, $message]);
        $messageId = $pdo->lastInsertId();
        
        // Update or create conversation
        $conversationId = updateOrCreateConversation($pdo, $senderId, $senderType, $receiverId, $receiverType, $messageId);
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Message sent successfully',
            'message_id' => $messageId,
            'conversation_id' => $conversationId
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Failed to send message: ' . $e->getMessage()]);
    }
}

function updateOrCreateConversation($pdo, $participant1Id, $participant1Type, $participant2Id, $participant2Type, $messageId) {
    // Check if conversation exists (in both directions)
    $stmt = $pdo->prepare("
        SELECT ConversationID FROM conversations 
        WHERE (Participant1ID = ? AND Participant1Type = ? AND Participant2ID = ? AND Participant2Type = ?)
        OR (Participant1ID = ? AND Participant1Type = ? AND Participant2ID = ? AND Participant2Type = ?)
    ");
    $stmt->execute([
        $participant1Id, $participant1Type, $participant2Id, $participant2Type,
        $participant2Id, $participant2Type, $participant1Id, $participant1Type
    ]);
    
    $conversation = $stmt->fetch();
    
    if ($conversation) {
        // Update existing conversation
        $stmt = $pdo->prepare("
            UPDATE conversations 
            SET LastMessageID = ?, LastMessageAt = NOW() 
            WHERE ConversationID = ?
        ");
        $stmt->execute([$messageId, $conversation['ConversationID']]);
        return $conversation['ConversationID'];
    } else {
        // Create new conversation
        $stmt = $pdo->prepare("
            INSERT INTO conversations (Participant1ID, Participant1Type, Participant2ID, Participant2Type, LastMessageID) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$participant1Id, $participant1Type, $participant2Id, $participant2Type, $messageId]);
        return $pdo->lastInsertId();
    }
}

function getConversations($pdo) {
    $userId = $_GET['user_id'] ?? null;
    $userType = $_GET['user_type'] ?? null;
    
    if (!$userId || !$userType) {
        echo json_encode(['success' => false, 'message' => 'Missing user ID or type']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                c.ConversationID,
                c.LastMessageAt,
                c.LastMessageID,
                m.Message as LastMessage,
                m.Subject as LastSubject,
                m.CreatedAt as LastMessageTime,
                CASE 
                    WHEN c.Participant1ID = ? AND c.Participant1Type = ? THEN c.Participant2ID
                    ELSE c.Participant1ID
                END as OtherParticipantID,
                CASE 
                    WHEN c.Participant1ID = ? AND c.Participant1Type = ? THEN c.Participant2Type
                    ELSE c.Participant1Type
                END as OtherParticipantType,
                CASE 
                    WHEN c.Participant1ID = ? AND c.Participant1Type = ? THEN 
                        CASE 
                            WHEN c.Participant2Type = 'candidate' THEN 
                                (SELECT FullName FROM candidate_login_info WHERE CandidateID = c.Participant2ID)
                            WHEN c.Participant2Type = 'company' THEN 
                                (SELECT CompanyName FROM Company_login_info WHERE CompanyID = c.Participant2ID)
                        END
                    ELSE 
                        CASE 
                            WHEN c.Participant1Type = 'candidate' THEN 
                                (SELECT FullName FROM candidate_login_info WHERE CandidateID = c.Participant1ID)
                            WHEN c.Participant1Type = 'company' THEN 
                                (SELECT CompanyName FROM Company_login_info WHERE CompanyID = c.Participant1ID)
                        END
                END as OtherParticipantName,
                CASE 
                    WHEN c.Participant1ID = ? AND c.Participant1Type = ? THEN 
                        CASE 
                            WHEN c.Participant2Type = 'candidate' THEN 
                                (SELECT ProfilePicture FROM candidate_login_info WHERE CandidateID = c.Participant2ID)
                            WHEN c.Participant2Type = 'company' THEN 
                                (SELECT Logo FROM Company_login_info WHERE CompanyID = c.Participant2ID)
                        END
                    ELSE 
                        CASE 
                            WHEN c.Participant1Type = 'candidate' THEN 
                                (SELECT ProfilePicture FROM candidate_login_info WHERE CandidateID = c.Participant1ID)
                            WHEN c.Participant1Type = 'company' THEN 
                                (SELECT Logo FROM Company_login_info WHERE CompanyID = c.Participant1ID)
                        END
                END as OtherParticipantAvatar,
                0 as UnreadCount
            FROM conversations c
            LEFT JOIN messages m ON c.LastMessageID = m.MessageID
            WHERE (c.Participant1ID = ? AND c.Participant1Type = ?) 
            OR (c.Participant2ID = ? AND c.Participant2Type = ?)
            ORDER BY c.LastMessageAt DESC
        ");
        
        $stmt->execute([
            $userId, $userType, $userId, $userType, $userId, $userType, $userId, $userType, 
            $userId, $userType, $userId, $userType
        ]);
        
        $conversations = $stmt->fetchAll();
        
        echo json_encode(['success' => true, 'conversations' => $conversations]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to get conversations: ' . $e->getMessage()]);
    }
}

function getMessages($pdo) {
    $conversationId = $_GET['conversation_id'] ?? null;
    $userId = $_GET['user_id'] ?? null;
    $userType = $_GET['user_type'] ?? null;
    $limit = $_GET['limit'] ?? 50;
    $offset = $_GET['offset'] ?? 0;
    $lastMessageId = $_GET['last_message_id'] ?? 0;
    
    if (!$conversationId || !$userId || !$userType) {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        return;
    }
    
    try {
        // Get conversation details
        $stmt = $pdo->prepare("
            SELECT * FROM conversations WHERE ConversationID = ?
        ");
        $stmt->execute([$conversationId]);
        $conversation = $stmt->fetch();
        
        if (!$conversation) {
            echo json_encode(['success' => false, 'message' => 'Conversation not found']);
            return;
        }
        
        // Verify user is participant
        $isParticipant = ($conversation['Participant1ID'] == $userId && $conversation['Participant1Type'] == $userType) ||
                        ($conversation['Participant2ID'] == $userId && $conversation['Participant2Type'] == $userType);
        
        if (!$isParticipant) {
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            return;
        }
        
        // Get messages for this conversation
        $whereClause = "((m.SenderID = ? AND m.SenderType = ? AND m.ReceiverID = ? AND m.ReceiverType = ?)
            OR (m.SenderID = ? AND m.SenderType = ? AND m.ReceiverID = ? AND m.ReceiverType = ?))";
        
        // Add last_message_id filter for real-time polling
        if ($lastMessageId > 0) {
            $whereClause .= " AND m.MessageID > ?";
        }
        
        $stmt = $pdo->prepare("
            SELECT 
                m.*,
                CASE 
                    WHEN m.SenderType = 'candidate' THEN 
                        (SELECT FullName FROM candidate_login_info WHERE CandidateID = m.SenderID)
                    WHEN m.SenderType = 'company' THEN 
                        (SELECT CompanyName FROM Company_login_info WHERE CompanyID = m.SenderID)
                END as SenderName,
                CASE 
                    WHEN m.SenderType = 'candidate' THEN 
                        (SELECT ProfilePicture FROM candidate_login_info WHERE CandidateID = m.SenderID)
                    WHEN m.SenderType = 'company' THEN 
                        (SELECT Logo FROM Company_login_info WHERE CompanyID = m.SenderID)
                END as SenderAvatar
            FROM messages m
            WHERE $whereClause
            ORDER BY m.CreatedAt ASC
            LIMIT ? OFFSET ?
        ");
        
        $params = [
            $conversation['Participant1ID'], $conversation['Participant1Type'], 
            $conversation['Participant2ID'], $conversation['Participant2Type'],
            $conversation['Participant2ID'], $conversation['Participant2Type'], 
            $conversation['Participant1ID'], $conversation['Participant1Type']
        ];
        
        // Add last_message_id parameter if filtering
        if ($lastMessageId > 0) {
            $params[] = $lastMessageId;
        }
        
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt->execute($params);
        
        $messages = $stmt->fetchAll();
        
        // Mark messages as read
        $stmt = $pdo->prepare("
            UPDATE messages 
            SET IsRead = 1 
            WHERE ((SenderID = ? AND SenderType = ? AND ReceiverID = ? AND ReceiverType = ?)
                OR (SenderID = ? AND SenderType = ? AND ReceiverID = ? AND ReceiverType = ?))
            AND IsRead = 0
        ");
        $stmt->execute([
            $conversation['Participant1ID'], $conversation['Participant1Type'], 
            $conversation['Participant2ID'], $conversation['Participant2Type'],
            $conversation['Participant2ID'], $conversation['Participant2Type'], 
            $conversation['Participant1ID'], $conversation['Participant1Type']
        ]);
        
        echo json_encode(['success' => true, 'messages' => $messages]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to get messages: ' . $e->getMessage()]);
    }
}

function markAsRead($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        $input = $_POST;
    }
    
    $messageId = $input['message_id'] ?? null;
    $userId = $input['user_id'] ?? null;
    $userType = $input['user_type'] ?? null;
    
    if (!$messageId || !$userId || !$userType) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("
            UPDATE messages 
            SET IsRead = 1 
            WHERE MessageID = ? AND ReceiverID = ? AND ReceiverType = ?
        ");
        $stmt->execute([$messageId, $userId, $userType]);
        
        echo json_encode(['success' => true, 'message' => 'Message marked as read']);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to mark as read: ' . $e->getMessage()]);
    }
}

function getUnreadCount($pdo) {
    $userId = $_GET['user_id'] ?? null;
    $userType = $_GET['user_type'] ?? null;
    
    if (!$userId || !$userType) {
        echo json_encode(['success' => false, 'message' => 'Missing user ID or type']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as unread_count 
            FROM messages 
            WHERE ReceiverID = ? AND ReceiverType = ? AND IsRead = 0
        ");
        $stmt->execute([$userId, $userType]);
        $result = $stmt->fetch();
        
        echo json_encode(['success' => true, 'unread_count' => $result['unread_count']]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to get unread count: ' . $e->getMessage()]);
    }
}

function searchConversations($pdo) {
    $userId = $_GET['user_id'] ?? null;
    $userType = $_GET['user_type'] ?? null;
    $query = $_GET['query'] ?? '';
    
    if (!$userId || !$userType) {
        echo json_encode(['success' => false, 'message' => 'Missing user ID or type']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                c.ConversationID,
                c.LastMessageAt,
                c.LastMessageID,
                m.Message as LastMessage,
                m.Subject as LastSubject,
                m.CreatedAt as LastMessageTime,
                CASE 
                    WHEN c.Participant1ID = ? AND c.Participant1Type = ? THEN c.Participant2ID
                    ELSE c.Participant1ID
                END as OtherParticipantID,
                CASE 
                    WHEN c.Participant1ID = ? AND c.Participant1Type = ? THEN c.Participant2Type
                    ELSE c.Participant1Type
                END as OtherParticipantType,
                CASE 
                    WHEN c.Participant1Type = 'candidate' AND c.Participant1ID = ? THEN 
                        (SELECT CONCAT(FirstName, ' ', LastName) FROM candidate_login_info WHERE CandidateID = c.Participant1ID)
                    WHEN c.Participant1Type = 'company' AND c.Participant1ID = ? THEN 
                        (SELECT CompanyName FROM Company_login_info WHERE CompanyID = c.Participant1ID)
                    WHEN c.Participant2Type = 'candidate' AND c.Participant2ID = ? THEN 
                        (SELECT CONCAT(FirstName, ' ', LastName) FROM candidate_login_info WHERE CandidateID = c.Participant2ID)
                    WHEN c.Participant2Type = 'company' AND c.Participant2ID = ? THEN 
                        (SELECT CompanyName FROM Company_login_info WHERE CompanyID = c.Participant2ID)
                END as OtherParticipantName
            FROM conversations c
            LEFT JOIN messages m ON c.LastMessageID = m.MessageID
            WHERE ((c.Participant1ID = ? AND c.Participant1Type = ?) 
            OR (c.Participant2ID = ? AND c.Participant2Type = ?))
            AND (
                m.Message LIKE ? OR 
                m.Subject LIKE ? OR
                (CASE 
                    WHEN c.Participant1Type = 'candidate' AND c.Participant1ID = ? THEN 
                        (SELECT CONCAT(FirstName, ' ', LastName) FROM candidate_login_info WHERE CandidateID = c.Participant1ID)
                    WHEN c.Participant1Type = 'company' AND c.Participant1ID = ? THEN 
                        (SELECT CompanyName FROM Company_login_info WHERE CompanyID = c.Participant1ID)
                    WHEN c.Participant2Type = 'candidate' AND c.Participant2ID = ? THEN 
                        (SELECT CONCAT(FirstName, ' ', LastName) FROM candidate_login_info WHERE CandidateID = c.Participant2ID)
                    WHEN c.Participant2Type = 'company' AND c.Participant2ID = ? THEN 
                        (SELECT CompanyName FROM Company_login_info WHERE CompanyID = c.Participant2ID)
                END) LIKE ?
            )
            ORDER BY c.LastMessageAt DESC
        ");
        
        $searchTerm = "%$query%";
        $stmt->execute([
            $userId, $userType, $userId, $userType, $userId, $userId, $userId, $userId,
            $userId, $userType, $userId, $userType,
            $searchTerm, $searchTerm, $userId, $userId, $userId, $userId, $searchTerm
        ]);
        
        $conversations = $stmt->fetchAll();
        
        echo json_encode(['success' => true, 'conversations' => $conversations]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to search conversations: ' . $e->getMessage()]);
    }
}

function deleteMessage($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        $input = $_POST;
    }
    
    $messageId = $input['message_id'] ?? null;
    $userId = $input['user_id'] ?? null;
    $userType = $input['user_type'] ?? null;
    
    if (!$messageId || !$userId || !$userType) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        return;
    }
    
    try {
        // Verify user owns the message
        $stmt = $pdo->prepare("
            SELECT * FROM messages 
            WHERE MessageID = ? AND (SenderID = ? AND SenderType = ?)
        ");
        $stmt->execute([$messageId, $userId, $userType]);
        $message = $stmt->fetch();
        
        if (!$message) {
            echo json_encode(['success' => false, 'message' => 'Message not found or access denied']);
            return;
        }
        
        // Delete message
        $stmt = $pdo->prepare("DELETE FROM messages WHERE MessageID = ?");
        $stmt->execute([$messageId]);
        
        echo json_encode(['success' => true, 'message' => 'Message deleted successfully']);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to delete message: ' . $e->getMessage()]);
    }
}

function deleteConversation($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        $input = $_POST;
    }
    
    $conversationId = $input['conversation_id'] ?? null;
    $userId = $input['user_id'] ?? null;
    $userType = $input['user_type'] ?? null;
    
    if (!$conversationId || !$userId || !$userType) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        return;
    }
    
    try {
        // Verify user is participant
        $stmt = $pdo->prepare("
            SELECT * FROM conversations 
            WHERE ConversationID = ? AND ((Participant1ID = ? AND Participant1Type = ?) OR (Participant2ID = ? AND Participant2Type = ?))
        ");
        $stmt->execute([$conversationId, $userId, $userType, $userId, $userType]);
        $conversation = $stmt->fetch();
        
        if (!$conversation) {
            echo json_encode(['success' => false, 'message' => 'Conversation not found or access denied']);
            return;
        }
        
        $pdo->beginTransaction();
        
        // Delete all messages in conversation
        $stmt = $pdo->prepare("
            DELETE FROM messages 
            WHERE ((SenderID = ? AND SenderType = ? AND ReceiverID = ? AND ReceiverType = ?)
                OR (SenderID = ? AND SenderType = ? AND ReceiverID = ? AND ReceiverType = ?))
        ");
        $stmt->execute([
            $conversation['Participant1ID'], $conversation['Participant1Type'], 
            $conversation['Participant2ID'], $conversation['Participant2Type'],
            $conversation['Participant2ID'], $conversation['Participant2Type'], 
            $conversation['Participant1ID'], $conversation['Participant1Type']
        ]);
        
        // Delete conversation
        $stmt = $pdo->prepare("DELETE FROM conversations WHERE ConversationID = ?");
        $stmt->execute([$conversationId]);
        
        $pdo->commit();
        
        echo json_encode(['success' => true, 'message' => 'Conversation deleted successfully']);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Failed to delete conversation: ' . $e->getMessage()]);
    }
}
?>
