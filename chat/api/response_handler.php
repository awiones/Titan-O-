<?php
session_start();
header('Content-Type: application/json');

function saveResponseState($chatId, $content, $status = 'in_progress', $messageId = null) {
    $stateFile = "../temp/responses/{$chatId}_state.json";
    $stateDir = dirname($stateFile);
    
    if (!is_dir($stateDir)) {
        mkdir($stateDir, 0777, true);
    }
    
    $state = [
        'status' => $status,
        'content' => $content,
        'timestamp' => time(),
        'message_id' => $messageId,
        'user_message' => isset($_POST['message']) ? $_POST['message'] : null,
        'last_update' => time()
    ];
    
    file_put_contents($stateFile, json_encode($state));
    return $state['message_id'];
}

function getResponseState($chatId) {
    $stateFile = "../temp/responses/{$chatId}_state.json";
    if (file_exists($stateFile)) {
        return json_decode(file_get_contents($stateFile), true);
    }
    return null;
}

// Add cleanup function
function cleanupOldResponses() {
    $responseDir = "../temp/responses/";
    $files = glob($responseDir . "*_state.json");
    $now = time();
    
    foreach ($files as $file) {
        $state = json_decode(file_get_contents($file), true);
        // Clean up files older than 1 hour or completed responses
        if ($state['status'] === 'completed' || ($now - $state['last_update']) > 3600) {
            unlink($file);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $chatId = $_GET['chat_id'] ?? null;
    if ($chatId) {
        $state = getResponseState($chatId);
        if ($state) {
            // Only return active states less than 1 hour old AND in_progress AND with content
            if ($state['status'] === 'in_progress' && 
                (time() - $state['last_update']) < 3600 && 
                !empty(trim($state['content']))) {
                echo json_encode($state);
            } else {
                echo json_encode(null);
                // Clean up the state file
                $stateFile = "../temp/responses/{$chatId}_state.json";
                if (file_exists($stateFile)) {
                    unlink($stateFile);
                }
            }
        } else {
            echo json_encode(null);
        }
    }
    
    // Periodically clean up old responses
    if (rand(1, 10) === 1) { // 10% chance to run cleanup
        cleanupOldResponses();
    }
}
