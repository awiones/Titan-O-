<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Disable output buffering
if (ob_get_level()) ob_end_clean();

if (!isset($_GET['model'])) {
    echo "data: " . json_encode(['status' => 'error', 'error' => 'Model name is required']) . "\n\n";
    flush();
    exit;
}

$model = $_GET['model'];

// Start the pull process
$descriptorspec = array(
    0 => array("pipe", "r"),  // stdin
    1 => array("pipe", "w"),  // stdout
    2 => array("pipe", "w")   // stderr
);

$process = proc_open("ollama pull $model 2>&1", $descriptorspec, $pipes);

if (is_resource($process)) {
    // Set pipes to non-blocking mode
    stream_set_blocking($pipes[1], false);
    stream_set_blocking($pipes[2], false);

    while (true) {
        $status = proc_get_status($process);
        
        // Check if process was terminated externally
        if (!$status['running']) {
            $stderr = stream_get_contents($pipes[2]);
            if (!empty($stderr)) {
                echo "data: " . json_encode(['status' => 'cancelled', 'error' => 'Download was cancelled']) . "\n\n";
                flush();
            }
            break;
        }

        $output = fgets($pipes[1]);
        if ($output) {
            $data = json_decode($output, true);
            if ($data && isset($data['status'])) {
                echo "data: " . $output . "\n\n";
                flush();
            }
        }

        // Check for errors
        $error = fgets($pipes[2]);
        if ($error) {
            echo "data: " . json_encode(['status' => 'error', 'error' => trim($error)]) . "\n\n";
            flush();
            break;
        }

        // Small delay to prevent CPU overload
        usleep(100000); // 100ms delay
    }

    fclose($pipes[0]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    proc_close($process);
} else {
    echo "data: " . json_encode(['status' => 'error', 'error' => 'Failed to start download process']) . "\n\n";
    flush();
}
