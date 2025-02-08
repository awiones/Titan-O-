<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

if (!isset($_GET['model'])) {
    echo "data: " . json_encode(['status' => 'error', 'error' => 'Model name is required']) . "\n\n";
    exit;
}

$modelName = $_GET['model'];

// Initialize cURL session
$ch = curl_init('http://localhost:11434/api/pull');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode(['name' => $modelName]),
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_WRITEFUNCTION => function($ch, $data) {
        $lines = explode("\n", $data);
        foreach ($lines as $line) {
            if (empty($line)) continue;
            
            $json = json_decode($line, true);
            if ($json === null) continue;

            if (isset($json['error'])) {
                echo "data: " . json_encode([
                    'status' => 'error',
                    'error' => $json['error']
                ]) . "\n\n";
                return 0;
            }

            if (isset($json['completed'])) {
                echo "data: " . json_encode([
                    'status' => 'pulling',
                    'completed' => $json['completed'],
                    'total' => $json['total']
                ]) . "\n\n";
            }

            if (isset($json['status']) && $json['status'] === 'success') {
                echo "data: " . json_encode([
                    'status' => 'complete'
                ]) . "\n\n";
                return 0;
            }
        }
        return strlen($data);
    }
]);

curl_exec($ch);

if (curl_errno($ch)) {
    echo "data: " . json_encode([
        'status' => 'error',
        'error' => 'Error connecting to Ollama: ' . curl_error($ch)
    ]) . "\n\n";
}

curl_close($ch);
