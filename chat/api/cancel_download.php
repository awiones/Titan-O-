<?php
header('Content-Type: application/json');

if (!isset($_GET['model'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Model name is required']);
    exit;
}

// Find and kill the download process
$temp_dir = sys_get_temp_dir();
foreach (glob($temp_dir . "/dl_*_pid.txt") as $pidfile) {
    $pid = (int)file_get_contents($pidfile);
    if ($pid) {
        // Force kill the process and its children on Windows
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            exec("taskkill /F /T /PID $pid 2>nul");
            exec("taskkill /F /IM ollama.exe 2>nul");
        } else {
            // For Linux/Unix systems
            exec("kill -9 $pid 2>/dev/null");
            exec("pkill -9 -f 'ollama pull' 2>/dev/null");
        }
        
        // Clean up status files
        $status_file = str_replace('_pid.txt', '_status.txt', $pidfile);
        @unlink($status_file);
        @unlink($pidfile);
    }
}

// Try to stop Ollama download through its API
$ch = curl_init('http://localhost:11434/api/stop');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 5
]);
curl_exec($ch);
curl_close($ch);

echo json_encode(['status' => 'success', 'message' => 'Download cancelled']);
