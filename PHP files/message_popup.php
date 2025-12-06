<?php
// message_popup.php - Message popup modal component
?>

<!-- Message Popup Modal -->
<div id="messagePopup" class="message-popup-overlay" style="display: none;">
    <div class="message-popup-content">
        <div class="message-popup-header">
            <div class="message-popup-title">
                <i class="fas fa-comment"></i>
                Send Message
            </div>
            <button class="message-popup-close" onclick="closeMessagePopup()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="message-popup-body">
            <div class="message-recipient-info" id="messageRecipientInfo">
                <div class="recipient-avatar" id="recipientAvatar"></div>
                <div class="recipient-details">
                    <div class="recipient-name" id="recipientName"></div>
                    <div class="recipient-type" id="recipientType"></div>
                </div>
            </div>
            
            <form id="messagePopupForm">
                <input type="hidden" id="popupRecipientId" name="recipient_id">
                <input type="hidden" id="popupRecipientType" name="recipient_type">
                
                <div class="form-group">
                    <label for="popupMessage">Your Message *</label>
                    <textarea 
                        id="popupMessage" 
                        name="message" 
                        placeholder="Type your message here..."
                        required
                        rows="4"
                    ></textarea>
                </div>
                
                <div class="message-popup-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeMessagePopup()">
                        <i class="fas fa-times"></i>
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="sendMessageBtn">
                        <i class="fas fa-paper-plane"></i>
                        Send Message
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Message Popup Styles */
.message-popup-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 10000;
    backdrop-filter: blur(5px);
}

.message-popup-content {
    background: var(--bg-secondary);
    border-radius: 16px;
    width: 90%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
    border: 1px solid var(--border);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    animation: messagePopupSlideIn 0.3s ease-out;
}

.message-popup-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 25px;
    border-bottom: 1px solid var(--border);
    background: var(--bg-tertiary);
    border-radius: 16px 16px 0 0;
}

.message-popup-title {
    font-size: 20px;
    font-weight: 600;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 10px;
}

.message-popup-title i {
    color: var(--accent-1);
    font-size: 18px;
}

.message-popup-close {
    background: none;
    border: none;
    color: var(--text-secondary);
    font-size: 20px;
    cursor: pointer;
    padding: 8px;
    border-radius: 50%;
    transition: all 0.2s;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.message-popup-close:hover {
    background: var(--bg-primary);
    color: var(--text-primary);
    transform: scale(1.1);
}

.message-popup-body {
    padding: 25px;
}

.message-recipient-info {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    background: var(--bg-primary);
    border-radius: 12px;
    border: 1px solid var(--border);
    margin-bottom: 20px;
}

.recipient-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: var(--accent-2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 18px;
    flex-shrink: 0;
}

.recipient-details {
    flex: 1;
}

.recipient-name {
    font-weight: 600;
    font-size: 16px;
    color: var(--text-primary);
    margin-bottom: 4px;
}

.recipient-type {
    font-size: 14px;
    color: var(--text-secondary);
    text-transform: capitalize;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    color: var(--text-primary);
    font-weight: 500;
    margin-bottom: 8px;
    font-size: 14px;
}

.form-group textarea {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid var(--border);
    border-radius: 12px;
    background: var(--bg-primary);
    color: var(--text-primary);
    font-size: 15px;
    font-family: inherit;
    resize: vertical;
    min-height: 100px;
    transition: all 0.3s ease;
    box-sizing: border-box;
}

.form-group textarea:focus {
    outline: none;
    border-color: var(--accent-1);
    box-shadow: 0 0 0 3px rgba(88, 166, 255, 0.1);
}

.form-group textarea::placeholder {
    color: var(--text-secondary);
}

.message-popup-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 25px;
    padding-top: 20px;
    border-top: 1px solid var(--border);
}

.message-popup-actions .btn {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 120px;
    justify-content: center;
}

.message-popup-actions .btn-secondary {
    background: var(--bg-tertiary);
    color: var(--text-primary);
    border: 1px solid var(--border);
}

.message-popup-actions .btn-secondary:hover {
    background: var(--bg-primary);
    transform: translateY(-1px);
}

.message-popup-actions .btn-primary {
    background: linear-gradient(135deg, var(--accent-1), var(--accent-hover));
    color: white;
    box-shadow: 0 4px 12px rgba(88, 166, 255, 0.3);
}

.message-popup-actions .btn-primary:hover {
    background: linear-gradient(135deg, var(--accent-hover), var(--accent-1));
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(88, 166, 255, 0.4);
}

