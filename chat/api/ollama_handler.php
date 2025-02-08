<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $message = $data['message'] ?? '';
    $model = $data['model'] ?? 'llama2';
    
    // Configure Ollama endpoint
    $ollamaEndpoint = 'http://localhost:11434/api/generate';
    
    // Prepare the request data
    $requestData = [
        'model' => $model,
        'prompt' => $message,
        'stream' => false  // Changed to false for simpler handling
    ];

    // Initialize cURL session
    $ch = curl_init($ollamaEndpoint);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($requestData),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT => 30, // Add timeout of 30 seconds
        CURLOPT_CONNECTTIMEOUT => 5 // Connection timeout of 5 seconds
    ]);

    // Execute cURL request
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        http_response_code(500);
        echo json_encode(['error' => 'Error connecting to Ollama: ' . curl_error($ch)]);
        exit;
    }

    curl_close($ch);

    // Process the response
    $responseData = json_decode($response, true);
    
    if ($responseData && isset($responseData['response'])) {
        echo json_encode([
            'response' => $responseData['response']
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Invalid response from Ollama']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
