class ChatManager {
    constructor() {
        this.currentChatId = null;
        this.chats = this.loadChats();
        this.lastActiveChatId = null;
        this.currentBranchId = null;
    }

    loadChats() {
        const chats = this.getCookie('titano_chats');
        this.chats = chats ? JSON.parse(chats) : {};
        return this.chats;
    }

    createNewChat() {
        const chatId = Date.now().toString();
        const newChat = {
            id: chatId,
            title: 'New Chat',
            messages: [],
            model: currentModel,
            timestamp: new Date().toISOString()
        };
        
        // Immediately save the new chat
        this.chats[chatId] = newChat;
        this.currentChatId = chatId;
        this.lastActiveChatId = chatId;
        this.saveChats();
        
        // Update URL with new chat ID
        if (window.updateURL) {
            window.updateURL(chatId);
        }
        
        // Update chat history display
        if (window.updateChatHistory) {
            window.updateChatHistory();
        }
        
        return chatId;
    }

    addMessage(chatId, message, isAI = false, isRegenerated = false, messageId = null) {
        if (!chatId) {
            chatId = this.createNewChat();
        }

        let targetChat = this.chats[chatId];
        if (!targetChat) {
            return false;
        }

        // For AI responses that are regenerated, create a new branch
        if (isAI && isRegenerated) {
            const lastAiMessage = [...targetChat.messages].reverse()
                .find(m => m.isAI);
            
            if (lastAiMessage) {
                // If this is the first regeneration, initialize versions array
                if (!lastAiMessage.versions) {
                    lastAiMessage.versions = [lastAiMessage.content];
                    lastAiMessage.currentVersion = 0;
                }
                
                // Add new version
                lastAiMessage.versions.push(message);
                lastAiMessage.currentVersion = lastAiMessage.versions.length - 1;
                lastAiMessage.content = message;
                
                this.saveChats();
                return true;
            }
        }

        // For new messages (not regenerated), add to current branch
        const branchId = this.currentBranchId || 'main';
        const messages = targetChat.branches?.[branchId] || targetChat.messages;

        // Prevent duplicate messages
        if (this.isDuplicateMessage(chatId, message, isAI)) {
            return false;
        }

        // Add new message
        const newMessage = {
            content: message,
            timestamp: new Date().toISOString(),
            isAI: isAI,
            messageId: messageId || this.uniqid(),
            branchId: branchId,
            aiIndex: isAI ? messages.filter(m => m.isAI).length : undefined,
            versions: isAI ? [message] : undefined,
            currentVersion: isAI ? 0 : undefined
        };

        // Update chat title with first message if needed
        if (messages.length === 0 && !isAI) {
            targetChat.title = message.substring(0, 50) + (message.length > 50 ? '...' : '');
        }

        if (targetChat.branches && branchId !== 'main') {
            targetChat.branches[branchId].push(newMessage);
        } else {
            targetChat.messages.push(newMessage);
        }

        targetChat.timestamp = new Date().toISOString();
        this.saveChats();
        return true;
    }

    messageExists(chatId, messageId) {
        if (!chatId || !messageId) return false;
        const chat = this.chats[chatId];
        if (!chat) return false;
        
        // Check not only messageId but also content duplicates in recent messages
        const duplicateMessage = chat.messages.some(m => {
            return m.messageId === messageId || 
                   (m.isAI && m.messageId && m.content === message);
        });
        
        return duplicateMessage;
    }

    isDuplicateMessage(chatId, message, isAI) {
        const chat = this.chats[chatId];
        if (!chat || !message) return false;

        // For AI messages, check the entire chat history
        if (isAI) {
            return chat.messages.some(m => 
                m.isAI && m.content === message
            );
        }

        // For user messages, only check last 3 messages
        const recentMessages = chat.messages.slice(-3);
        return recentMessages.some(m => 
            m.isAI === isAI && 
            m.content === message
        );
    }

    // Add method to get last active chat messages
    getLastActiveChatMessages() {
        if (this.lastActiveChatId && this.chats[this.lastActiveChatId]) {
            return this.chats[this.lastActiveChatId].messages;
        }
        return null;
    }

    // Add new method to track pending message
    setPendingMessage(chatId, messageId) {
        this.pendingMessageId = { chatId, messageId };
    }

    // Add new method to remove last message from a chat
    removeLastMessage(chatId) {
        const chat = this.chats[chatId] || this.pendingChat;
        if (chat && chat.messages.length > 0) {
            chat.messages.pop();
            this.saveChats();
        }
    }

    getChatHistory(chatId) {
        return this.chats[chatId] || null;
    }

    saveChats() {
        this.setCookie('titano_chats', JSON.stringify(this.chats), 30); // 30 days expiry
    }

    setCookie(name, value, days) {
        const expires = new Date();
        expires.setTime(expires.getTime() + days * 24 * 60 * 60 * 1000);
        document.cookie = `${name}=${encodeURIComponent(value)};expires=${expires.toUTCString()};path=/`;
    }

    getCookie(name) {
        const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
        return match ? decodeURIComponent(match[2]) : null;
    }

    deleteChat(chatId) {
        if (this.chats[chatId]) {
            delete this.chats[chatId];
            this.saveChats();
            return true;
        }
        return false;
    }

