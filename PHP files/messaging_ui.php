<?php
// messaging_ui.php - Messaging UI component
// This file contains the HTML and CSS for the messaging interface
?>

<!-- Messaging UI Styles -->
<style>
/* Messaging UI Styles - Simplified for button only */
</style>

<!-- Messaging UI - Now opens in new page -->

<!-- Message Button Component -->
<button id="openMessagingBtn" class="btn btn-primary" style="display: flex; align-items: center; gap: 8px; padding: 10px 16px;" onclick="openMessagingPage()">
    <i class="fas fa-comments"></i>
    Messages
    <span id="unreadCount" class="unread-badge" style="display: none;">0</span>
</button>

<script>
// Messaging System JavaScript
class MessagingSystem {
    constructor() {
        this.currentUserId = null;
        this.currentUserType = null;
        this.isInitialized = false;
        
        this.initializeEventListeners();
    }

    initialize(userId, userType) {
        this.currentUserId = userId;
        this.currentUserType = userType;
        this.isInitialized = true;
        
        // Load unread count only
        this.loadUnreadCount();
        
        // Set up auto-refresh
        setInterval(() => {
            if (this.isInitialized) {
                this.loadUnreadCount();
            }
        }, 30000); // Check every 30 seconds
    }

    initializeEventListeners() {
        // No event listeners needed for popup since we're opening a new page
    }


    async loadUnreadCount() {
        try {
            const response = await fetch(`messaging_handler.php?action=get_unread_count&user_id=${this.currentUserId}&user_type=${this.currentUserType}`);
            const data = await response.json();
            
            if (data.success) {
                const unreadCount = data.unread_count;
                const badge = document.getElementById('unreadCount');
                
                if (unreadCount > 0) {
                    badge.textContent = unreadCount;
                    badge.style.display = 'inline-block';
                } else {
                    badge.style.display = 'none';
                }
            }
        } catch (error) {
            console.error('Error loading unread count:', error);
        }
    }
}

// Initialize messaging system
const messagingSystem = new MessagingSystem();

// Function to open messaging page
function openMessagingPage() {
    window.location.href = 'messaging_page.php';
}
</script>
