<?php
header('Content-Type: application/json');

function getGPUInfo() {
    if (PHP_OS === 'Windows') {
        exec('nvidia-smi --query-gpu=name,memory.total,memory.used --format=csv,noheader', $output);
        if (!empty($output)) {
            return ['available' => true, 'info' => $output[0]];
        }
    } else {
        exec('lspci | grep -i nvidia', $output);
        if (!empty($output)) {
            return ['available' => true, 'info' => $output[0]];
        }
    }
    return ['available' => false, 'info' => 'No GPU detected'];
}

function getSystemMemory() {
    if (PHP_OS === 'Windows') {
        $cmd = 'wmic OS get FreePhysicalMemory,TotalVisibleMemorySize /Value';
        exec($cmd, $output);
        $memory = [];
        foreach ($output as $line) {
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line);
                $memory[$key] = trim($value);
            }
        }
        return [
            'total' => round($memory['TotalVisibleMemorySize'] / 1024 / 1024, 2),
            'free' => round($memory['FreePhysicalMemory'] / 1024 / 1024, 2)
        ];
    } else {
        $free = shell_exec('free -g');
        $free = (string)trim($free);
        $free_arr = explode("\n", $free);
        $mem = explode(" ", $free_arr[1]);
        $mem = array_filter($mem);
        $mem = array_merge($mem);
        return [
            'total' => $mem[1],
            'free' => $mem[3]
        ];
    }
}

// Get Ollama version and models info
$ollamaEndpoint = 'http://localhost:11434/api/tags';
$ch = curl_init($ollamaEndpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$models = json_decode($response, true);

// Calculate total size of models
$totalSize = 0;
if (isset($models['models'])) {
    foreach ($models['models'] as $model) {
        $totalSize += $model['size'];
    }
}

// Get Ollama version
$versionEndpoint = 'http://localhost:11434/api/version';
$ch = curl_init($versionEndpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$versionResponse = curl_exec($ch);
$version = json_decode($versionResponse, true);

curl_close($ch);

// Compile system information
$systemInfo = [
    'version' => $version['version'] ?? 'Unknown',
    'total_size' => round($totalSize / 1024 / 1024 / 1024, 2), // Convert to GB
    'system_memory' => getSystemMemory(),
    'gpu' => getGPUInfo(),
    'timestamp' => date('Y-m-d H:i:s')
];

echo json_encode($systemInfo);
