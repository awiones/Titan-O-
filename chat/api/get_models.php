<?php
header('Content-Type: application/json');

$ollamaEndpoint = 'http://localhost:11434/api/tags';

$ch = curl_init($ollamaEndpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    http_response_code(500);
    echo json_encode(['error' => 'Error connecting to Ollama: ' . curl_error($ch)]);
    exit;
}

curl_close($ch);

echo $response;
