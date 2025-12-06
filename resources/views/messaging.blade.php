@extends('layouts.app')

@section('content')
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    <input type="hidden" id="currentUserId" value="{{ session('candidate_id') ?? session('company_id') }}">
    <input type="hidden" id="currentUserType" value="{{ session('candidate_id') ? 'candidate' : 'company' }}">
    <input type="hidden" id="currentUserName" value="{{ session('candidate_name') ?? session('company_name') }}">

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
                }
            }

            async loadConversations() {
                // Mock data for demonstration - in real app, replace with fetch call
                // fetch(`/api/conversations?user_id=${this.currentUserId}&type=${this.currentUserType}`)
                
                // Simulating API delay
                if (this.conversations.length === 0) {
                     // Initial mock load
                     this.conversations = [
                         {
                             ConversationID: 1,
                             OtherParticipantID: 101,
                             OtherParticipantType: this.currentUserType === 'candidate' ? 'company' : 'candidate',
                             OtherParticipantName: "Tech Solutions Inc.",
                             OtherParticipantAvatar: "",
                             LastMessage: "When are you available for an interview?",
                             LastMessageTime: new Date(Date.now() - 1000 * 60 * 5).toISOString(), // 5 mins ago
                             UnreadCount: 2
                         },
                         {
                             ConversationID: 2,
                             OtherParticipantID: 102,
                             OtherParticipantType: this.currentUserType === 'candidate' ? 'company' : 'candidate',
                             OtherParticipantName: "Global Systems",
                             OtherParticipantAvatar: "",
                             LastMessage: "Thank you for your application.",
                             LastMessageTime: new Date(Date.now() - 1000 * 60 * 60 * 24).toISOString(), // 1 day ago
                             UnreadCount: 0
                         }
                     ];
                     this.renderConversations();
                } else {
                     // For polling, we would fetch and merge/update
                     // this.renderConversations();
                }
            }

            startRealTimePolling() {
                if (this.isPolling) return;
                this.isPolling = true;
                
                // Poll every 3 seconds
                this.pollInterval = setInterval(() => {
                    this.pollForUpdates();
                }, 3000);
            }

            stopRealTimePolling() {
                if (this.pollInterval) clearInterval(this.pollInterval);
                this.isPolling = false;
            }

            pollForUpdates() {
                // In a real implementation:
                // this.loadConversations();
                // if (this.currentConversationId) this.loadMessages(this.currentConversationId); 
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

            loadMessages(conversationId) {
                const container = document.getElementById('messagesList');
                container.innerHTML = '<div class="loading-messages"><i class="fas fa-spinner fa-spin"></i>Loading...</div>';

                // Mock loading messages
                setTimeout(() => {
                    // Mock messages
                    if (conversationId === 1) {
                         this.messages = [
                             { MessageID: 1, Message: "Hello, I am interested in the job.", SenderID: this.currentUserId, SenderType: this.currentUserType, CreatedAt: new Date(Date.now() - 1000 * 60 * 60).toISOString() },
                             { MessageID: 2, Message: "Hi! Thanks for reaching out.", SenderID: 101, SenderType: 'other', SenderName: "Tech Solutions", CreatedAt: new Date(Date.now() - 1000 * 60 * 30).toISOString() },
                             { MessageID: 3, Message: "When are you available for an interview?", SenderID: 101, SenderType: 'other', SenderName: "Tech Solutions", CreatedAt: new Date(Date.now() - 1000 * 60 * 5).toISOString() }
                         ];
                    } else {
                         this.messages = [
                             { MessageID: 4, Message: "Application received.", SenderID: 102, SenderType: 'other', SenderName: "Global Systems", CreatedAt: new Date(Date.now() - 1000 * 60 * 60 * 24).toISOString() }
                         ];
                    }
                    this.renderMessages();
                }, 500);
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

            sendMessage() {
                const input = document.getElementById('messageInput');
                const text = input.value.trim();
                
                if (!text || !this.currentConversationId) return;

                // Optimistic UI update
                const tempMsg = {
                    MessageID: Date.now(), // temp ID
                    Message: text,
                    SenderID: this.currentUserId,
                    SenderType: this.currentUserType,
                    CreatedAt: new Date().toISOString()
                };
                
                this.messages.push(tempMsg);
                this.renderMessages();
                
                input.value = '';
                this.autoResizeTextarea(input);

                // Mock API call
                console.log('Sending message:', text, 'to conv:', this.currentConversationId);
                // In real app: POST to backend, then update ID or handle error
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
            if (theme === 'dark') {
                icon.className = 'fas fa-sun';
                text.textContent = 'Light Mode';
            } else {
                icon.className = 'fas fa-moon';
                text.textContent = 'Dark Mode';
            }
        }

        // Setup theme toggle
        document.getElementById('themeToggleBtn').addEventListener('click', () => {
            const current = document.documentElement.getAttribute('data-theme');
            const next = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            localStorage.setItem('candihire-theme', next);
            updateThemeButton(next);
        });

        function goBack() {
            window.history.back();
        }

        const messagingSystem = new MessagingPageSystem();
        initializeTheme();
    </script>
</body>
</html>
@endsection
