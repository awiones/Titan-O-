<?php
session_start();
require_once 'config/config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Titano AI Chat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --background-color: #ffffff;
            --text-color: #000000;
            --input-border: #000000;
            --input-background: #ffffff;
            --button-background: #000000;
            --button-text: #ffffff;
            --hover-background: #333333;
        }

        body {
            height: 100vh;
            overflow: hidden;
            background-color: var(--background-color);
            color: var(--text-color);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
        }

        .chat-container {
            height: 100vh;
            display: flex;
        }

        .sidebar {
            width: 250px;
            background-color: var(--background-color);
            color: var(--text-color);
            padding: 20px;
            display: flex;
            flex-direction: column;
            transition: width 0.3s ease, min-width 0.3s ease;
        }

        .titan-title {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 15px;
            letter-spacing: 2px;
        }

        .settings-btn {
            margin-top: auto;
            background-color: var(--button-background);
            color: var(--button-text);
            border: 2px solid var(--text-color);
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 4px 4px 0 var(--text-color);
            display: flex;
            align-items: center;
            gap: 10px;
            justify-content: center;
        }

        .settings-btn:hover {
            transform: translate(-2px, -2px);
            box-shadow: 6px 6px 0 var(--text-color);
        }

        .sidebar.minimized .titan-title,
        .sidebar.minimized .settings-btn span {
            display: none;
        }

        .sidebar.minimized {
            width: 70px;
            min-width: 70px;
            overflow: hidden;
        }

        .sidebar.minimized .user-profile,
        .sidebar.minimized .new-chat-btn,
        .sidebar.minimized .chat-history {
            display: none;
        }

        .sidebar.minimized .sidebar-toggle {
            width: 100%;
            text-align: center;
        }

        .main-content {
            flex-grow: 1;
            background-color: var(--background-color);
            display: flex;
            flex-direction: column;
            transition: width 0.3s ease, margin-left 0.3s ease;
        }

        .main-content.expanded {
            width: calc(100% - 70px);
            margin-left: 70px;
        }

        .sidebar-toggle {
            background: none;
            border: none;
            color: var(--text-color);
            cursor: pointer;
            padding: 10px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sidebar-toggle i {
            font-size: 1.2rem;
        }

        .sidebar.minimized .sidebar-toggle-text {
            display: none;
        }

        .user-profile {
            background-color: var(--input-background);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            display: flex;
            flex-direction: column;
        }

        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            margin-right: 10px;
            object-fit: cover;
        }

        .user-info {
            display: flex;
            flex-direction: column;
        }

        .username {
            font-weight: 600;
        }

        .user-tier {
            font-size: 0.8rem;
            color: #888;
        }

        .upgrade-btn {
            width: 100%;
            margin-top: 10px;
            background-color: transparent;
            border-color: var(--button-background);
            color: var(--button-background);
        }

        .upgrade-btn:hover {
            background-color: var(--button-background);
            color: var (--button-text);
        }

        .new-chat-btn {
            background-color: var(--background-color);
            color: var(--text-color);
            border: 1px solid var(--input-border);
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .new-chat-btn:hover {
            background-color: var(--hover-background);
        }

        .chat-history {
            flex-grow: 1;
            overflow-y: auto;
        }

        .chat-history-item {
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.2s ease;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .chat-history-item:hover {
            background-color: var(--hover-background);
        }

        .chat-history-item.active {
            background-color: var(--hover-background);
            border-left: 3px solid var(--button-background);
        }

        .chat-messages {
            flex-grow: 1;
            padding: 20px;
            overflow-y: auto;
        }

        .message {
            max-width: 800px;
            margin: 12px 0;
            width: fit-content; 
            padding: 14px 18px;
            border-radius: 8px;
            line-height: 1.6;
            font-size: 0.95rem;
            color: var(--text-color);
            display: flex;
            gap: 12px;
            align-items: start;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .user-message {
            background-color: var(--input-background);
            margin-left: auto;
            border: 1px solid var(--input-border);
        }

        .ai-message {
            background-color: var(--hover-background);
            margin-right: auto;
            border: 1px solid var(--input-border);
        }

        .message-icon {
            font-size: 1.2rem;
            margin-top: 2px;
            color: #9b9b9b;
        }

        .message-text {
            flex-grow: 1;
        }

        .input-container {
            padding: 20px;
            background-color: var(--background-color);
            position: relative;
            max-width: 800px;
            margin: 0 auto;
            width: 100%;
        }

        .input-group {
            position: relative;
            display: flex;
            align-items: center;
            background-color: var(--input-background);
            border-radius: 10px;
            border: 1px solid var(--input-border);
        }

        .message-input {
            width: 100%;
            padding: 12px 50px 12px 15px !important;
            border: none !important;
            background-color: transparent !important;
            color: var(--text-color) !important;
            resize: none;
            max-height: 200px;
            font-size: 1rem;
            line-height: 1.5;
        }

        .message-input:focus {
            outline: none !important;
            box-shadow: none !important;
        }

        .send-button {
            position: absolute;
            right: 10px;
            bottom: 50%;
            transform: translateY(50%);
            background: none;
            border: none;
            color: var(--text-color);
            padding: 8px 12px;
            cursor: pointer;
            transition: color 0.3s ease, transform 0.1s ease;
        }

        .send-button:active:not(:disabled) {
            transform: translateY(50%) scale(0.95);
        }

        .message-input:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .chat-container {
            background-color: var(--background-color);
            border: 2px solid var(--text-color);
            border-radius: 10px;
            width: 100%;
            height: 95vh;
            margin: 20px;
            box-shadow: 8px 8px 0 var(--text-color);
            display: flex;
        }

        .sidebar {
            border-right: 2px solid var(--text-color);
            background-color: var(--background-color);
        }

        .new-chat-btn {
            border: 2px solid var(--text-color);
            background-color: var(--button-background);
            color: var(--button-text);
            transition: all 0.3s ease;
            box-shadow: 4px 4px 0 var(--text-color);
        }

        .new-chat-btn:hover {
            transform: translate(-2px, -2px);
            box-shadow: 6px 6px 0 var(--text-color);
        }

        .chat-history-item {
            border: 1px solid var(--text-color);
            transition: all 0.3s ease;
        }

        .chat-history-item:hover {
            transform: translate(-2px, -2px);
            box-shadow: 4px 4px 0 var(--text-color);
        }

        .chat-history-item.active {
            background-color: var(--button-background);
            color: var(--button-text);
            box-shadow: 4px 4px 0 var(--text-color);
        }

        .message {
            background-color: var(--background-color);
            border: 2px solid var(--text-color);
            box-shadow: 4px 4px 0 var(--text-color);
            transition: all 0.3s ease;
        }

        .message:hover {
            transform: translate(-2px, -2px);
            box-shadow: 6px 6px 0 var(--text-color);
        }

        .input-container {
            border-top: 2px solid var(--text-color);
            background-color: var(--background-color);
            padding: 20px;
        }

        .input-group {
            border: 2px solid var(--text-color);
            box-shadow: 4px 4px 0 var(--text-color);
            transition: all 0.3s ease;
        }

        .input-group:focus-within {
            transform: translate(-2px, -2px);
            box-shadow: 6px 6px 0 var(--text-color);
        }

        .message-input {
            border: none !important;
            background-color: var(--background-color) !important;
            color: var(--text-color) !important;
        }

        .send-button {
            color: var(--text-color);
            transition: transform 0.3s ease;
        }

        .send-button:hover:not(:disabled) {
            transform: translateY(50%) scale(1.1);
        }

        .sidebar-toggle {
            border: 2px solid var(--text-color);
            background-color: var(--button-background);
            color: var(--button-text);
            margin: 10px;
            border-radius: 5px;
            transition: all 0.3s ease;
            box-shadow: 4px 4px 0 var(--text-color);
        }

        .sidebar-toggle:hover {
            transform: translate(-2px, -2px);
            box-shadow: 6px 6px 0 var(--text-color);
        }

        body {
            background-color: var(--background-color);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        /* Update message colors */
        .user-message, .ai-message {
            background-color: var(--background-color);
            color: var(--text-color);
        }

        .message-icon {
            color: var(--text-color);
        }

        .typing-indicator {
            display: flex;
            gap: 4px;
        }

        .typing-indicator span {
            width: 8px;
            height: 8px;
            background: var(--text-color);
            border-radius: 50%;
            animation: bounce 1.5s infinite ease-in-out;
        }

        .typing-indicator span:nth-child(2) {
            animation-delay: 0.2s;
        }

        .typing-indicator span:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes bounce {
            0%, 60%, 100% {
                transform: translateY(0);
            }
            30% {
                transform: translateY(-4px);
            }
        }

        .message.error {
            background-color: #fff2f2;
            border-color: #ff4444;
        }

        .model-selector-container {
            display: flex;
            gap: 10px;
            align-items: center;
            padding: 20px;
            border-bottom: 2px solid var(--text-color);
        }

        .model-selector {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            display: block;
            padding: 10px;
            border: 2px solid var(--text-color);
            border-radius: 5px;
            background-color: var(--background-color);
            color: var (--text-color);
            cursor: pointer;
            box-shadow: 4px 4px 0 var(--text-color);
            transition: all 0.3s ease;
        }

        .model-selector:hover {
            transform: translate(-2px, -2px);
            box-shadow: 6px 6px 0 var(--text-color);
        }

        .model-selector:focus {
            outline: none;
        }

        .add-model-btn {
            padding: 10px;
            border: 2px solid var(--text-color);
            border-radius: 5px;
            background-color: var(--button-background);
            color: var(--button-text);
            cursor: pointer;
            box-shadow: 4px 4px 0 var(--text-color);
            transition: all 0.3s ease;
        }

        .add-model-btn:hover {
            transform: translate(-2px, -2px);
            box-shadow: 6px 6px 0 var(--text-color);
        }

        .modal-content {
            border: 2px solid var(--text-color);
            box-shadow: 8px 8px 0 var(--text-color);
        }

        .modal-header, .modal-footer {
            border-color: var(--text-color);
        }

        .modal .btn {
            border: 2px solid var(--text-color);
            box-shadow: 4px 4px 0 var(--text-color);
            transition: all 0.3s ease;
        }

        .modal .btn:hover {
            transform: translate(-2px, -2px);
            box-shadow: 6px 6px 0 var(--text-color);
        }

        .chat-history-item {
            position: relative;
        }

        .delete-chat {
            position: absolute;
            right: 10px;
            opacity: 0;
            transition: opacity 0.2s;
        }

        .chat-history-item:hover .delete-chat {
            opacity: 1;
        }

        .message-actions {
            display: flex;
            gap: 12px;
            margin-top: 8px;
            transition: opacity 0.2s ease;
        }

        .message:hover .message-actions {
            opacity: 1;
        }

        .action-btn {
            background: none;
            border: none;
            padding: 4px;
            cursor: pointer;
            color: var(--text-color);
            opacity: 0.6;
            transition: all 0.2s ease;
        }

        .action-btn:hover {
            opacity: 1;
            transform: scale(1.1);
        }

        .action-btn i {
            font-size: 14px;
        }

        .message.editing .message-text {
            position: relative;
        }

        .message.editing textarea {
            width: 850px !important;  /* Force the width */
            min-height: 150px;
            padding: 16px;
            border: 2px solid var(--text-color);
            border-radius: 8px;
            background: var(--background-color);
            color: var(--text-color);
            font-family: inherit;
            font-size: 1rem;
            line-height: 1.6;
            resize: vertical;
            margin: 15px 0;
            box-shadow: 4px 4px 0 var(--text-color);
            transition: all 0.3s ease;
            display: block;  /* Add this */
            box-sizing: border-box;  /* Add this */
        }

        .edit-actions {
            display: flex;
            gap: 12px;
            margin-top: 8px;
        }

        .message {
            flex-direction: column;
        }

        .message-content {
            display: flex;
            gap: 12px;
            align-items: flex-start;
        }

        .regenerate-btn {
            color: black;
        }

        .remove-btn {
            color: black;
        }

        .edit-actions {
            display: flex;
            gap: 8px;
            margin-top: 8px;
        }

        .message.editing textarea {
            width: 100%;
            min-height: 100px;
            padding: 8px;
            border: 2px solid var(--text-color);
            border-radius: 4px;
            background: var(--background-color);
            color: var(--text-color);
            font-family: inherit;
            font-size: inherit;
            line-height: inherit;
            resize: vertical;
            margin-bottom: 8px;
            box-shadow: 4px 4px 0 var(--text-color);
        }

        .save-edit, .cancel-edit {
            color: var(--text-color);
        }

        .save-edit:hover, .cancel-edit:hover {
            transform: scale(1.1);
        }

        .message.editing .message-text {
            width: 100%;
            max-width: 800px;
        }

        .message.editing {
            width: 100%;
            max-width: 800px;
        }

        /* Update message editing styles */
        .message.editing {
            width: 850px;  /* Fixed width instead of 100% */
            max-width: 90%; /* Fallback for smaller screens */
            transition: all 0.3s ease;
            margin-left: auto;
            margin-right: auto;
        }

        .message.editing textarea {
            width: 850px;  /* Fixed width */
            min-height: 150px;
            padding: 16px;
            border: 2px solid var (--text-color);
            border-radius: 8px;
            background: var(--background-color);
            color: var(--text-color);
            font-family: inherit;
            font-size: 1rem;
            line-height: 1.6;
            resize: vertical;
            margin: 15px 0;
            box-shadow: 4px 4px 0 var(--text-color);
            transition: all 0.3s ease;
            max-width: 100%; /* Prevent overflow on small screens */
        }

        .message.editing {
            width: 100%;
            max-width: 1000px;  /* Increased from 800px */
            transition: all 0.3s ease;
        }

        .message.editing .message-text {
            width: 100%;
        }

        .message.editing textarea {
            width: 100%;
            min-height: 150px;  /* Increased from 120px */
            padding: 16px;      /* Increased from 12px */
            border: 2px solid var(--text-color);
            border-radius: 8px;  /* Increased from 4px */
            background: var(--background-color);
            color: var(--text-color);
            font-family: inherit;
            font-size: 1rem;    /* Explicit font size */
            line-height: 1.6;   /* Improved line height */
            resize: vertical;
            margin: 15px 0;     /* Increased from 10px */
            box-shadow: 4px 4px 0 var(--text-color);
            transition: all 0.3s ease;
        }

        .message.editing textarea:focus {
            outline: none;
            transform: translate(-2px, -2px);
            box-shadow: 6px 6px 0 var (--text-color);
        }

        .edit-actions {
            display: flex;
            gap: 12px;         /* Increased from 8px */
            margin-top: 12px;  /* Increased from 8px */
            padding: 0 4px;
        }

        .edit-actions .action-btn {
            padding: 8px 12px;  /* Added padding */
            border: 2px solid var(--text-color);
            border-radius: 4px;
            background: var(--background-color);
            box-shadow: 3px 3px 0 var(--text-color);
            opacity: 1;
        }

        .edit-actions .action-btn:hover {
            transform: translate(-2px, -2px);
            box-shadow: 5px 5px 0 var(--text-color);
        }

        .edit-actions .save-edit {
            color: #008000;  /* Green color for save */
        }

        .edit-actions .cancel-edit {
            color: #ff0000;  /* Red color for cancel */
        }

        .message.editing {
            padding: 20px;    /* Increased padding */
            margin: 20px 0;   /* Added margin */
        }

        /* Update editing styles to maintain message alignment */
        .message.editing {
            width: 900px;
            max-width: 100%;
            transition: all 0.3s ease;
        }

        .user-message.editing {
            margin-left: auto;
            margin-right: 0;
        }

        .ai-message.editing {
            margin-left: 0;
            margin-right: auto;
        }

        .message.editing textarea {
            width: 850px !important;
            min-height: 150px;
            padding: 16px;
            border: 2px solid var(--text-color);
            border-radius: 8px;
            background: var(--background-color);
            color: var(--text-color);
            font-family: inherit;
            font-size: 1rem;
            line-height: 1.6;
            resize: vertical;
            margin: 15px 0;
            box-shadow: 4px 4px 0 var(--text-color);
            transition: all 0.3s ease;
            display: block;
            box-sizing: border-box;
        }

        .user-message.editing .edit-actions {
            justify-content: flex-end;
        }

        .message-text code {
            background-color: #f0f0f0;
            padding: 2px 4px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 0.9em;
        }

        .message-text pre {
            background-color: #f0f0f0;
            padding: 15px;
            border-radius: 8px;
            overflow-x: auto;
            margin: 10px 0;
        }

        .message-text pre code {
            background-color: transparent;
            padding: 0;
        }

        .message-text blockquote {
            border-left: 4px solid var(--text-color);
            margin: 10px 0;
            padding: 10px 20px;
            background-color: rgba(0,0,0,0.05);
        }

        .message-text h1,
        .message-text h2,
        .message-text h3 {
            margin: 15px 0 10px 0;
            line-height: 1.2;
        }

        .message-text ul,
        .message-text ol {
            margin: 10px 0;
            padding-left: 20px;
        }

        .message-text li {
            margin: 5px 0;
        }

        .message.incomplete {
            opacity: 0.8;
            border-style: dashed;
        }

        .message.incomplete .regenerate-btn {
            animation: pulse 2s infinite;
            opacity: 1 !important;
            color: #ff6b00;
        }

        .message.incomplete .message-actions {
            opacity: 1 !important;
        }

        .message.incomplete {
            opacity: 0.7;
            border-style: dashed;
        }

        .message.incomplete .regenerate-btn {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        .progress {
            height: 20px;
            border: 2px solid var(--text-color);
            border-radius: 5px;
            background-color: var(--background-color);
            box-shadow: 4px 4px 0 var(--text-color);
        }

        .progress-bar {
            background-color: var(--button-background);
            transition: width 0.3s ease;
        }
    </style>
    <script src="js/chat_manager.js"></script>
</head>
<body>
    <div class="chat-container">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <!-- Title -->
            <div class="titan-title">Titan 'O'</div>
            
            <!-- Sidebar Toggle Button -->
            <button class="sidebar-toggle" id="sidebar-toggle">
                <i class="fas fa-chevron-left"></i>
                <span class="sidebar-toggle-text">Minimize</span>
            </button>
            
            <!-- New Chat Button -->
            <button class="new-chat-btn" id="new-chat-btn">
                <i class="fas fa-plus"></i> New Chat
            </button>

            <!-- Chat History -->
            <div class="chat-history" id="chat-history">
                <!-- Chat history will be populated by JavaScript -->
            </div>
            
            <!-- Settings Button -->
            <a href="settings.php" class="settings-btn">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
        </div>

        <!-- Main Content -->
        <div class="main-content" id="main-content">
            <!-- Add Model Selector -->
            <div class="model-selector-container">
                <select id="model-selector" class="model-selector">
                    <option value="">Loading models...</option>
                </select>
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
                                <div class="mb-3">
                                    <label for="modelName" class="form-label">Model Name (e.g., llama2:latest, deepseek-coder:6.7b)</label>
                                    <input type="text" class="form-control" id="modelName" required 
                                           placeholder="Enter model name (e.g., llama2:latest)">
                                </div>
                                <div id="downloadProgress" class="mt-3 d-none">
                                    <label class="form-label">Download Progress:</label>
                                    <div class="progress">
                                        <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                                    </div>
                                    <small class="text-muted" id="progressText"></small>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" id="saveModel">Pull Model</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="chat-messages" id="chat-messages">
                <div class="message ai-message">
                    <i class="message-icon fas fa-robot"></i>
                    <div class="message-text">
                        <strong>Hello! 👋</strong> I'm Titan 'O'. an website for local ai run, chat below here
                    </div>
                </div>
            </div>

            <!-- Input Container -->
            <div class="input-container">
                <form id="chat-form">
                    <div class="input-group">
                        <textarea 
                            class="form-control message-input" 
                            placeholder="Type your message here... (Press Enter to send, Shift+Enter for new line)" 
                            rows="1" 
                            id="message-input"
                        ></textarea>
                        <button type="submit" class="send-button" disabled>
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
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

        // Update model selection
        modelSelector.addEventListener('change', function() {
            currentModel = this.value;
            addMessage(`Switched to ${currentModel} model`, 'ai-message');
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

        // Add this after the variable declarations
        const INCOMPLETE_RESPONSE = '⌛ Response interrupted. Click regenerate to try again.';

        // Update the send message function
        async function sendMessage(message = null, isRegenerate = false) {
            const messageToSend = message || messageInput.value.trim();
            
            if (messageToSend) {
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
                
                messageInput.disabled = true;
                sendButton.disabled = true;
                
                // Create new AbortController for this request
                currentAbortController = new AbortController();
                
                // Add typing indicator
                const typingDiv = document.createElement('div');
                typingDiv.className = 'message ai-message typing';
                typingDiv.innerHTML = `
                    <div class="message-content">
                        <i class="message-icon fas fa-robot"></i>
                        <div class="message-text">
                            <div class="typing-indicator">
                                <span></span><span></span><span></span>
                            </div>
                        </div>
                    </div>
                `;
                document.getElementById('chat-messages').appendChild(typingDiv);
                
                try {
                    const response = await fetch('api/ollama_handler.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ 
                            message: messageToSend,
                            model: currentModel
                        }),
                        signal: currentAbortController.signal
                    });

                    const data = await response.json();
                    typingDiv.remove();

                    if (data.error) {
                        throw new Error(data.error);
                    }

                    if (data.response) {
                        addMessage(data.response, 'ai-message');
                        if (!isRegenerate) {
                            // Save message and update URL/history on first AI response
                            chatManager.addMessage(
                                chatManager.currentChatId || chatManager.pendingChat.id, 
                                data.response, 
                                true
                            );
                            
                            // No need to call updateChatHistory() here as it's handled in ChatManager
                        }
                    }
                } catch (error) {
                    console.error('Error:', error);
                    typingDiv.remove();
                    
                    if (error.name === 'AbortError') {
                        // Get the chat ID before the abort
                        const lastActiveChatId = chatManager.lastActiveChatId;
                        
                        // Add incomplete message to the last active chat
                        addMessage(INCOMPLETE_RESPONSE, 'ai-message incomplete');
                        chatManager.addIncompleteMessage(INCOMPLETE_RESPONSE);
                        
                        // If we're in a different chat now, switch back to show the error
                        const currentUrlChatId = getChatIdFromURL();
                        if (currentUrlChatId !== lastActiveChatId && lastActiveChatId) {
                            loadChatHistory(lastActiveChatId);
                        }
                        
                        updateChatHistory();
                        return;
                    }
                    
                    addMessage('Sorry, I encountered an error while processing your request: ' + error.message, 'ai-message error');
                } finally {
                    currentAbortController = null;
                    messageInput.disabled = false;
                    messageInput.focus();
                    sendButton.disabled = false;
                }
            }
        }

        // Update addMessage function to handle code blocks and formatting
        function addMessage(message, className, originalQuestion = '') {
            const messagesContainer = document.getElementById('chat-messages');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${className}`;
            const iconClass = className === 'user-message' ? 'fa-user' : 'fa-robot';
            
            // Check if this is an interrupted response
            const isIncomplete = message === INCOMPLETE_RESPONSE;
            if (isIncomplete) {
                messageDiv.classList.add('incomplete');
            }
            
            const formattedMessage = formatMessage(message);
            
            // Update the message actions HTML in addMessage function
            messageDiv.innerHTML = `
                <div class="message-content">
                    <i class="message-icon fas ${iconClass}"></i>
                    <div class="message-text">${formattedMessage}</div>
                </div>
                <div class="message-actions">
                    ${className === 'ai-message' ? 
                        isIncomplete ? 
                            `<button class="action-btn regenerate-btn" title="Regenerate">
                                <i class="fas fa-sync"></i>
                            </button>` 
                        : 
                            `<button class="action-btn copy-btn" title="Copy">
                                <i class="fas fa-copy"></i>
                            </button>
                            <button class="action-btn edit-btn" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="action-btn regenerate-btn" title="Regenerate">
                                <i class="fas fa-sync"></i>
                            </button>`
                    : 
                        `<button class="action-btn edit-btn" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="action-btn remove-btn" title="Remove">
                            <i class="fas fa-trash"></i>
                        </button>`
                    }
                </div>
            `;

            // Add remove functionality for user messages
            if (className === 'user-message') {
                if (!isIncomplete && messageDiv.querySelector('.edit-btn')) {
                    // Add edit and remove button handlers only for complete messages
                    messageDiv.querySelector('.remove-btn').addEventListener('click', () => {
                        if (confirm('This will remove this message and all subsequent messages. Are you sure?')) {
                            const messageIndex = chatManager.getMessageIndex(chatManager.currentChatId, message);
                            if (messageIndex !== -1) {
                                // Remove all messages from DOM starting with this one
                                let currentElement = messageDiv;
                                while (currentElement.nextElementSibling) {
                                    currentElement.nextElementSibling.remove();
                                }
                                messageDiv.remove();

                                // Remove messages from chat manager
                                chatManager.removeMessage(chatManager.currentChatId, messageIndex);
                                updateChatHistory();
                            }
                        }
                    });
                }
            }

            if (className === 'ai-message') {
                // Always add regenerate button handler
                messageDiv.querySelector('.regenerate-btn').addEventListener('click', async () => {
                    const questionMessage = messageDiv.previousElementSibling;
                    if (questionMessage && questionMessage.classList.contains('user-message')) {
                        // Get all subsequent messages
                        let currentElement = messageDiv;
                        const subsequentMessages = [];
                        while (currentElement.nextElementSibling) {
                            subsequentMessages.push(currentElement.nextElementSibling);
                            currentElement = currentElement.nextElementSibling;
                        }
                        
                        // Remove all subsequent messages
                        subsequentMessages.forEach(msg => msg.remove());
                        
                        // Remove this AI message
                        messageDiv.remove();
                        
                        // Get the index of the current message in chat history
                        const messageIndex = chatManager.getMessageIndex(chatManager.currentChatId, message);
                        if (messageIndex !== -1) {
                            // Remove all subsequent messages from chat manager
                            chatManager.chats[chatManager.currentChatId].messages.splice(messageIndex);
                            chatManager.saveChats();
                        }
                        
                        // Regenerate the AI response
                        const userQuestion = questionMessage.querySelector('.message-text').textContent;
                        await sendMessage(userQuestion, true);
                    }
                });

                // Add copy and edit handlers only for complete messages
                if (!isIncomplete) {
                    if (messageDiv.querySelector('.copy-btn')) {
                        messageDiv.querySelector('.copy-btn').addEventListener('click', () => {
                            navigator.clipboard.writeText(message).then(() => {
                                const btn = messageDiv.querySelector('.copy-btn');
                                btn.innerHTML = '<i class="fas fa-check"></i>';
                                setTimeout(() => {
                                    btn.innerHTML = '<i class="fas fa-copy"></i>';
                                }, 2000);
                            });
                        });
                    }
                    if (messageDiv.querySelector('.edit-btn')) {
                        messageDiv.querySelector('.edit-btn').addEventListener('click', () => {
                            const messageText = messageDiv.querySelector('.message-text');
                            const originalText = message;
                            
                            // Add editing class to message
                            messageDiv.classList.add('editing');
                            
                            // Create textarea with original content
                            const textarea = document.createElement('textarea');
                            textarea.value = originalText;
                            // Remove the inline width style
                            textarea.style.minHeight = '150px';
                            
                            // Create edit actions
                            const editActions = document.createElement('div');
                            editActions.className = 'edit-actions';
                            editActions.innerHTML = `
                                <button class="action-btn save-edit" title="Save">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button class="action-btn cancel-edit" title="Cancel">
                                    <i class="fas fa-times"></i>
                                </button>
                            `;
                            
                            // Replace content with textarea and actions
                            messageText.innerHTML = '';
                            messageText.appendChild(textarea);
                            messageText.appendChild(editActions);
                            
                            // Focus textarea
                            textarea.focus();
                            
                            // Handle save
                            editActions.querySelector('.save-edit').addEventListener('click', () => {
                                const newText = textarea.value.trim();
                                if (newText && newText !== originalText) {
                                    // If it's a user message, update in chat manager
                                    if (className === 'user-message') {
                                        const messageIndex = chatManager.getMessageIndex(chatManager.currentChatId, originalText);
                                        if (messageIndex !== -1) {
                                            chatManager.chats[chatManager.currentChatId].messages[messageIndex].content = newText;
                                            chatManager.saveChats();
                                            
                                            // Remove subsequent messages as they may no longer be relevant
                                            let currentElement = messageDiv;
                                            while (currentElement.nextElementSibling) {
                                                currentElement.nextElementSibling.remove();
                                            }
                                            
                                            // Send new message to get updated response
                                            sendMessage(newText, true);
                                        }
                                    }
                                    messageText.innerHTML = formatMessage(newText);
                                } else {
                                    messageText.innerHTML = formatMessage(originalText);
                                }
                                messageDiv.classList.remove('editing');
                            });
                            
                            // Handle cancel
                            editActions.querySelector('.cancel-edit').addEventListener('click', () => {
                                messageText.innerHTML = formatMessage(originalText);
                                messageDiv.classList.remove('editing');
                            });
                            
                            // Handle Escape key to cancel
                            textarea.addEventListener('keydown', (e) => {
                                if (e.key === 'Escape') {
                                    messageText.innerHTML = formatMessage(originalText);
                                    messageDiv.classList.remove('editing');
                                }
                            });
                        });
                    }
                }
            }
            messagesContainer.appendChild(messageDiv);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        // Update formatMessage function with Markdown support
        function formatMessage(message) {
            // Bold (**text**)
            message = message.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
            
            // Italic (*text*)
            message = message.replace(/\*(.*?)\*/g, '<em>$1</em>');
            
            // Strikethrough (~~text~~)
            message = message.replace(/~~(.*?)~~/g, '<del>$1</del>');
            
            // Code blocks (```text```)
            message = message.replace(/```([\s\S]*?)```/g, '<pre><code>$1</code></pre>');
            
            // Inline code (`text`)
            message = message.replace(/`([^`]+)`/g, '<code>$1</code>');
            
            // Headers (# text)
            message = message.replace(/^# (.*$)/gm, '<h1>$1</h1>');
            message = message.replace(/^## (.*$)/gm, '<h2>$1</h2>');
            message = message.replace(/^### (.*$)/gm, '<h3>$1</h3>');
            
            // Blockquotes (> text)
            message = message.replace(/^> (.*$)/gm, '<blockquote>$1</blockquote>');
            
            // Lists
            // Unordered lists (- text)
            message = message.replace(/^- (.*$)/gm, '<li>$1</li>');
            message = message.replace(/(<li>.*<\/li>)/gs, '<ul>$1</ul>');
            
            // Ordered lists (1. text)
            message = message.replace(/^\d+\. (.*$)/gm, '<li>$1</li>');
            message = message.replace(/(<li>.*<\/li>)/gs, '<ol>$1></ol>');
            
            // Bullet points
            message = message.replace(/^[•]\s/gm, '<br>• ');
            
            // Add spacing after periods for readability
            message = message.replace(/\./g, '. ');
            
            // Convert URLs to links
            message = message.replace(
                /(https?:\/\/[^\s]+)/g,
                '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>'
            );
            
            // Add line breaks
            message = message.replace(/\n/g, '<br>');
            
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
        function loadChatHistory(chatId) {
            if (currentAbortController) {
                currentAbortController.abort();
                currentAbortController = null;
            }
            
            // Clear any pending chat when loading a saved chat
            chatManager.pendingChat = null;
            
            const chat = chatManager.getChatHistory(chatId);
            if (!chat) return;

            // Update URL first
            updateURL(chatId);
            
            chatManager.currentChatId = chatId;
            updateChatHistory();

            const chatMessages = document.getElementById('chat-messages');
            chatMessages.innerHTML = '';

            chat.messages.forEach(msg => {
                const className = msg.isAI ? 'ai-message' : 'user-message';
                // Add incomplete class if the message was incomplete
                const fullClassName = msg.isIncomplete ? `${className} incomplete` : className;
                addMessage(msg.content, fullClassName);
            });
        }

        // Add delete chat function
        function deleteChat(chatId) {
            event.stopPropagation();
            if (confirm('Are you sure you want to delete this chat?')) {
                chatManager.deleteChat(chatId);
                if (chatManager.currentChatId === chatId) {
                    chatManager.currentChatId = null;
                    document.getElementById('chat-messages').innerHTML = '';
                }
                updateChatHistory();
            }
        }

        // Function to update chat history display
        function updateChatHistory() {
            const chatHistoryDiv = document.getElementById('chat-history');
            chatHistoryDiv.innerHTML = '';
            
            const chats = chatManager.loadChats();
            Object.values(chats)
                .sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp))
                .forEach(chat => {
                    const div = document.createElement('div');
                    div.className = `chat-history-item${chat.id === chatManager.currentChatId ? ' active' : ''}`;
                    div.setAttribute('data-chat-id', chat.id);
                    div.innerHTML = `
                        <i class="fas fa-message"></i>
                        <span class="chat-title">${chat.title}</span>
                        <button class="delete-chat" onclick="deleteChat('${chat.id}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    `;
                    div.addEventListener('click', () => loadChatHistory(chat.id));
                    chatHistoryDiv.appendChild(div);
                });
        }

        // Add this function to get chat ID from URL
        function getChatIdFromURL() {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get('chat');
        }

        // Add this code to handle back/forward browser navigation
        window.addEventListener('popstate', function(event) {
            const chatId = getChatIdFromURL();
            if (chatId) {
                loadChatHistory(chatId);
            } else {
                // Handle return to base URL (no chat selected)
                chatManager.currentChatId = null;
                document.getElementById('chat-messages').innerHTML = `
                    <div class="message ai-message">
                        <i class="message-icon fas fa-robot"></i>
                        <div class="message-text">
                            <strong>Hello! 👋</strong> I'm Titano AI. How can I assist you today? 
                            Feel free to ask me anything - I'm here to help with information, 
                            ideas, or creative solutions!
                        </div>
                    </div>
                `;
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

        // Remove or comment out these lines since they're now handled in DOMContentLoaded
        // fetchModels();
        // updateChatHistory();

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

            if (modelName) {
                try {
                    saveBtn.disabled = true;
                    progressDiv.classList.remove('d-none');
                    progressBar.style.width = '0%';
                    progressText.textContent = 'Starting download...';

                    // Create EventSource for progress updates
                    const eventSource = new EventSource(`api/add_model.php?model=${encodeURIComponent(modelName)}`);

                    eventSource.onmessage = (event) => {
                        const data = JSON.parse(event.data);
                        
                        if (data.status === 'pulling') {
                            const progress = Math.round((data.completed / data.total) * 100);
                            progressBar.style.width = `${progress}%`;
                            progressText.textContent = `Downloading: ${progress}% (${formatBytes(data.completed)} / ${formatBytes(data.total)})`;
                        } else if (data.status === 'complete') {
                            progressBar.style.width = '100%';
                            progressText.textContent = 'Download complete!';
                            eventSource.close();
                            
                            // Refresh model list and close modal after a delay
                            setTimeout(() => {
                                fetchModels();
                                const modal = bootstrap.Modal.getInstance(document.getElementById('addModelModal'));
                                modal.hide();
                                document.getElementById('addModelForm').reset();
                                progressDiv.classList.add('d-none');
                                saveBtn.disabled = false;
                            }, 1500);
                        } else if (data.status === 'error') {
                            throw new Error(data.error);
                        }
                    };

                    eventSource.onerror = () => {
                        eventSource.close();
                        throw new Error('Failed to connect to server');
                    };

                } catch (error) {
                    progressText.textContent = `Error: ${error.message}`;
                    progressDiv.classList.add('text-danger');
                    saveBtn.disabled = false;
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
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>