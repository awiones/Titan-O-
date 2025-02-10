<?php
session_start();
require_once 'config/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Titano AI Chat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/chat_manager.js"></script>
    <script src="assets/js/theme.js"></script>
    <script src="assets/js/font.js"></script>
    <script src="assets/js/lang.js"></script>
</head>
<body>
    <div class="chat-container">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <!-- Title and Toggle Button -->
            <div class="titan-header">
                <button class="sidebar-toggle" id="sidebar-toggle">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div class="titan-title">Titan 'O'</div>
            </div>
            
            <!-- New Chat Button -->
            <button class="new-chat-btn" id="new-chat-btn">
                <i class="fas fa-plus"></i> <span>New Chat</span>
            </button>

            <!-- Chat History -->
            <div class="chat-history" id="chat-history">
                <div id="chat-history-content"></div>
            </div>
            
            <!-- Bottom Actions Container -->
            <div class="sidebar-bottom-actions">
                <button class="settings-btn theme-toggle">
                    <i class="fas fa-moon"></i>
                </button>
                <a href="settings.php" class="settings-btn">
                    <i class="fas fa-cog"></i>
                    <span data-i18n="settings">Settings</span>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content" id="main-content">
            <!-- Add Model Selector -->
            <div class="model-selector-container">
                <div class="select-group">
                    <select id="model-selector" class="model-selector">
                        <option value="">Loading models...</option>
                    </select>
                    <button id="refresh-models" class="refresh-models-btn" title="Refresh models list">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
                <button id="add-model-btn" class="add-model-btn">
                    <i class="fas fa-plus"></i> Add Model
                </button>
            </div>

            <!-- Add Model Modal -->
            <div class="modal fade" id="addModelModal" tabindex="-1" aria-labelledby="addModelModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addModelModalLabel">Add New Model</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="addModelForm">
                                <div class="model-input-group">
                                    <label for="modelName">Model Name</label>
                                    <div class="input-container">
                                        <input type="text" id="modelName" class="form-control" placeholder="Enter model name (e.g., llama2 or TheBloke/Llama-2-7B)">
                                        <button type="button" class="check-btn" id="checkModel">
                                            <i class="fas fa-search"></i> Check
                                        </button>
                                    </div>
                                </div>
                                <div id="modelStatus" style="display: none;"></div>
                                <div id="downloadProgress" class="mt-3" style="display: none;">
                                    <div class="progress mb-2">
                                        <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span id="progressText">Starting download...</span>
                                        <button type="button" class="btn btn-danger btn-sm" id="cancelDownload" style="display: none;">
                                            <i class="fas fa-times"></i> Cancel
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" id="saveModel" disabled>Pull Model</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="chat-messages" id="chat-messages">
                <div class="welcome-message" style="
                    display: flex;
                    height: 100%;
                    justify-content: center;
                    align-items: center;
                    text-align: center;
                    color: var(--text-color);
                    font-size: 1.5rem;
                    opacity: 0.8;
                ">
                    Chat with your local ai now, <span id="username-display"></span>!
                </div>
            </div>

            <!-- Input Container -->
            <div class="input-container">
                <form id="chat-form">
                    <div class="input-group">
                        <textarea 
                            class="form-control message-input" 
                            placeholder="Type your message here..."
                            rows="1" 
                            id="message-input"
                        ></textarea>
                        <button type="button" class="stop-button" id="stop-button" style="display: none;">
                            <i class="fas fa-stop"></i>
                        </button>
                        <button type="submit" class="send-button" disabled>
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Chat Confirmation Modal -->
    <div class="modal fade" id="deleteChatModal" tabindex="-1" aria-labelledby="deleteChatModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteChatModalLabel">Delete Chat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    this action will have consequences...
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteChat">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const themeManager = new ThemeManager();
        const fontManager = new FontManager();
        const langManager = new LanguageManager();

        // Load user settings
        async function loadUserSettings() {
            try {
                const response = await fetch('api/get_settings.php');
                const settings = await response.json();
                
                if (settings.font_size) {
                    fontManager.setFontSize(settings.font_size);
                }
                if (settings.theme) {
                    themeManager.setTheme(settings.theme);
                }
                if (settings.language) {
                    await langManager.setLanguage(settings.language);
                }
            } catch (error) {
                console.error('Error loading settings:', error);
            }
        }

        // Call this when the page loads
        document.addEventListener('DOMContentLoaded', loadUserSettings);

        // Update the selector in the click event listener
        document.querySelector('.theme-toggle').addEventListener('click', function() {
            const currentTheme = themeManager.getCurrentTheme();
            if (currentTheme === 'system') {
                themeManager.setTheme(themeManager.isDarkMode() ? 'light' : 'dark');
            } else {
                themeManager.setTheme(currentTheme === 'dark' ? 'light' : 'dark');
            }
            updateThemeIcon();
        });

        function updateThemeIcon() {
            const icon = document.querySelector('.theme-toggle i');
            icon.className = themeManager.isDarkMode() ? 'fas fa-sun' : 'fas fa-moon';
        }

        // Update theme icon on theme changes
        window.addEventListener('themechange', updateThemeIcon);
        updateThemeIcon();

        let currentModel = 'llama2';
        const modelSelector = document.getElementById('model-selector');

        // Add this near the top of your script section, after variable declarations
        let currentAbortController = null;

        // Add page unload handler
        window.addEventListener('beforeunload', () => {
            if (currentAbortController) {
                currentAbortController.abort();
            }
        });

        // Add this function to update URL with chat ID
        function updateURL(chatId) {
            if (chatId) {
                const newUrl = `${window.location.pathname}?chat=${chatId}`;
                window.history.pushState({ chatId }, '', newUrl);
            } else {
                window.history.pushState({}, '', window.location.pathname);
            }
        }

        // Fetch available models
        async function fetchModels() {
            try {
                const response = await fetch('api/get_models.php');
                const data = await response.json();
                
                // Clear loading option
                modelSelector.innerHTML = '';
                
                // Add models to selector
                data.models.forEach(model => {
                    const option = document.createElement('option');
                    option.value = model.name;
                    option.textContent = model.name;
                    modelSelector.appendChild(option);
                });

                // Set default model
                if (data.models.length > 0) {
                    currentModel = data.models[0].name;
                    modelSelector.value = currentModel;
                }
            } catch (error) {
                console.error('Error fetching models:', error);
                modelSelector.innerHTML = '<option value="">Error loading models</option>';
            }
        }

        // Replace the model selection event listener
        modelSelector.addEventListener('change', function() {
            const oldModel = currentModel;
            currentModel = this.value;
            
            // Create and show notification
            const notification = document.createElement('div');
            notification.className = 'model-switch-notification';
            notification.innerHTML = `
                <i class="fas fa-exchange-alt"></i>
                <div class="notification-content">
                    <div class="notification-title">Model Switched</div>
                    <div class="notification-text">Changed from ${oldModel} to ${currentModel}</div>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            // Trigger animation
            setTimeout(() => notification.classList.add('show'), 100);
            
            // Remove notification after animation
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        });

        const messageInput = document.getElementById('message-input');
        const sendButton = document.querySelector('.send-button');
        const chatForm = document.getElementById('chat-form');
        const newChatBtn = document.getElementById('new-chat-btn');
        const chatHistoryItems = document.querySelectorAll('.chat-history-item');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('main-content');
        const sidebarToggle = document.getElementById('sidebar-toggle');

        // Initialize chat manager
        const chatManager = new ChatManager();

        // Add isGenerating flag to track AI response status
        let isGenerating = false;

        // Update sendMessage function to remove incomplete response handling
        async function sendMessage(message = null, isRegenerate = false, resume = false) {
            const messageToSend = message || messageInput.value.trim();
            
            if (messageToSend) {
                // Set generating flag to true
                isGenerating = true;

                // Show stop button and hide send button
                document.querySelector('.send-button').style.display = 'none';
                const stopButton = document.querySelector('.stop-button');
                stopButton.style.display = 'flex';
                setTimeout(() => stopButton.classList.add('visible'), 10);

                // Clear welcome message if it exists
                const chatMessages = document.getElementById('chat-messages');
                const welcomeMessage = chatMessages.querySelector('div[style*="display: flex"]');
                if (welcomeMessage && welcomeMessage.textContent.includes('Chat with your local ai now')) {
                    chatMessages.innerHTML = '';
                }

                // Abort any ongoing request
                if (currentAbortController) {
                    currentAbortController.abort();
                }
                
                if (!isRegenerate) {
                    // Create new chat if none exists
                    if (!chatManager.currentChatId) {
                        chatManager.createNewChat();
                    }
                    
                    // Add user message
                    addMessage(messageToSend, 'user-message');
                    chatManager.addMessage(chatManager.currentChatId, messageToSend, false);
                    messageInput.value = '';
                }

                // Show loading state
                messageInput.disabled = true;
                sendButton.disabled = true;

                // If regenerating, remove all messages after the regenerated one
                if (isRegenerate) {
                    const messages = chatMessages.querySelectorAll('.message-container');
                    let found = false;
                    messages.forEach(msg => {
                        if (found) {
                            msg.remove();
                        }
                        if (msg.querySelector('.message-text')?.textContent.trim() === messageToSend) {
                            found = true;
                        }
                    });
                }
                
                // Create new AbortController for this request
                currentAbortController = new AbortController();
                
                // Create AI message container for streaming
                const aiMessageDiv = document.createElement('div');
                aiMessageDiv.className = 'message-container ai-message-container';

                // Add icon and model name
                const iconDiv = document.createElement('div');
                iconDiv.className = 'message-icon fas fa-robot';
                aiMessageDiv.appendChild(iconDiv);

                const modelNameDiv = document.createElement('div');
                modelNameDiv.className = 'model-name';
                modelNameDiv.textContent = currentModel;
                aiMessageDiv.appendChild(modelNameDiv);

                // Add message content
                const messageDiv = document.createElement('div');
                messageDiv.className = 'message ai-message';
                messageDiv.innerHTML = `
                    <div class="message-content">
                        <div class="message-text"></div>
                    </div>
                `;
                aiMessageDiv.appendChild(messageDiv);

                // Add actions div
                const actionsDiv = document.createElement('div');
                actionsDiv.className = 'message-actions';
                actionsDiv.style.opacity = '0';
                actionsDiv.innerHTML = `
                    <i class="fas fa-copy action-btn copy-btn" title="Copy"></i>
                    <i class="fas fa-sync action-btn regenerate-btn" title="Regenerate"></i>
                `;
                aiMessageDiv.appendChild(actionsDiv);

                chatMessages.appendChild(aiMessageDiv);
                setupMessageActions(aiMessageDiv); // Add this line
                const messageText = aiMessageDiv.querySelector('.message-text');
                let fullResponse = '';
                
                const messageId = chatManager.uniqid();
                
                try {
                    const response = await fetch('api/ollama_handler.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ 
                            message: messageToSend,
                            model: currentModel,
                            chat_id: chatManager.currentChatId,
                            message_id: messageId,
                            resume: resume,
                            stream: true
                        }),
                        signal: currentAbortController.signal
                    });

                    const reader = response.body.getReader();
                    const decoder = new TextDecoder();

                    while (true) {
                        const {value, done} = await reader.read();
                        if (done) break;
                        
                        const chunk = decoder.decode(value);
                        try {
                            const lines = chunk.split('\n');
                            for (const line of lines) {
                                if (line.trim() && line.startsWith('data: ')) {
                                    const data = JSON.parse(line.substring(6));
                                    if (data.full_response) {
                                        fullResponse = data.full_response;
                                        messageText.innerHTML = formatMessage(fullResponse);
                                        chatMessages.scrollTop = chatMessages.scrollHeight;
                                    }
                                }
                            }
                        } catch (e) {
                            console.error('Error parsing chunk:', e);
                        }
                    }

                    // Show message actions after completion
                    aiMessageDiv.querySelector('.message-actions').style.opacity = '1';
                    
                    // Save the complete message
                    if (!isRegenerate) {
                        chatManager.addMessage(
                            chatManager.currentChatId || chatManager.pendingChat.id, 
                            fullResponse, 
                            true,
                            false,
                            messageId
                        );
                    } else {
                        chatManager.addMessage(
                            chatManager.currentChatId, 
                            fullResponse, 
                            true, 
                            true  // Mark as regenerated
                        );
                        
                        // Show version navigation immediately after regeneration
                        const aiMessageDiv = chatMessages.querySelector('.ai-message-container:last-child');
                        if (aiMessageDiv) {
                            const currentChat = chatManager.getChatHistory(chatManager.currentChatId);
                            const aiMessages = currentChat.messages.filter(m => m.isAI);
                            const lastAIMessage = aiMessages[aiMessages.length - 1];
                            
                            if (lastAIMessage?.versions?.length > 1) {
                                const versionNav = document.createElement('div');
                                versionNav.className = 'version-nav';
                                versionNav.innerHTML = `
                                    <button class="version-btn" ${lastAIMessage.currentVersion === 0 ? 'disabled' : ''} 
                                            onclick="navigateVersion(${lastAIMessage.aiIndex}, -1)">
                                        <i class="fas fa-chevron-left"></i>
                                    </button>
                                    <span class="version-info">${lastAIMessage.currentVersion + 1}/${lastAIMessage.versions.length}</span>
                                    <button class="version-btn" disabled 
                                            onclick="navigateVersion(${lastAIMessage.aiIndex}, 1)">
                                        <i class="fas fa-chevron-right"></i>
                                    </button>
                                `;
                                
                                const actionsDiv = aiMessageDiv.querySelector('.message-actions');
                                actionsDiv.insertBefore(versionNav, actionsDiv.firstChild);
                            }
                        }
                    }

                } catch (error) {
                    console.error('Error:', error);
                    
                    if (error.name === 'AbortError') {
                        // Keep the partial response instead of removing it
                        const lastAiMessage = chatMessages.querySelector('.ai-message-container:last-child');
                        if (lastAiMessage) {
                            // Save the partial response in chat history
                            const partialText = lastAiMessage.querySelector('.message-text').innerHTML;
                            chatManager.addMessage(
                                chatManager.currentChatId,
                                partialText,
                                true,
                                false,
                                messageId
                            );
                            
                            // Show the message actions
                            lastAiMessage.querySelector('.message-actions').style.opacity = '1';
                        }
                        return;
                    }
                    
                    messageText.innerHTML = 'Sorry, I encountered an error while processing your request: ' + error.message;
                    aiMessageDiv.classList.add('error');
                } finally {
                    currentAbortController = null;
                    messageInput.disabled = false;
                    messageInput.focus();
                    
                    // Reset generating flag
                    isGenerating = false;

                    // Hide stop button with fade out
                    stopButton.classList.remove('visible');
                    setTimeout(() => {
                        stopButton.style.display = 'none';
                    }, 300);
                    
                    // Hide stop button and show send button
                    document.querySelector('.stop-button').style.display = 'none';
                    document.querySelector('.send-button').style.display = 'flex';
                }
            }
        }

        // Remove incomplete message handling in addMessage function
        function addMessage(message, className, originalQuestion = '', isRegenerated = false) {
            const messagesContainer = document.getElementById('chat-messages');
            
            // Create container for message and its actions
            const containerDiv = document.createElement('div');
            containerDiv.className = `message-container ${className}-container`;
            
            // Add icon and model name for AI messages
            const iconDiv = document.createElement('div');
            const iconClass = className === 'user-message' ? 'fa-user' : 'fa-robot';
            iconDiv.className = `message-icon fas ${iconClass}`;
            containerDiv.appendChild(iconDiv);
            
            // Add model name for AI messages
            if (className === 'ai-message') {
                const currentChat = chatManager.getChatHistory(chatManager.currentChatId);
                if (currentChat) {
                    // Find the matching AI message to retrieve its aiIndex
                    const matchedMessage = currentChat.messages.find(m => m.isAI && m.content === message);
                    if (matchedMessage && matchedMessage.aiIndex !== undefined) {
                        containerDiv.setAttribute('data-ai-index', matchedMessage.aiIndex);
                    }
                }
            }
            
            // Create message div
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${className}`;
            
            const formattedMessage = formatMessage(message);
            
            // Create message content without icon
            messageDiv.innerHTML = `
                <div class="message-content">
                    <div class="message-text">${formattedMessage}</div>
                </div>
            `;

            // Create simplified actions div
            const actionsDiv = document.createElement('div');
            actionsDiv.className = 'message-actions';
            
            // Replace the version navigation creation code in the addMessage function
            if (className === 'ai-message') {
                const currentChat = chatManager.getChatHistory(chatManager.currentChatId);
                if (currentChat) {
                    const currentMessage = currentChat.messages.find(
                        m => m.isAI && m.content === message
                    );
                    
                    let versionControl = '';
                    if (currentMessage?.versions?.length > 1) {
                        versionControl = `
                            <div class="version-control">
                                <button class="version-prev" ${currentMessage.currentVersion === 0 ? 'disabled' : ''} 
                                        title="Previous version">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <span class="version-info">
                                    <span class="version-count">${currentMessage.currentVersion + 1}/${currentMessage.versions.length}</span>
                                </span>
                                <button class="version-next" 
                                        ${currentMessage.currentVersion === currentMessage.versions.length - 1 ? 'disabled' : ''} 
                                        title="Next version">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        `;
                    }

                    actionsDiv.innerHTML = `
                        ${versionControl}
                        <i class="fas fa-copy action-btn copy-btn" title="Copy"></i>
                        <i class="fas fa-sync action-btn regenerate-btn" title="Regenerate"></i>
                    `;

                    // Add version control event listeners
                    if (currentMessage?.versions?.length > 1) {
                        const container = actionsDiv;
                        const prevBtn = container.querySelector('.version-prev');
                        const nextBtn = container.querySelector('.version-next');

                        prevBtn?.addEventListener('click', (e) => {
                            e.preventDefault();
                            e.stopPropagation();
                            if (!prevBtn.disabled) {
                                handleVersionChange(currentMessage.aiIndex, -1, container);
                            }
                        });

                        nextBtn?.addEventListener('click', (e) => {
                            e.preventDefault();
                            e.stopPropagation();
                            if (!nextBtn.disabled) {
                                handleVersionChange(currentMessage.aiIndex, 1, container);
                            }
                        });
                    }
                }
            } else {
                actionsDiv.innerHTML = `
                    <i class="fas fa-edit action-btn edit-btn" title="Edit"></i>
                    <i class="fas fa-trash action-btn remove-btn" title="Remove"></i>
                `;
            }

            // Append message and actions to container
            containerDiv.appendChild(messageDiv);
            containerDiv.appendChild(actionsDiv);
            
            // Add click handlers directly to the icons with stopPropagation
            const icons = actionsDiv.querySelectorAll('.action-btn');
            icons.forEach(icon => {
                icon.addEventListener('click', async function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    if (icon.classList.contains('copy-btn')) {
                        const text = containerDiv.querySelector('.message-text').innerText;
                        try {
                            await navigator.clipboard.writeText(text);
                            // Change copy icon to check icon to indicate success
                            icon.classList.remove('fa-copy');
                            icon.classList.add('fa-check');
                            setTimeout(() => {
                                icon.classList.remove('fa-check');
                                icon.classList.add('fa-copy');
                            }, 2000);
                        } catch (error) {
                            console.error('Copy failed', error);
                        }
                    }
                    
                    if (icon.classList.contains('regenerate-btn')) {
                        // Find the corresponding user message preceding this AI response
                        const currentContainer = icon.closest('.message-container');
                        let userContainer = currentContainer.previousElementSibling;
                        while (userContainer && !userContainer.classList.contains('user-message-container')) {
                            userContainer = userContainer.previousElementSibling;
                        }
                        const userMessage = userContainer ? userContainer.querySelector('.message-text').innerText.trim() : '';
                        if (userMessage && !isGenerating) {
                            sendMessage(userMessage, true);
                        }
                    }
                    
                    if (icon.classList.contains('edit-btn')) {
                        const container = icon.closest('.message-container');
                        const messageDiv = container.querySelector('.message');
                        const messageText = messageDiv.querySelector('.message-text');
                        const originalText = messageText.textContent;
                        
                        // Create textarea without inline styles
                        const textarea = document.createElement('textarea');
                        textarea.value = originalText;
                        textarea.className = 'editing-textarea'; // Add a class instead of inline styles
                        
                        // Replace content with textarea
                        messageText.innerHTML = '';
                        messageText.appendChild(textarea);
                        textarea.focus();
                        
                        // Create save/cancel buttons
                        const editActions = document.createElement('div');
                        editActions.className = 'edit-actions';
                        editActions.innerHTML = `
                            <i class="fas fa-check save-edit" title="Save"></i>
                            <i class="fas fa-times cancel-edit" title="Cancel"></i>
                        `;
                        messageText.appendChild(editActions);
                        
                        // Add handlers for save/cancel
                        editActions.querySelector('.save-edit').addEventListener('click', () => {
                            const newText = textarea.value.trim();
                            if (newText && newText !== originalText) {
                                messageText.innerHTML = formatMessage(newText);
                                // Update in chat manager if needed
                                const messageIndex = chatManager.getMessageIndex(chatManager.currentChatId, originalText);
                                if (messageIndex !== -1) {
                                    chatManager.chats[chatManager.currentChatId].messages[messageIndex].content = newText;
                                    chatManager.saveChats();
                                }
                            } else {
                                messageText.innerHTML = formatMessage(originalText);
                            }
                        });
                        
                        editActions.querySelector('.cancel-edit').addEventListener('click', () => {
                            messageText.innerHTML = formatMessage(originalText);
                        });
                        
                        // Handle Escape key
                        textarea.addEventListener('keydown', (e) => {
                            if (e.key === 'Escape') {
                                messageText.innerHTML = formatMessage(originalText);
                            }
                        });
                    }
                    
                    if (icon.classList.contains('remove-btn')) {
                        const container = icon.closest('.message-container');
                        const messageText = container.querySelector('.message-text').textContent;
                        
                        // Show delete confirmation modal
                        const deleteChatModal = new bootstrap.Modal(document.getElementById('deleteChatModal'));
                        const confirmDeleteBtn = document.getElementById('confirmDeleteChat');
                        
                        // Update modal title and message for message deletion
                        document.getElementById('deleteChatModalLabel').textContent = 'Delete Message';
                        document.querySelector('#deleteChatModal .modal-body').textContent = 
                            'This will delete this message and all subsequent messages. This action cannot be undone.';
                        
                        // Set up the delete confirmation
                        const handleDelete = () => {
                            // Find the index of the message in the chat
                            const messageIndex = chatManager.getMessageIndex(chatManager.currentChatId, messageText);
                            if (messageIndex !== -1) {
                                // Remove this message and all subsequent messages
                                chatManager.removeMessage(chatManager.currentChatId, messageIndex);
                                
                                // Remove all message containers from this one onwards
                                let currentContainer = container;
                                while (currentContainer) {
                                    const nextContainer = currentContainer.nextElementSibling;
                                    currentContainer.remove();
                                    currentContainer = nextContainer;
                                }

                                // Update the chat history display
                                updateChatHistory();
                            }
                            deleteChatModal.hide();
                            
                            // Remove event listener after execution
                            confirmDeleteBtn.removeEventListener('click', handleDelete);
                        };
                        
                        // Add event listener for confirmation
                        confirmDeleteBtn.addEventListener('click', handleDelete);
                        
                        // Show the modal
                        deleteChatModal.show();
                        
                        // Clean up event listener when modal is hidden
                        document.getElementById('deleteChatModal').addEventListener('hidden.bs.modal', function () {
                            confirmDeleteBtn.removeEventListener('click', handleDelete);
                            // Reset modal title and message back to default
                            document.getElementById('deleteChatModalLabel').textContent = 'Delete Chat';
                            document.querySelector('#deleteChatModal .modal-body').textContent = 
                                'this action will have consequences...';
                        });
                    }
                });
            });

            // Also add click handlers for version navigation buttons if they exist
            const versionBtns = containerDiv.querySelectorAll('.version-btn');
            versionBtns.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    // Determine navigation direction from a class on the button:
                    const direction = btn.classList.contains('prev-version') ? -1 : 1;
                    const aiIndexStr = containerDiv.getAttribute('data-ai-index');
                    if (aiIndexStr !== null) {
                        const aiIndex = parseInt(aiIndexStr, 10);
                        window.navigateVersion(aiIndex, direction);
                    }
                });
            });

            // Append container to messages
            messagesContainer.appendChild(containerDiv);
            setupMessageActions(containerDiv); // Add this line
            messagesContainer.scrollTop = messagesContainer.scrollHeight;

            // Rest of your existing event handler code...
        }

        // Add version navigation function
        function navigateVersion(messageIndex, direction) {
            const result = chatManager.navigateVersion(
                chatManager.currentChatId, 
                messageIndex, 
                direction
            );
            
            if (result) {
                const chatMessages = document.getElementById('chat-messages');
                const messageContainers = chatMessages.querySelectorAll('.message-container');
                const targetContainer = Array.from(messageContainers).find(container => 
                    container.getAttribute('data-ai-index') === messageIndex.toString()
                );
                
                if (targetContainer) {
                    // Remove all subsequent messages from UI
                    let currentElem = targetContainer.nextElementSibling;
                    while (currentElem) {
                        const nextElem = currentElem.nextElementSibling;
                        currentElem.remove();
                        currentElem = nextElem;
                    }

                    const messageText = targetContainer.querySelector('.message-text');
                    const versionNav = targetContainer.querySelector('.version-nav');
                    
                    // Add changing animation
                    messageText.classList.add('changing');
                    
                    setTimeout(() => {
                        // Update content
                        messageText.innerHTML = formatMessage(result.content);
                        messageText.classList.remove('changing');
                        messageText.classList.add('changed');
                        
                        if (versionNav) {
                            // Update version info
                            versionNav.querySelector('.version-info').textContent = 
                                `${result.currentVersion + 1}/${result.totalVersions}`;
                            
                            // Update button states
                            const [prevBtn, nextBtn] = versionNav.querySelectorAll('.version-btn');
                            prevBtn.disabled = !result.hasOlder;
                            nextBtn.disabled = !result.hasNewer;
                        }
                        
                        setTimeout(() => {
                            messageText.classList.remove('changed');
                        }, 300);
                    }, 300);
                }
            }
        }

        // Update formatMessage function with Markdown support
        function formatMessage(message) {
            // Store any code blocks temporarily
            const codeBlocks = [];
            message = message.replace(/```([\s\S]*?)```/g, (match) => {
                codeBlocks.push(match);
                return `__CODE_BLOCK_${codeBlocks.length - 1}__`;
            });
            
            // Remove any '>' character that appears alone on any line
            message = message.replace(/^\s*>\s*$/gm, '');
            message = message.replace(/\n\s*>\s*$/g, '');
            
            // Remove multiple consecutive empty lines
            message = message.replace(/\n{3,}/g, '\n\n');
            
            // Remove trailing whitespace from each line
            message = message.replace(/[ \t]+$/gm, '');
            
            // Apply Markdown formatting
            message = message
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\*(.*?)\*/g, '<em>$1</em>')
                .replace(/~~(.*?)~~/g, '<del>$1</del>')
                .replace(/`([^`]+)`/g, '<code>$1</code>')
                .replace(/^# (.*$)/gm, '<h1>$1</h1>')
                .replace(/^## (.*$)/gm, '<h2>$2</h2>')
                .replace(/^### (.*$)/gm, '<h3>$3</h3>')
                .replace(/^> (.*$)/gm, '<blockquote>$1</blockquote>')
                .replace(/^- (.*$)/gm, '<li>$1</li>')
                .replace(/(<li>.*<\/li>)/gs, '<ul>$1</ul>')
                .replace(/^\d+\. (.*$)/gm, '<li>$1</li>')
                .replace(/(<li>.*<\/li>)/gs, '<ol>$1</ol>')
                .replace(/^[•]\s/gm, '<br>• ');

            // Restore code blocks with proper formatting
            codeBlocks.forEach((block, i) => {
                const code = block.replace(/```([\s\S]*?)```/g, '$1').trim();
                message = message.replace(
                    `__CODE_BLOCK_${i}__`,
                    `<pre><code>${code}</code></pre>`
                );
            });
            
            // Convert URLs to links
            message = message.replace(
                /(https?:\/\/[^\s<]+)/g,
                '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>'
            );
            
            // Add line breaks but preserve formatted blocks
            message = message.replace(/\n(?![^<]*>)/g, '<br>');
            
            return message;
        }

        // Sidebar Toggle Functionality
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('minimized');
            mainContent.classList.toggle('expanded');

            // Toggle icon direction
            const icon = this.querySelector('i');
            if (sidebar.classList.contains('minimized')) {
                icon.classList.remove('fa-chevron-left');
                icon.classList.add('fa-chevron-right');
            } else {
                icon.classList.remove('fa-chevron-right');
                icon.classList.add('fa-chevron-left');
            }
        });

        // Enable/disable send button based on input
        messageInput.addEventListener('input', function() {
            const isEmpty = !this.value.trim();
            sendButton.disabled = isEmpty;
            
            // Auto-resize textarea
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 200) + 'px';
            
            // Hide stop button when input is being typed
            document.getElementById('stop-button').style.display = 'none';
        });

        // Handle form submission (for send button click)
        chatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            sendMessage();
        });

        // Handle keyboard events
        messageInput.addEventListener('keydown', function(e) {
            // Check if Enter was pressed without Shift
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault(); // Prevent default behavior (new line)
                
                // Only send if the button is not disabled
                if (!sendButton.disabled) {
                    sendMessage();
                }
            }
        });

        // Add visual feedback for send button
        sendButton.addEventListener('mousedown', function() {
            if (!this.disabled) {
                this.style.transform = 'translateY(50%) scale(0.95)';
            }
        });

        sendButton.addEventListener('mouseup', function() {
            this.style.transform = 'translateY(50%)';
        });

        sendButton.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(50%)';
        });

        // Add stop button handler
        document.getElementById('stop-button').addEventListener('click', function() {
            if (currentAbortController) {
                // Add clicking animation
                this.style.transform = 'translateY(50%) scale(0.9)';
                setTimeout(() => {
                    this.style.transform = 'translateY(50%) scale(1)';
                }, 150);
                
                currentAbortController.abort();
                this.classList.remove('visible');
                setTimeout(() => {
                    this.style.display = 'none';
                    document.querySelector('.send-button').style.display = 'flex';
                }, 300);
                
                messageInput.disabled = false;
                messageInput.focus();
                isGenerating = false;
                updateMessageActionStates();
            }
        });

        // New Chat Button Handling
        newChatBtn.addEventListener('click', function() {
            // Abort any ongoing request
            if (currentAbortController) {
                currentAbortController.abort();
                currentAbortController = null;
            }
            
            // Simply redirect to the base URL
            window.location.href = 'titano.php';
        });

        // Chat History Item Selection
        chatHistoryItems.forEach(item => {
            item.addEventListener('click', function() {
                // Remove active state from all items
                chatHistoryItems.forEach(i => i.classList.remove('active'));
                
                // Add active state to clicked item
                this.classList.add('active');
                
                // Fetch and load selected chat history
                const chatId = this.getAttribute('data-chat-id');
                loadChatHistory(chatId);
            });
        });

        // Simulated chat history loading function
        async function loadChatHistory(chatId) {
            if (currentAbortController) {
                currentAbortController.abort();
                currentAbortController = null;
            }
            
            const chat = chatManager.getChatHistory(chatId);
            if (!chat) return;

            updateURL(chatId);
            chatManager.currentChatId = chatId;
            updateChatHistory();

            const chatMessages = document.getElementById('chat-messages');
            chatMessages.innerHTML = '';

            // Display messages from current branch
            const messages = chatManager.getCurrentBranchMessages(chatId);
            messages.forEach(msg => {
                const className = msg.isAI ? 'ai-message' : 'user-message';
                addMessage(msg.content, className);
            });

            // Setup actions for all messages
            document.querySelectorAll('.message-container').forEach(container => {
                setupMessageActions(container);
            });

            // Check for incomplete response
            const incompleteResponse = await chatManager.resumeResponse(chatId);
            if (incompleteResponse) {
                // Resume the interrupted response
                sendMessage(incompleteResponse.message, false, true);
            }
        }

        // Update delete chat function
        function deleteChat(chatId) {
            event.stopPropagation();
            
            // Store the chat ID to be deleted
            const deleteChatModal = new bootstrap.Modal(document.getElementById('deleteChatModal'));
            const confirmDeleteBtn = document.getElementById('confirmDeleteChat');
            
            // Set up the delete confirmation
            const handleDelete = () => {
                chatManager.deleteChat(chatId);
                if (chatManager.currentChatId === chatId) {
                    chatManager.currentChatId = null;
                    document.getElementById('chat-messages').innerHTML = '';
                }
                updateChatHistory();
                deleteChatModal.hide();
                
                // Remove event listener after execution
                confirmDeleteBtn.removeEventListener('click', handleDelete);
            };
            
            // Add event listener for confirmation
            confirmDeleteBtn.addEventListener('click', handleDelete);
            
            // Show the modal
            deleteChatModal.show();
            
            // Clean up event listener when modal is hidden
            document.getElementById('deleteChatModal').addEventListener('hidden.bs.modal', function () {
                confirmDeleteBtn.removeEventListener('click', handleDelete);
            });
        }

        // Function to update chat history display
        function updateChatHistory() {
            const searchQuery = document.getElementById('chat-search')?.value || '';
            const chatHistoryContent = document.getElementById('chat-history-content');
            const groups = chatManager.searchChats(searchQuery);
            
            if (Object.values(groups).flat().length === 0) {
                chatHistoryContent.innerHTML = `
                    <div class="chat-history-empty">
                        ${searchQuery ? 'No chats found' : 'No chats yet'}
                    </div>
                `;
                return;
            }

            let html = '';
            for (const [groupName, chats] of Object.entries(groups)) {
                if (chats.length === 0) continue;

                html += `
                    <div class="chat-history-group">
                        <div class="chat-history-group-title">${groupName}</div>
                `;

                chats.forEach(chat => {
                    const messageCount = chat.messages.length;
                    const isActive = chat.id === chatManager.currentChatId;
                    
                    html += `
                        <div class="chat-history-item${isActive ? ' active' : ''}" data-chat-id="${chat.id}">
                            <div class="chat-title">
                                <i class="fas fa-message"></i>
                                <span>${chat.title}</span>
                            </div>
                            <div class="chat-meta">
                                <span class="chat-timestamp">${chatManager.formatRelativeTime(chat.timestamp)}</span>
                                <button class="delete-chat" onclick="deleteChat('${chat.id}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    `;
                });

                html += '</div>';
            }
            
            chatHistoryContent.innerHTML = html;

            // Add click handlers
            document.querySelectorAll('.chat-history-item').forEach(item => {
                item.addEventListener('click', () => loadChatHistory(item.dataset.chatId));
            });
        }

        // Add search handler
        document.getElementById('chat-search')?.addEventListener('input', debounce(updateChatHistory, 300));

        // Debounce helper function
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // Add this function to get chat ID from URL
        function getChatIdFromURL() {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get('chat');
        }

        // Update the base URL handler in the popstate event listener
        window.addEventListener('popstate', function(event) {
            const chatId = getChatIdFromURL();
            if (chatId) {
                loadChatHistory(chatId);
            } else {
                chatManager.currentChatId = null;
                document.getElementById('chat-messages').innerHTML = `
                    <div class="welcome-message" style="
                        display: flex;
                        height: 100%;
                        justify-content: center;
                        align-items: center;
                        text-align: center;
                        color: var(--text-color);
                        font-size: 1.5rem;
                        opacity: 0.8;
                    ">
                        Chat with your local ai now, <span id="username-display"></span>!
                    </div>
                `;
                refreshUsername();
                updateChatHistory();
            }
        });

        // Add this code at the end of your script to load initial chat
        document.addEventListener('DOMContentLoaded', function() {
            const initialChatId = getChatIdFromURL();
            if (initialChatId) {
                loadChatHistory(initialChatId);
            }
            
            fetchModels();
            updateChatHistory();
        });

        // Make updateChatHistory function globally accessible
        window.updateChatHistory = updateChatHistory;
        window.updateURL = updateURL;

        // Check for existing chat ID in URL on page load
        const urlChatId = getChatIdFromURL();
        if (urlChatId) {
            const existingChat = chatManager.getChatHistory(urlChatId);
            if (existingChat) {
                chatManager.currentChatId = urlChatId;
                loadChatHistory(urlChatId);
            }
        }

        // Add Model functionality
        document.getElementById('add-model-btn').addEventListener('click', () => {
            const modal = new bootstrap.Modal(document.getElementById('addModelModal'));
            modal.show();
        });

        document.getElementById('saveModel').addEventListener('click', async () => {
            const modelName = document.getElementById('modelName').value.trim();
            const saveBtn = document.getElementById('saveModel');
            const progressDiv = document.getElementById('downloadProgress');
            const progressBar = progressDiv.querySelector('.progress-bar');
            const progressText = document.getElementById('progressText');
            const cancelBtn = document.getElementById('cancelDownload');

            if (modelName) {
                try {
                    saveBtn.disabled = true;
                    progressDiv.style.display = 'block'; // Changed from classList.remove('d-none')
                    progressBar.style.width = '0%';
                    progressText.textContent = 'Starting download...';
                    cancelBtn.style.display = 'block';

                    // Create EventSource for progress updates
                    const eventSource = new EventSource(`api/add_model.php?model=${encodeURIComponent(modelName)}`);

                    // Add cancel button click handler
                    cancelBtn.onclick = async () => {
                        try {
                            await fetch(`api/cancel_download.php?model=${encodeURIComponent(modelName)}`, {
                                method: 'POST'
                            });
                            eventSource.close();
                            progressText.textContent = 'Download cancelled';
                            cancelBtn.style.display = 'none';
                            saveBtn.disabled = false;
                            setTimeout(() => {
                                progressDiv.style.display = 'none'; // Changed from classList.add('d-none')
                            }, 2000);
                        } catch (error) {
                            console.error('Error cancelling download:', error);
                        }
                    };

                    eventSource.onmessage = (event) => {
                        try {
                            const data = JSON.parse(event.data);
                            console.log('Progress update:', data); // Add debugging

                            if (data.status === 'pulling') {
                                const progress = Math.round((data.completed / data.total) * 100);
                                progressBar.style.width = `${progress}%`;
                                progressText.textContent = `Downloading: ${progress}% (${formatBytes(data.completed)} / ${formatBytes(data.total)})`;
                            } else if (data.status === 'complete') {
                                progressBar.style.width = '100%';
                                progressText.textContent = 'Download complete!';
                                cancelBtn.style.display = 'none';
                                eventSource.close();
                                
                                // Refresh model list and close modal after a delay
                                setTimeout(() => {
                                    fetchModels();
                                    const modal = bootstrap.Modal.getInstance(document.getElementById('addModelModal'));
                                    modal.hide();
                                    document.getElementById('addModelForm').reset();
                                    progressDiv.style.display = 'none';
                                    saveBtn.disabled = false;
                                }, 1500);
                            } else if (data.status === 'error' || data.status === 'cancelled') {
                                throw new Error(data.error || 'Download cancelled');
                            }
                        } catch (error) {
                            console.error('Error parsing event data:', error);
                            throw error;
                        }
                    };

                    eventSource.onerror = (error) => {
                        console.error('EventSource error:', error);
                        eventSource.close();
                        cancelBtn.style.display = 'none';
                        throw new Error('Failed to connect to server');
                    };

                } catch (error) {
                    console.error('Download error:', error);
                    progressText.textContent = `Error: ${error.message}`;
                    progressDiv.classList.add('text-danger');
                    saveBtn.disabled = false;
                    cancelBtn.style.display = 'none';
                }
            }
        });

        // Helper function to format bytes
        function formatBytes(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Add this function to update username
        async function refreshUsername() {
            try {
                const response = await fetch('api/get_user_info.php');
                const data = await response.json();
                if (data.username) {
                    document.getElementById('username-display').textContent = data.username;
                }
            } catch (error) {
                console.error('Error fetching username:', error);
            }
        }

        // Call refreshUsername when page loads
        document.addEventListener('DOMContentLoaded', refreshUsername);

        // Make navigateVersion function global
        window.navigateVersion = function(messageIndex, direction) {
            const result = chatManager.navigateVersion(
                chatManager.currentChatId, 
                messageIndex, 
                direction
            );
            
            if (result) {
                const aiMessages = document.querySelectorAll('.ai-message-container');
                const targetContainer = Array.from(aiMessages).find(container => {
                    const messageText = container.querySelector('.message-text');
                    const currentMessage = chatManager.chats[chatManager.currentChatId].messages
                        .find(m => m.isAI && m.aiIndex === messageIndex);
                    return currentMessage && messageText.textContent === currentMessage.content;
                });

                if (targetContainer) {
                    const messageText = targetContainer.querySelector('.message-text');
                    
                    // Add changing class to trigger fade out
                    messageText.classList.add('changing');
                    
                    // After fade out, update content and trigger fade in
                    setTimeout(() => {
                        messageText.innerHTML = formatMessage(result.content);
                        messageText.classList.remove('changing');
                        messageText.classList.add('changed');
                        
                        // Update version counter and buttons
                        const versionNav = targetContainer.querySelector('.version-nav');
                        if (versionNav) {
                            versionNav.querySelector('.version-info').textContent = 
                                `${result.currentVersion + 1}/${result.totalVersions}`;
                            
                            // Update button states
                            const [prevBtn, nextBtn] = versionNav.querySelectorAll('.version-btn');
                            prevBtn.disabled = result.currentVersion === 0;
                            nextBtn.disabled = result.currentVersion === result.totalVersions - 1;
                        }
                        
                        // Remove changed class after animation completes
                        setTimeout(() => {
                            messageText.classList.remove('changed');
                        }, 300);
                    }, 300);
                }
            }
        }

        // Replace the navigateVersion function with this fixed version
        function navigateVersion(aiIndex, direction) {
            // Prevent rapid clicks by disabling buttons temporarily
            const versionBtns = document.querySelectorAll('.version-btn');
            versionBtns.forEach(btn => btn.disabled = true);
            
            const result = chatManager.navigateVersion(
                chatManager.currentChatId, 
                aiIndex, 
                direction
            );
            
            if (result) {
                const messageContainers = document.querySelectorAll('.ai-message-container');
                const targetContainer = Array.from(messageContainers).find(container => 
                    container.getAttribute('data-ai-index') === aiIndex.toString()
                );
                
                if (targetContainer) {
                    const messageText = targetContainer.querySelector('.message-text');
                    const versionNav = targetContainer.querySelector('.version-nav');
                    
                    // Add fade-out effect
                    messageText.classList.add('changing');
                    
                    setTimeout(() => {
                        // Update message content
                        messageText.innerHTML = formatMessage(result.content);
                        messageText.classList.remove('changing');
                        messageText.classList.add('changed');
                        
                        if (versionNav) {
                            // Update version info
                            versionNav.querySelector('.version-info').textContent = 
                                `${result.currentVersion + 1}/${result.totalVersions}`;
                            
                            // Get fresh references to buttons
                            const prevBtn = versionNav.querySelector('.version-btn:first-child');
                            const nextBtn = versionNav.querySelector('.version-btn:last-child');
                            
                            // Update button states
                            prevBtn.disabled = !result.hasOlder;
                            nextBtn.disabled = !result.hasNewer;
                            
                            // Re-add event listeners
                            prevBtn.onclick = (e) => {
                                e.preventDefault();
                                e.stopPropagation();
                                if (!prevBtn.disabled) {
                                    navigateVersion(aiIndex, -1);
                                }
                            };
                            
                            nextBtn.onclick = (e) => {
                                e.preventDefault();
                                e.stopPropagation();
                                if (!nextBtn.disabled) {
                                    navigateVersion(aiIndex, 1);
                                }
                            };
                        }
                        
                        // Remove changed class after animation
                        setTimeout(() => {
                            messageText.classList.remove('changed');
                            // Re-enable all version buttons
                            document.querySelectorAll('.version-btn').forEach(btn => {
                                if (!btn.hasAttribute('data-disabled')) {
                                    btn.disabled = false;
                                }
                            });
                        }, 300);
                    }, 300);
                }
            } else {
                // Re-enable buttons if navigation failed
                versionBtns.forEach(btn => {
                    if (!btn.hasAttribute('data-disabled')) {
                        btn.disabled = false;
                    }
                });
            }
        }

        // Add this function to initialize version navigation buttons
        function setupVersionNavigation(messageContainer, aiIndex, currentVersion, totalVersions) {
            const versionNav = messageContainer.querySelector('.version-nav');
            if (versionNav) {
                const prevBtn = versionNav.querySelector('.version-btn:first-child');
                const nextBtn = versionNav.querySelector('.version-btn:last-child');
                
                // Remove existing listeners
                prevBtn.replaceWith(prevBtn.cloneNode(true));
                nextBtn.replaceWith(nextBtn.cloneNode(true));
                
                // Get the new button references
                const newPrevBtn = versionNav.querySelector('.version-btn:first-child');
                const newNextBtn = versionNav.querySelector('.version-btn:last-child');
                
                // Add new listeners
                newPrevBtn.onclick = (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    if (!newPrevBtn.disabled) {
                        navigateVersion(aiIndex, -1);
                    }
                };
                
                newNextBtn.onclick = (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    if (!newNextBtn.disabled) {
                        navigateVersion(aiIndex, 1);
                    }
                };
                
                // Set initial states
                newPrevBtn.disabled = currentVersion === 0;
                newNextBtn.disabled = currentVersion === totalVersions - 1;
            }
        }

        // Add new version handling function
        function handleVersionChange(aiIndex, direction, container) {
            const result = chatManager.navigateVersion(
                chatManager.currentChatId,
                aiIndex,
                direction
            );

            if (result) {
                const messageContainer = container.closest('.message-container');
                const messageText = messageContainer.querySelector('.message-text');
                const versionControl = container.querySelector('.version-control');

                // Fade out
                messageText.classList.add('changing');

                setTimeout(() => {
                    // Update content
                    messageText.innerHTML = formatMessage(result.content);
                    messageText.classList.remove('changing');
                    messageText.classList.add('changed');

                    // Update version controls
                    if (versionControl) {
                        const versionCount = versionControl.querySelector('.version-count');
                        versionCount.textContent = `${result.currentVersion + 1}/${result.totalVersions}`;

                        const prevBtn = versionControl.querySelector('.version-prev');
                        const nextBtn = versionControl.querySelector('.version-next');

                        prevBtn.disabled = !result.hasOlder;
                        nextBtn.disabled = !result.hasNewer;
                    }

                    setTimeout(() => {
                        messageText.classList.remove('changed');
                    }, 300);
                }, 300);
            }
        }

        // Add to your window unload handler
        window.addEventListener('beforeunload', function() {
            // Save the current state before unloading
            chatManager.saveChats();
        });

        // Add this function inside your script tag, before any other functions

        function setupMessageActions(containerDiv) {
            const actionsDiv = containerDiv.querySelector('.message-actions');
            if (!actionsDiv) return;

            const icons = actionsDiv.querySelectorAll('.action-btn');
            icons.forEach(icon => {
                icon.addEventListener('click', async function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // Prevent actions while generating
                    if (isGenerating && (icon.classList.contains('regenerate-btn') || icon.classList.contains('edit-btn') || icon.classList.contains('remove-btn'))) {
                        return;
                    }
                    
                    if (icon.classList.contains('copy-btn')) {
                        const text = containerDiv.querySelector('.message-text').innerText;
                        try {
                            await navigator.clipboard.writeText(text);
                            icon.classList.remove('fa-copy');
                            icon.classList.add('fa-check');
                            setTimeout(() => {
                                icon.classList.remove('fa-check');
                                icon.classList.add('fa-copy');
                            }, 2000);
                        } catch (error) {
                            console.error('Copy failed:', error);
                        }
                    }
                    
                    if (icon.classList.contains('regenerate-btn')) {
                        const currentContainer = icon.closest('.message-container');
                        let userContainer = currentContainer.previousElementSibling;
                        while (userContainer && !userContainer.classList.contains('user-message-container')) {
                            userContainer = userContainer.previousElementSibling;
                        }
                        const userMessage = userContainer ? userContainer.querySelector('.message-text').innerText.trim() : '';
                        if (userMessage && !isGenerating) {
                            sendMessage(userMessage, true);
                        }
                    }
                    
                    if (icon.classList.contains('edit-btn')) {
                        // ... existing edit button code ...
                    }
                    
                    if (icon.classList.contains('remove-btn')) {
                        // ... existing remove button code ...
                    }
                });
            });

            // Setup version control if it exists
            const versionControl = actionsDiv.querySelector('.version-control');
            if (versionControl) {
                const prevBtn = versionControl.querySelector('.version-prev');
                const nextBtn = versionControl.querySelector('.version-next');

                prevBtn?.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    if (!prevBtn.disabled) {
                        const aiIndex = containerDiv.getAttribute('data-ai-index');
                        handleVersionChange(parseInt(aiIndex), -1, actionsDiv);
                    }
                });

                nextBtn?.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    if (!nextBtn.disabled) {
                        const aiIndex = containerDiv.getAttribute('data-ai-index');
                        handleVersionChange(parseInt(aiIndex), 1, actionsDiv);
                    }
                });
            }
        }

        // Add branch switching function
        function switchToBranch(chatId, branchId) {
            if (chatManager.switchBranch(chatId, branchId)) {
                loadChatHistory(chatId);
            }
        }

        // Update function to handle message action states
        function updateMessageActionStates() {
            const actionButtons = document.querySelectorAll('.regenerate-btn, .edit-btn, .remove-btn');
            actionButtons.forEach(button => {
                if (isGenerating) {
                    button.style.opacity = '0.5';
                    button.style.cursor = 'not-allowed';
                } else {
                    button.style.opacity = '1';
                    button.style.cursor = 'pointer';
                }
            });
        }

        // Add observer to update action states when messages are added
        const chatMessages = document.getElementById('chat-messages');
        const observer = new MutationObserver(() => {
            updateMessageActionStates();
        });

        observer.observe(chatMessages, {
            childList: true,
            subtree: true
        });

        // Add refresh button handler
        document.getElementById('refresh-models').addEventListener('click', function() {
            const icon = this.querySelector('i');
            icon.classList.add('rotating');
            fetchModels().then(() => {
                setTimeout(() => {
                    icon.classList.remove('rotating');
                }, 500);
            });
        });

        // Add model validation
        document.getElementById('checkModel').addEventListener('click', async () => {
            const modelName = document.getElementById('modelName').value.trim();
            const statusDiv = document.getElementById('modelStatus');
            const saveBtn = document.getElementById('saveModel');
            
            if (!modelName) return;
            
            try {
                // Show loading state
                statusDiv.innerHTML = `
                    <div class="d-flex align-items-center text-info">
                        <i class="fas fa-spinner fa-spin me-2"></i>
                        Checking model availability...
                    </div>
                `;
                statusDiv.style.display = 'block';
                
                const response = await fetch(`api/check_model.php?model=${encodeURIComponent(modelName)}`);
                const data = await response.json();
                
                if (data.isLocal) {
                    statusDiv.innerHTML = `
                        <div class="text-success">
                            <i class="fas fa-check-circle me-2"></i>
                            ${data.message}
                        </div>
                    `;
                    saveBtn.disabled = true;
                } else if (data.exists) {
                    statusDiv.innerHTML = `
                        <div class="text-info">
                            <i class="fas fa-info-circle me-2"></i>
                            ${data.message}
                            ${data.source === 'Hugging Face' ? `
                                <div class="small text-muted mt-1">
                                    Author: ${data.author}<br>
                                    Model: ${data.name}<br>
                                    <a href="${data.url}" target="_blank" rel="noopener noreferrer">View on Hugging Face</a>
                                </div>
                            ` : ''}
                        </div>
                    `;
                    saveBtn.disabled = false;
                } else {
                    statusDiv.innerHTML = `
                        <div class="text-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            ${data.message}
                        </div>
                    `;
                    saveBtn.disabled = true;
                }
            } catch (error) {
                statusDiv.innerHTML = `
                    <div class="text-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        Error checking model availability
                    </div>
                `;
                saveBtn.disabled = true;
            }
        });

        // Clear status when modal is hidden
        document.getElementById('addModelModal').addEventListener('hidden.bs.modal', () => {
            document.getElementById('modelStatus').style.display = 'none';
            document.getElementById('modelName').value = '';
            document.getElementById('saveModel').disabled = false;
        });

    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>