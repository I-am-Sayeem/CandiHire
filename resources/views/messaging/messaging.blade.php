<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Messages - CandiHire</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/messaging_page.css') }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="messaging-page">
        <!-- Messaging Header -->
        <div class="messaging-header">
                <div class="messaging-title">
                    <i class="fas fa-comments"></i>
                    Messages
                </div>
                <div class="messaging-actions">
                    <div class="messaging-search">
                        <input type="text" placeholder="Search conversations..." id="searchConversations">
                        <i class="fas fa-search"></i>
                    </div>
                    
                    <button id="themeToggleBtn" class="theme-toggle-btn" title="Switch to Light Mode">
                        <i class="fas fa-moon" id="themeIcon"></i>
                        <span id="themeText">Dark Mode</span>
                    </button>
                    
                    <button class="back-button" onclick="goBack()">
                        <i class="fas fa-arrow-left"></i> Back
                    </button>
                </div>
        </div>

        <div class="messaging-content">
            <!-- Conversations Sidebar -->
            <div class="conversations-sidebar">
                <div class="conversations-header">
                    <h3>Recent Conversations</h3>
                </div>
                <div class="conversations-list" id="conversationsList">
                    <!-- Conversations will be loaded here via JS -->
                    <div class="loading-messages"><i class="fas fa-spinner fa-spin"></i>Loading...</div>
                </div>
            </div>

            <!-- Messages Area -->
            <div class="messages-area" id="messagesArea">
                <div class="messages-header" id="messagesHeader" style="display: none;">
                    <button class="back-button" style="padding: 8px 12px; margin-right: 10px; min-width: auto; display: none;" id="mobileBackBtn">
                        <i class="fas fa-arrow-left"></i>
                    </button>
                    <div class="messages-avatar" id="messagesAvatar"></div>
                    <div class="messages-info">
                        <div class="messages-name" id="messagesName">Select a conversation</div>
                        <div class="messages-status" id="messagesStatus" style="font-size: 12px; color: var(--text-secondary);"></div>
                    </div>
                </div>

                <div class="messages-list" id="messagesList">
                    <div class="no-conversation">
                        <i class="fas fa-comment-dots"></i>
                        <h3>Select a conversation</h3>
                        <p>Choose a contact from the left to start messaging</p>
                    </div>
                </div>

                <div class="message-input-area" id="messageInputArea" style="display: none;">
                    <form class="message-input-form" onsubmit="event.preventDefault(); messagingSystem.sendMessage();">
                        <textarea class="message-input" id="messageInput" placeholder="Type a message..." rows="1" oninput="messagingSystem.autoResizeTextarea(this)"></textarea>
                        <button type="submit" class="send-button" id="sendMessageBtn">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden inputs for user context -->
    <input type="hidden" id="currentUserId" value="{{ session('user_id') }}">
    <input type="hidden" id="currentUserType" value="{{ session('user_type') ?? 'candidate' }}">
    <input type="hidden" id="currentUserName" value="{{ session('user_name') ?? '' }}">

    <script>
        // Inject data from Laravel FIRST (before class initialization)
        window.initialConversations = @json($conversationsJson ?? []);
        window.initialConversationId = @json($currentConversationJson['ConversationID'] ?? null);
        window.initialMessages = @json($messagesJson ?? []);
    </script>
    <script>
        class MessagingPageSystem {
            constructor() {
                this.currentUserId = document.getElementById('currentUserId').value;
                this.currentUserType = document.getElementById('currentUserType').value;
                this.currentConversationId = null;
                this.pollInterval = null;
                this.isPolling = false;
                this.conversations = [];
                this.messages = [];
                this.lastMessageId = 0;
                this.typingTimeout = null;
                this.isTyping = false;
                this.otherPersonTyping = false;
                
                this.init();
            }

            init() {
                this.loadConversations();
                this.setupEventListeners();
                this.startRealTimePolling();
                
                // Mobile responsive handling
                if (window.innerWidth <= 768) {
                    document.getElementById('mobileBackBtn').style.display = 'block';
                    document.getElementById('mobileBackBtn').addEventListener('click', () => {
                        document.getElementById('messagesArea').classList.remove('active');
                    });
                }
            }

            setupEventListeners() {
                const searchInput = document.getElementById('searchConversations');
                if (searchInput) {
                    searchInput.addEventListener('input', (e) => {
                        this.searchConversations(e.target.value);
                    });
                }
                
                // Enter key to send (Shift+Enter for new line)
                const messageInput = document.getElementById('messageInput');
                if (messageInput) {
                    messageInput.addEventListener('keydown', (e) => {
                        if (e.key === 'Enter' && !e.shiftKey) {
                            e.preventDefault();
                            this.sendMessage();
                        }
                    });
                    
                    // Typing indicator - detect when user is typing
                    messageInput.addEventListener('input', () => {
                        this.onTyping();
                    });
                }
            }

            // Send typing status to server
            async onTyping() {
                if (!this.currentConversationId) return;
                
                // Clear previous timeout
                if (this.typingTimeout) {
                    clearTimeout(this.typingTimeout);
                }
                
                // Send typing status (send every time to keep it active on server)
                if (!this.isTyping) {
                    this.isTyping = true;
                }
                // Always send to refresh the server cache
                this.sendTypingStatus(true);
                
                // Clear typing status after 2 seconds of no typing
                this.typingTimeout = setTimeout(async () => {
                    this.isTyping = false;
                    await this.sendTypingStatus(false);
                }, 2000);
            }

            async sendTypingStatus(isTyping) {
                try {
                    await fetch('/api/typing/set', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            conversation_id: this.currentConversationId,
                            is_typing: isTyping
                        })
                    });
                } catch(e) {
                    console.warn('Error sending typing status:', e);
                }
            }

            async checkTypingStatus() {
                if (!this.currentConversationId) return;
                
                try {
                    const response = await fetch(`/api/typing/${this.currentConversationId}`);
                    const data = await response.json();
                    
                    if (data.success) {
                        const wasTyping = this.otherPersonTyping;
                        this.otherPersonTyping = data.is_typing;
                        
                        // Update UI if typing status changed
                        if (wasTyping !== this.otherPersonTyping) {
                            this.updateTypingIndicator();
                        }
                    }
                } catch(e) {
                    console.warn('Error checking typing status:', e);
                }
            }

            updateTypingIndicator() {
                const statusEl = document.getElementById('messagesStatus');
                
                if (statusEl) {
                    if (this.otherPersonTyping) {
                        statusEl.innerHTML = '<span class="typing-dots"><span></span><span></span><span></span></span> typing...';
                        statusEl.classList.add('typing-active');
                    } else {
                        statusEl.innerHTML = '';
                        statusEl.classList.remove('typing-active');
                    }
                }
            }

            async loadConversations() {
                // Data is passed from Laravel controller
                console.log('Loading conversations. Initial data:', window.initialConversations);
                
                if (this.conversations.length === 0 && window.initialConversations && window.initialConversations.length > 0) {
                     this.conversations = window.initialConversations;
                     this.renderConversations();
                } else if (this.conversations.length === 0) {
                    // Fetch via AJAX if not preloaded
                    try {
                        const response = await fetch('/api/conversations');
                        const data = await response.json();
                        console.log('API conversations response:', data);
                        
                        if (data.success && data.conversations) {
                            this.conversations = data.conversations;
                        } else {
                            this.conversations = [];
                        }
                    } catch(e) {
                        console.warn('Could not load conversations', e);
                        this.conversations = [];
                    }
                    // Always render after fetching (even if empty)
                    this.renderConversations();
                } else {
                     // For polling, re-render
                     this.renderConversations();
                }
            }


            startRealTimePolling() {
                if (this.isPolling) return;
                this.isPolling = true;
                
                // Poll every 1 second for faster updates
                this.pollInterval = setInterval(() => {
                    this.pollForUpdates();
                }, 1000);
            }

            stopRealTimePolling() {
                if (this.pollInterval) clearInterval(this.pollInterval);
                this.isPolling = false;
            }

            async pollForUpdates() {
                try {
                    // Refresh conversations list
                    const convResponse = await fetch('/api/conversations');
                    if (convResponse.ok) {
                        const convData = await convResponse.json();
                        if (convData.success && convData.conversations) {
                            this.conversations = convData.conversations;
                            this.renderConversations();
                        }
                    }
                    
                    // Refresh current conversation messages
                    if (this.currentConversationId) {
                        const msgResponse = await fetch(`/api/messages/${this.currentConversationId}`);
                        if (msgResponse.ok) {
                            const msgData = await msgResponse.json();
                            if (msgData.success && msgData.messages) {
                                // Only update if message count changed
                                if (msgData.messages.length !== this.messages.length) {
                                    this.messages = msgData.messages;
                                    this.renderMessages();
                                }
                            }
                        }
                        
                        // Check if other person is typing
                        await this.checkTypingStatus();
                    }
                } catch(e) {
                    console.warn('Polling error:', e);
                }
            }

            renderConversations() {
                const container = document.getElementById('conversationsList');
                if (!container) return;

                if (this.conversations.length === 0) {
                    container.innerHTML = `
                        <div class="no-conversation">
                            <i class="fas fa-comment-dots"></i>
                            <h3>No conversations yet</h3>
                            <p>Start a conversation by messaging someone</p>
                        </div>
                    `;
                    return;
                }

                container.innerHTML = this.conversations.map(conv => {
                    const isActive = this.currentConversationId === conv.ConversationID ? 'active' : '';
                    const avatar = conv.OtherParticipantAvatar ? '' : conv.OtherParticipantName.charAt(0).toUpperCase();
                    const bgImage = conv.OtherParticipantAvatar ? `background-image: url('${conv.OtherParticipantAvatar}'); background-size: cover;` : '';
                    
                    return `
                    <div class="conversation-item ${isActive}" onclick="messagingSystem.selectConversation(${conv.ConversationID})">
                        <div class="conversation-avatar" style="${bgImage}">
                            ${avatar}
                        </div>
                        <div class="conversation-info">
                            <div class="conversation-name">${conv.OtherParticipantName}</div>
                            <div class="conversation-preview">${conv.LastMessage}</div>
                        </div>
                        <div class="conversation-meta">
                            <div class="conversation-time">${this.formatTime(conv.LastMessageTime)}</div>
                            ${conv.UnreadCount > 0 ? `<div class="unread-badge">${conv.UnreadCount}</div>` : ''}
                        </div>
                    </div>
                `}).join('');
            }

            selectConversation(conversationId) {
                this.currentConversationId = conversationId;
                
                // Update UI active state
                document.querySelectorAll('.conversation-item').forEach(el => el.classList.remove('active'));
                // Note: In real scenarios, finding by ID attributes is safer
                // But re-rendering works too
                this.renderConversations(); 

                this.showMessagesArea();
                this.loadMessages(conversationId);
                
                // Mobile handling
                if (window.innerWidth <= 768) {
                    document.getElementById('messagesArea').classList.add('active');
                }
            }

            async loadMessages(conversationId, showLoading = true) {
                const container = document.getElementById('messagesList');
                if (showLoading) {
                    container.innerHTML = '<div class="loading-messages"><i class="fas fa-spinner fa-spin"></i>Loading...</div>';
                }

                // Load messages from API
                try {
                    const response = await fetch(`/api/messages/${conversationId}`);
                    if (response.ok) {
                        const data = await response.json();
                        if (data.success && data.messages) {
                            this.messages = data.messages;
                            this.renderMessages();
                        } else {
                            this.messages = [];
                            this.renderMessages();
                        }
                    } else {
                        // Try preloaded
                        if (window.initialMessages && window.initialMessages.length > 0 && window.initialConversationId == conversationId) {
                            this.messages = window.initialMessages;
                        } else {
                            this.messages = [];
                        }
                        this.renderMessages();
                    }
                } catch(e) {
                    console.warn('Could not load messages', e);
                    // Fallback to preloaded if available
                    if (window.initialMessages && window.initialConversationId == conversationId) {
                        this.messages = window.initialMessages;
                    } else {
                        this.messages = [];
                    }
                    this.renderMessages();
                }
            }

            renderMessages() {
                const container = document.getElementById('messagesList');
                const conv = this.conversations.find(c => c.ConversationID === this.currentConversationId);
                
                if (conv) {
                    document.getElementById('messagesName').textContent = conv.OtherParticipantName;
                    const avatarEl = document.getElementById('messagesAvatar');
                    avatarEl.textContent = conv.OtherParticipantAvatar ? '' : conv.OtherParticipantName.charAt(0).toUpperCase();
                    if(conv.OtherParticipantAvatar) {
                        avatarEl.style.backgroundImage = `url('${conv.OtherParticipantAvatar}')`;
                        avatarEl.style.backgroundSize = 'cover';
                    } else {
                        avatarEl.style.backgroundImage = 'none';
                    }
                }

                if (this.messages.length === 0) {
                    container.innerHTML = `
                        <div class="no-conversation">
                            <i class="fas fa-comment-dots"></i>
                            <h3>No messages yet</h3>
                            <p>Start the conversation by sending a message</p>
                        </div>
                    `;
                    return;
                }

                container.innerHTML = this.messages.map(msg => {
                    const isSent = (msg.SenderID == this.currentUserId && msg.SenderType == this.currentUserType);
                    // Use simpler avatar logic for messages for now
                    const avatarInitial = isSent ? 'Me' : (msg.SenderName ? msg.SenderName.charAt(0) : 'U');
                    
                    return `
                        <div class="message-item ${isSent ? 'sent' : ''}">
                            <div class="message-avatar">${avatarInitial}</div>
                            <div class="message-content">
                                <p class="message-text">${msg.Message}</p>
                                <div class="message-time">${this.formatTime(msg.CreatedAt)}</div>
                            </div>
                        </div>
                    `;
                }).join('');

                this.scrollToBottom();
            }

            async sendMessage() {
                const input = document.getElementById('messageInput');
                const text = input.value.trim();
                
                if (!text || !this.currentConversationId) return;

                // Optimistic UI update
                const tempMsg = {
                    MessageID: Date.now(),
                    Message: text,
                    SenderID: this.currentUserId,
                    SenderType: this.currentUserType,
                    CreatedAt: new Date().toISOString()
                };
                
                this.messages.push(tempMsg);
                this.renderMessages();
                
                input.value = '';
                this.autoResizeTextarea(input);

                // POST to backend
                try {
                    const response = await fetch('/messages/send', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            ConversationID: this.currentConversationId,
                            MessageText: text
                        })
                    });
                    
                    const data = await response.json();
                    if (data.success) {
                        console.log('Message sent successfully');
                        // Refresh messages to get the real ID (without showing loading spinner)
                        await this.loadMessages(this.currentConversationId, false);
                    } else {
                        console.error('Failed to send message:', data.message);
                    }
                } catch(e) {
                    console.error('Error sending message:', e);
                }
            }

            searchConversations(query) {
                // Filter this.conversations based on query
                // In real app: call API search endpoint
                if (!query) {
                    this.loadConversations(); // restore
                    return;
                }
                const filtered = this.conversations.filter(c => c.OtherParticipantName.toLowerCase().includes(query.toLowerCase()));
                // Temporarily render filtered without overwriting main list if we want, or just mock:
                const original = [...this.conversations];
                this.conversations = filtered;
                this.renderConversations();
                this.conversations = original; // restore for next search
            }

            showMessagesArea() {
                document.getElementById('messagesHeader').style.display = 'flex';
                document.getElementById('messageInputArea').style.display = 'block';
            }

            scrollToBottom() {
                const container = document.getElementById('messagesList');
                container.scrollTop = container.scrollHeight;
            }

            autoResizeTextarea(textarea) {
                textarea.style.height = 'auto';
                textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
            }

            formatTime(timestamp) {
                if (!timestamp) return '';
                const date = new Date(timestamp);
                const now = new Date();
                const diff = now - date;
                
                if (diff < 60000) return 'Just now';
                if (diff < 3600000) return Math.floor(diff/60000) + 'm ago';
                if (diff < 86400000) {
                     return date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                }
                return date.toLocaleDateString();
            }
        }

        // Initialize theme
        function initializeTheme() {
            const savedTheme = localStorage.getItem('candihire-theme') || 'dark';
            document.documentElement.setAttribute('data-theme', savedTheme);
            updateThemeButton(savedTheme);
        }

        function updateThemeButton(theme) {
            const icon = document.getElementById('themeIcon');
            const text = document.getElementById('themeText');
            if (icon && text) {
                if (theme === 'dark') {
                    icon.className = 'fas fa-sun';
                    text.textContent = 'Light Mode';
                } else {
                    icon.className = 'fas fa-moon';
                    text.textContent = 'Dark Mode';
                }
            }
        }

        function goBack() {
            const userType = document.getElementById('currentUserType').value;
            if (userType === 'company') {
                window.location.href = '/company/dashboard';
            } else {
                window.location.href = '/candidate/dashboard';
            }
        }

        // Setup theme toggle with null check
        function setupThemeToggle() {
            const themeBtn = document.getElementById('themeToggleBtn');
            if (themeBtn) {
                themeBtn.addEventListener('click', () => {
                    const current = document.documentElement.getAttribute('data-theme');
                    const next = current === 'dark' ? 'light' : 'dark';
                    document.documentElement.setAttribute('data-theme', next);
                    localStorage.setItem('candihire-theme', next);
                    updateThemeButton(next);
                });
            }
        }

        // Initialize everything
        try {
            initializeTheme();
            setupThemeToggle();
        } catch(e) {
            console.error('Error initializing theme:', e);
        }

        let messagingSystem;
        try {
            messagingSystem = new MessagingPageSystem();
        } catch(e) {
            console.error('Error initializing MessagingPageSystem:', e);
        }
        
        // If we have a current conversation preloaded, select it
        if (window.initialConversationId && messagingSystem) {
            messagingSystem.currentConversationId = window.initialConversationId;
            messagingSystem.conversations = window.initialConversations;
            messagingSystem.messages = window.initialMessages;
            messagingSystem.renderConversations();
            messagingSystem.showMessagesArea();
            messagingSystem.renderMessages();
        }
    </script>
</body>
</html>