    removeMessage(chatId, messageIndex) {
        if (!this.chats[chatId]) return false;
        
        // Remove the message and all subsequent messages
        this.chats[chatId].messages.splice(messageIndex);
        
        // Update chat title if first message was removed
        if (messageIndex === 0) {
            this.chats[chatId].title = 'New Chat';
        }
        
        this.saveChats();
        return true;
    }

    // Gets the message index in a chat
    getMessageIndex(chatId, messageContent) {
        if (!this.chats[chatId]) return -1;
        return this.chats[chatId].messages.findIndex(m => m.content === messageContent);
    }

    navigateVersion(chatId, aiIndex, direction) {
        const chat = this.chats[chatId];
        if (!chat) return null;
    
        // Find the AI message by its aiIndex
        const aiMessage = chat.messages.find(m => m.isAI && m.aiIndex === parseInt(aiIndex));
        if (!aiMessage?.versions?.length) return null;
    
        // Calculate new version index
        const newVersion = (aiMessage.currentVersion || 0) + direction;
        if (newVersion < 0 || newVersion >= aiMessage.versions.length) return null;
    
        // Update message
        aiMessage.currentVersion = newVersion;
        aiMessage.content = aiMessage.versions[newVersion];
    
        // Save changes
        this.saveChats();
    
        // Return updated state
        return {
            content: aiMessage.content,
            currentVersion: newVersion,
            totalVersions: aiMessage.versions.length,
            hasOlder: newVersion > 0,
            hasNewer: newVersion < aiMessage.versions.length - 1
        };
    }

    // Add new method to get grouped chats by date
    getGroupedChats() {
        const chats = Object.values(this.chats);
        const groups = {};

        // Group chats by date
        chats.forEach(chat => {
            const date = new Date(chat.timestamp);
            const today = new Date();
            const yesterday = new Date(today);
            yesterday.setDate(yesterday.getDate() - 1);

            let groupKey;
            if (date.toDateString() === today.toDateString()) {
                groupKey = 'Today';
            } else if (date.toDateString() === yesterday.toDateString()) {
                groupKey = 'Yesterday';
            } else if (date.getTime() > today.getTime() - 7 * 24 * 60 * 60 * 1000) {
                groupKey = 'This Week';
            } else if (date.getMonth() === today.getMonth()) {
                groupKey = 'This Month';
            } else {
                groupKey = new Intl.DateTimeFormat('en-US', { month: 'long', year: 'numeric' }).format(date);
            }

            if (!groups[groupKey]) {
                groups[groupKey] = [];
            }
            groups[groupKey].push(chat);
        });

        // Sort chats within each group
        Object.values(groups).forEach(group => {
            group.sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp));
        });

        return groups;
    }

    // Add method to search chats
    searchChats(query) {
        if (!query) return this.getGroupedChats();

        const matchedChats = Object.values(this.chats).filter(chat => {
            const titleMatch = chat.title.toLowerCase().includes(query.toLowerCase());
            const contentMatch = chat.messages.some(msg => 
                msg.content.toLowerCase().includes(query.toLowerCase())
            );
            return titleMatch || contentMatch;
        });

        return {
            'Search Results': matchedChats.sort((a, b) => 
                new Date(b.timestamp) - new Date(a.timestamp)
            )
        };
    }

    // Add method to format relative time
    formatRelativeTime(timestamp) {
        const date = new Date(timestamp);
        const now = new Date();
        const diff = Math.floor((now - date) / 1000); // difference in seconds

        if (diff < 60) return 'just now';
        if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
        if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
        if (diff < 604800) return `${Math.floor(diff / 86400)}d ago`;
        
        return new Intl.DateTimeFormat('en-US', { 
            month: 'short', 
            day: 'numeric' 
        }).format(date);
    }

    async resumeResponse(chatId) {
        try {
            const response = await fetch(`api/response_handler.php?chat_id=${chatId}`);
            const state = await response.json();
            
            if (!state) return null;

            // Verify this isn't a duplicate of an existing message
            if (state.message_id && this.messageExists(chatId, state.message_id)) {
                return null;
            }

            if (state.status === 'in_progress') {
                return {
                    message: state.user_message,
                    partial: state.content,
                    messageId: state.message_id
                };
            }
        } catch (error) {
            console.error('Error resuming response:', error);
        }
        return null;
    }

    // Add helper function to generate unique IDs
    uniqid() {
        return Date.now().toString(36) + Math.random().toString(36).substr(2);
    }

    // Add method to get messages for current branch
    getCurrentBranchMessages(chatId) {
        const chat = this.chats[chatId];
        if (!chat) return [];

        if (this.currentBranchId && chat.branches?.[this.currentBranchId]) {
            return chat.branches[this.currentBranchId];
        }
        return chat.messages;
    }

    // Add method to switch branches
    switchBranch(chatId, branchId) {
        const chat = this.chats[chatId];
        if (!chat) return false;

        if (branchId === 'main' || chat.branches?.[branchId]) {
            this.currentBranchId = branchId === 'main' ? null : branchId;
            return true;
        }
        return false;
    }
}