.message-popup-actions .btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none !important;
}

@keyframes messagePopupSlideIn {
    from {
        opacity: 0;
        transform: scale(0.9) translateY(-20px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

/* Responsive Design */
@media (max-width: 768px) {
    .message-popup-content {
        width: 95%;
        margin: 10px;
    }
    
    .message-popup-header {
        padding: 15px 20px;
    }
    
    .message-popup-body {
        padding: 20px;
    }
    
    .message-popup-actions {
        flex-direction: column;
    }
    
    .message-popup-actions .btn {
        width: 100%;
    }
}
</style>

<script>
// Message Popup JavaScript
function openMessagePopup(recipientId, recipientType, recipientName, recipientAvatar = null) {
    console.log('Opening message popup for:', { recipientId, recipientType, recipientName });
    
    // Set recipient information
    document.getElementById('popupRecipientId').value = recipientId;
    document.getElementById('popupRecipientType').value = recipientType;
    document.getElementById('recipientName').textContent = recipientName;
    document.getElementById('recipientType').textContent = recipientType;
    
    // Set avatar
    const avatarElement = document.getElementById('recipientAvatar');
    if (recipientAvatar) {
        avatarElement.style.backgroundImage = `url('${recipientAvatar}')`;
        avatarElement.style.backgroundSize = 'cover';
        avatarElement.style.backgroundPosition = 'center';
        avatarElement.textContent = '';
    } else {
        avatarElement.style.backgroundImage = '';
        avatarElement.style.background = 'var(--accent-2)';
        avatarElement.textContent = recipientName.charAt(0).toUpperCase();
    }
    
    // Clear previous message
    document.getElementById('popupMessage').value = '';
    
    // Show popup
    document.getElementById('messagePopup').style.display = 'flex';
    document.body.style.overflow = 'hidden';
    
    // Focus on message input
    setTimeout(() => {
        document.getElementById('popupMessage').focus();
    }, 100);
}

function closeMessagePopup() {
    document.getElementById('messagePopup').style.display = 'none';
    document.body.style.overflow = 'auto';
    
    // Reset form
    document.getElementById('messagePopupForm').reset();
}

// Handle form submission
document.getElementById('messagePopupForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const recipientId = document.getElementById('popupRecipientId').value;
    const recipientType = document.getElementById('popupRecipientType').value;
    const message = document.getElementById('popupMessage').value.trim();
    
    if (!message) {
        alert('Please enter a message');
        return;
    }
    
    const sendBtn = document.getElementById('sendMessageBtn');
    const originalContent = sendBtn.innerHTML;
    
    // Disable button and show loading
    sendBtn.disabled = true;
    sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
    
    try {
        const response = await fetch('messaging_handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'send_message',
                sender_id: getCurrentUserId(),
                sender_type: getCurrentUserType(),
                receiver_id: recipientId,
                receiver_type: recipientType,
                message: message
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Show success message
            showMessageSuccess('Message sent successfully!');
            
            // Close popup
            closeMessagePopup();
            
            // Redirect to messaging page after a short delay
            setTimeout(() => {
                window.location.href = 'messaging_page.php';
            }, 1500);
        } else {
            showMessageError('Failed to send message: ' + (data.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error sending message:', error);
        showMessageError('Network error. Please try again.');
    } finally {
        // Re-enable button
        sendBtn.disabled = false;
        sendBtn.innerHTML = originalContent;
    }
});

// Helper functions
function getCurrentUserId() {
    // This should be set by the parent page
    return window.currentUserId || 1;
}

function getCurrentUserType() {
    // This should be set by the parent page
    return window.currentUserType || 'candidate';
}

function showMessageSuccess(message) {
    showMessageNotification(message, 'success');
}

function showMessageError(message) {
    showMessageNotification(message, 'error');
}

function showMessageNotification(message, type) {
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? 'var(--success)' : 'var(--danger)'};
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        z-index: 10001;
        font-weight: 500;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        animation: slideIn 0.3s ease-out;
        max-width: 300px;
    `;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, type === 'success' ? 3000 : 5000);
}

// Close popup when clicking outside
document.getElementById('messagePopup').addEventListener('click', function(e) {
    if (e.target === this) {
        closeMessagePopup();
    }
});

// Close popup with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && document.getElementById('messagePopup').style.display === 'flex') {
        closeMessagePopup();
    }
});
</script>
