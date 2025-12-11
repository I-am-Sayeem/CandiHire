# Messaging System Implementation

This document describes the messaging system implementation for the CandiHire project.

## Overview

The messaging system allows candidates and companies to communicate directly through the platform. It replaces the trending skills section in both dashboards with a comprehensive messaging interface.

## Features

### Core Functionality
- **Real-time messaging** between candidates and companies
- **Conversation management** with threaded conversations
- **Message search** within conversations
- **Unread message indicators** with badge counts
- **Message buttons** on job posts and candidate profiles
- **Responsive design** that works on all devices

### User Interface
- **Dedicated messaging page** that opens in a new tab/window
- **Conversation list** with preview of last message
- **Message input** with auto-resize textarea
- **Message bubbles** with sender avatars
- **Search functionality** for finding conversations
- **Message buttons** on job posts and candidate profiles

## Files Created/Modified

### New Files
1. `create_messaging_tables.sql` - Database schema for messaging system
2. `messaging_handler.php` - Backend API for all messaging operations
3. `messaging_ui.php` - Frontend UI components and JavaScript (simplified)
4. `messaging_page.php` - Standalone messaging page
5. `message_button_helper.php` - Helper functions for message buttons
6. `setup_messaging_system.php` - Database setup script
7. `MESSAGING_SYSTEM_README.md` - This documentation

### Modified Files
1. `CandidateDashboard.php` - Replaced trending skills with messaging
2. `CompanyDashboard.php` - Replaced trending skills with messaging

## Database Schema

### Tables Created
1. **messages** - Stores all messages
   - MessageID (Primary Key)
   - SenderID, SenderType (candidate/company)
   - ReceiverID, ReceiverType (candidate/company)
   - Subject, Message content
   - IsRead status
   - Timestamps

2. **conversations** - Tracks conversation threads
   - ConversationID (Primary Key)
   - Participant1ID, Participant1Type
   - Participant2ID, Participant2Type
   - LastMessageID, LastMessageAt
   - Timestamps

3. **message_attachments** - For future file sharing
4. **message_read_status** - For read receipts

## Setup Instructions

1. **Run the setup script:**
   ```
   http://your-domain/setup_messaging_system.php
   ```

2. **Verify database tables were created:**
   - Check your MySQL database for the new tables
   - Verify sample data was inserted

3. **Test the system:**
   - Login as a candidate
   - Login as a company (in another browser/incognito)
   - Try sending messages between them

## Usage

### For Candidates
1. **View Messages:** Click the "Messages" button in the right sidebar (opens new page)
2. **Message Companies:** Click "Message" button on any job post (opens messaging page)
3. **Start Conversations:** The system will create a new conversation automatically

### For Companies
1. **View Messages:** Click the "Messages" button in the right sidebar (opens new page)
2. **Message Candidates:** Click "Message" button on any candidate profile (opens messaging page)
3. **Manage Conversations:** View all conversations in the dedicated messaging page

### Message Features
- **Send Messages:** Type in the message input and press Enter or click Send
- **Search Conversations:** Use the search box to find specific conversations
- **View Unread Count:** See badge with number of unread messages
- **Auto-refresh:** Unread count updates every 30 seconds

## API Endpoints

The messaging system provides the following API endpoints:

### GET Endpoints
- `messaging_handler.php?action=get_conversations&user_id=X&user_type=Y`
- `messaging_handler.php?action=get_messages&conversation_id=X&user_id=Y&user_type=Z`
- `messaging_handler.php?action=get_unread_count&user_id=X&user_type=Y`
- `messaging_handler.php?action=search_conversations&user_id=X&user_type=Y&query=Z`

### POST Endpoints
- `messaging_handler.php` with action=send_message
- `messaging_handler.php` with action=mark_as_read
- `messaging_handler.php` with action=delete_message
- `messaging_handler.php` with action=delete_conversation

## Security Features

- **Input validation** on all message content
- **SQL injection protection** using prepared statements
- **XSS prevention** with proper HTML escaping
- **Access control** - users can only access their own conversations
- **CSRF protection** through session validation

## Customization

### Styling
The messaging UI uses CSS custom properties that automatically adapt to the theme:
- Dark/Light theme support
- Responsive design
- Smooth animations and transitions

### Functionality
- Easy to extend with new features
- Modular JavaScript architecture
- Clean separation of concerns

## Troubleshooting

### Common Issues
1. **Messages not loading:** Check database connection and user session
2. **Message buttons not appearing:** Ensure message_button_helper.php is included
3. **Styling issues:** Check that CSS custom properties are defined

### Debug Mode
Enable browser developer tools to see console logs for debugging:
- Message sending/receiving
- Conversation loading
- Error messages

## Future Enhancements

Potential features for future development:
- File attachments in messages
- Message reactions/emojis
- Message forwarding
- Group conversations
- Message encryption
- Push notifications
- Message templates
- Auto-reply functionality

## Support

For issues or questions about the messaging system:
1. Check the browser console for error messages
2. Verify database tables exist and have data
3. Check file permissions and includes
4. Review the API responses in network tab

## Technical Notes

- Uses PDO for database operations
- Implements proper error handling
- Follows RESTful API patterns
- Uses modern JavaScript (ES6+)
- Responsive CSS Grid and Flexbox
- Font Awesome icons for UI elements
