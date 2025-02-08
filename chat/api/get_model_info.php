<?php
header('Content-Type: application/json');

if (!isset($_GET['model'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Model name is required']);
    exit;
}

$modelName = $_GET['model'];
$ollamaEndpoint = 'http://localhost:11434/api/show';

// Prepare request data
$requestData = json_encode(['name' => $modelName]);

// Initialize cURL session
$ch = curl_init($ollamaEndpoint);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $requestData,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_TIMEOUT => 10
]);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    http_response_code(500);
    echo json_encode(['error' => 'Error connecting to Ollama: ' . curl_error($ch)]);
    exit;
}

curl_close($ch);

// Forward the response from Ollama
echo $response;
