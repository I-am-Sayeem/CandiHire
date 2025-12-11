<?php
// message_button_helper.php - Helper functions for message buttons

/**
 * Generate a message button for job posts
 */
function generateJobMessageButton($jobId, $companyId, $companyName, $userType, $userId) {
    if ($userType === 'candidate') {
        return '
            <button class="message-job-btn btn btn-primary" 
                    onclick="openMessageDialog(' . $jobId . ', ' . $companyId . ', \'company\', \'' . addslashes($companyName) . '\')"
                    style="display: flex; align-items: center; gap: 8px; padding: 8px 16px; font-size: 14px;">
                <i class="fas fa-comment"></i>
                Message Company
            </button>';
    }
    return '';
}

/**
 * Generate a message button for candidate profiles
 */
function generateCandidateMessageButton($candidateId, $candidateName, $userType, $userId) {
    if ($userType === 'company') {
        return '
            <button class="message-candidate-btn btn btn-primary" 
                    onclick="openMessageDialog(' . $candidateId . ', ' . $candidateId . ', \'candidate\', \'' . addslashes($candidateName) . '\')"
                    style="display: flex; align-items: center; gap: 8px; padding: 8px 16px; font-size: 14px;">
                <i class="fas fa-comment"></i>
                Message Candidate
            </button>';
    }
    return '';
}

/**
 * Generate a message button for company profiles
 */
function generateCompanyMessageButton($companyId, $companyName, $userType, $userId) {
    if ($userType === 'candidate') {
        return '
            <button class="message-company-btn btn btn-primary" 
                    onclick="openMessageDialog(' . $companyId . ', ' . $companyId . ', \'company\', \'' . addslashes($companyName) . '\')"
                    style="display: flex; align-items: center; gap: 8px; padding: 8px 16px; font-size: 14px;">
                <i class="fas fa-comment"></i>
                Message Company
            </button>';
    }
    return '';
}
?>

<script>
// Message button functionality
function openMessageDialog(recipientId, recipientId2, recipientType, recipientName, recipientAvatar = null) {
    // Open message popup instead of redirecting
    openMessagePopup(recipientId, recipientType, recipientName, recipientAvatar);
}

function showNewMessageDialog(recipientId, recipientType, recipientName) {
    const subject = prompt(`Send a message to ${recipientName}:`, '');
    if (subject && subject.trim()) {
        // Send initial message
        sendInitialMessage(recipientId, recipientType, subject.trim());
    }
}

async function sendInitialMessage(recipientId, recipientType, message) {
    try {
        const response = await fetch('messaging_handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'send_message',
                sender_id: messagingSystem.currentUserId,
                sender_type: messagingSystem.currentUserType,
                receiver_id: recipientId,
                receiver_type: recipientType,
                message: message
            })
        });

        const data = await response.json();
        
        if (data.success) {
            // Reload conversations and select the new one
            await messagingSystem.loadConversations();
            messagingSystem.selectConversation(data.conversation_id);
        } else {
            alert('Failed to send message: ' + data.message);
        }
    } catch (error) {
        console.error('Error sending initial message:', error);
        alert('Network error. Please try again.');
    }
}
</script>
