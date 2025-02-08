class ChatManager {
    constructor() {
        this.currentChatId = null;
        this.chats = this.loadChats();
        this.lastActiveChatId = null;
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

    addMessage(chatId, message, isAI = false) {
        // If no chat exists, create one
        if (!chatId) {
            chatId = this.createNewChat();
        }

        let targetChat = this.chats[chatId];
        if (!targetChat) {
            return false;
        }

        // Add the message
        targetChat.messages.push({
            content: message,
            timestamp: new Date().toISOString(),
            isAI: isAI
        });

        // Update chat title based on first user message
        if (!isAI && targetChat.messages.length === 1) {
            targetChat.title = message.slice(0, 30) + (message.length > 30 ? '...' : '');
        }

        // Save changes immediately
        this.saveChats();
        
        // Update chat history display
        if (window.updateChatHistory) {
            window.updateChatHistory();
        }

        return true;
    }

    // Add new method to handle incomplete messages
    addIncompleteMessage(message) {
        const targetChatId = this.lastActiveChatId;
        if (!targetChatId) return false;

        // Always add incomplete messages to the actual chat, not pending
        if (this.chats[targetChatId]) {
            this.chats[targetChatId].messages.push({
                content: message,
                timestamp: new Date().toISOString(),
                isAI: true,
                isIncomplete: true
            });
            this.saveChats();
            return true;
        }
        return false;
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
}
