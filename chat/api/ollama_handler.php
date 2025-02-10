<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'response_handler.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $message = $data['message'] ?? '';
    $model = $data['model'] ?? 'llama2';
    $chatId = $data['chat_id'] ?? null;
    $resume = $data['resume'] ?? false;
    $messageId = $data['message_id'] ?? uniqid();
    
    // Define response accumulator outside the callback
    $accumulatedResponse = '';
    
    if ($resume && $chatId) {
        $state = getResponseState($chatId);
        if ($state && $state['status'] !== 'completed' && $state['message_id'] === $messageId) {
            $accumulatedResponse = $state['content'];
            echo "data: " . json_encode([
                'response' => $state['content'],
                'full_response' => $state['content'],
                'message_id' => $messageId
            ]) . "\n\n";
            ob_flush();
            flush();
        }
    }
    
    // Configure Ollama endpoint
    $ollamaEndpoint = 'http://127.0.0.1:11434/api/generate';
    
    // Initialize cURL
    $ch = curl_init($ollamaEndpoint);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode([
            'model' => $model,
            'prompt' => $message,
            'stream' => true
        ]),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_WRITEFUNCTION => function($curl, $data) use ($chatId, $messageId, &$accumulatedResponse) {
            // Each chunk needs to be processed line by line
            $lines = explode("\n", $data);
            foreach ($lines as $line) {
                if (empty(trim($line))) continue;
                
                $jsonData = json_decode($line, true);
                if ($jsonData && isset($jsonData['response'])) {
                    $accumulatedResponse .= $jsonData['response'];
                    
                    echo "data: " . json_encode([
                        'response' => $jsonData['response'],
                        'full_response' => $accumulatedResponse,
                        'message_id' => $messageId
                    ]) . "\n\n";
                    
                    if ($chatId) {
                        saveResponseState($chatId, $accumulatedResponse, 'in_progress', $messageId);
                    }
                    
                    ob_flush();
                    flush();
                }
            }
            return strlen($data);
        }
    ]);

    $success = curl_exec($ch);
    
    if ($success && $chatId) {
        // Send final complete message
        echo "data: " . json_encode([
            'response' => '',
            'full_response' => $accumulatedResponse,
            'message_id' => $messageId,
            'status' => 'completed'
        ]) . "\n\n";
        
        saveResponseState($chatId, $accumulatedResponse, 'completed', $messageId);
    } elseif (!$success) {
        echo "data: " . json_encode(['error' => curl_error($ch)]) . "\n\n";
        if ($chatId) {
            saveResponseState($chatId, curl_error($ch), 'error', $messageId);
        }
    }
    
    curl_close($ch);
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
