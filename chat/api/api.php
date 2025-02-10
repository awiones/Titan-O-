<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Error handling function
function sendError($message, $code = 500) {
    http_response_code($code);
    echo json_encode(['error' => $message]);
    exit;
}

// Function to check if Ollama is running
function checkOllamaStatus() {
    $ch = curl_init('http://localhost:11434/api/tags');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $httpCode === 200;
}

// Function to get system memory info
function getSystemMemory() {
    if (PHP_OS === 'Linux') {
        $memInfo = file_get_contents('/proc/meminfo');
        preg_match_all('/(\w+):\s+(\d+)\s/', $memInfo, $matches);
        $memInfo = array_combine($matches[1], $matches[2]);
        
        return [
            'total' => round($memInfo['MemTotal'] / 1024 / 1024, 2),
            'free' => round($memInfo['MemFree'] / 1024 / 1024, 2),
            'available' => round($memInfo['MemAvailable'] / 1024 / 1024, 2),
            'cached' => round($memInfo['Cached'] / 1024 / 1024, 2)
        ];
    } else if (PHP_OS === 'WINNT') {
        $cmd = "wmic OS get FreePhysicalMemory,TotalVisibleMemorySize /Value";
        exec($cmd, $output);
        $memory = [];
        foreach ($output as $line) {
            if (preg_match('/(\w+)=(\d+)/', $line, $matches)) {
                $memory[$matches[1]] = $matches[2];
            }
        }
        
        return [
            'total' => round($memory['TotalVisibleMemorySize'] / 1024, 2),
            'free' => round($memory['FreePhysicalMemory'] / 1024, 2),
            'available' => round($memory['FreePhysicalMemory'] / 1024, 2),
            'cached' => 0
        ];
    }
    
    return null;
}

// Function to check GPU status
function getGPUInfo() {
    if (PHP_OS === 'Linux') {
        exec('nvidia-smi --query-gpu=name,memory.total,memory.used,temperature.gpu --format=csv,noheader,nounits', $output);
        if (!empty($output)) {
            $gpuData = str_getcsv($output[0]);
            return [
                'available' => true,
                'name' => $gpuData[0],
                'total_memory' => $gpuData[1],
                'used_memory' => $gpuData[2],
                'temperature' => $gpuData[3]
            ];
        }
    } else if (PHP_OS === 'WINNT') {
        exec('nvidia-smi --query-gpu=name,memory.total,memory.used,temperature.gpu --format=csv,noheader,nounits', $output);
        if (!empty($output)) {
            $gpuData = str_getcsv($output[0]);
            return [
                'available' => true,
                'name' => $gpuData[0],
                'total_memory' => $gpuData[1],
                'used_memory' => $gpuData[2],
                'temperature' => $gpuData[3]
            ];
        }
    }
    
    return ['available' => false];
}

// Function to get installed models
function getInstalledModels() {
    $ch = curl_init('http://localhost:11434/api/tags');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    
    if ($response) {
        $data = json_decode($response, true);
        return $data['models'] ?? [];
    }
    
    return [];
}

// Function to get detailed model info
function getModelInfo($modelName) {
    $ch = curl_init("http://localhost:11434/api/show");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode(['name' => $modelName])
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    
    if ($response) {
        return json_decode($response, true);
    }
    
    return null;
}

// Main API handler
try {
    $ollamaStatus = checkOllamaStatus();
    $systemMemory = getSystemMemory();
    $gpuInfo = getGPUInfo();
    $models = getInstalledModels();
    
    // Calculate total model size
    $totalModelSize = 0;
    foreach ($models as $model) {
        $totalModelSize += $model['size'] ?? 0;
    }
    
    // Get Ollama version (if available)
    $ollamaVersion = 'Unknown';
    if ($ollamaStatus) {
        $ch = curl_init('http://localhost:11434/api/version');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        if ($response) {
            $versionData = json_decode($response, true);
            $ollamaVersion = $versionData['version'] ?? 'Unknown';
        }
    }
    
    // Prepare response data
    $response = [
        'status' => [
            'ollama_running' => $ollamaStatus,
            'last_check' => date('Y-m-d H:i:s'),
            'version' => $ollamaVersion
        ],
        'system' => [
            'os' => PHP_OS,
            'memory' => $systemMemory,
            'gpu' => $gpuInfo
        ],
        'models' => [
            'count' => count($models),
            'total_size' => round($totalModelSize / (1024 * 1024 * 1024), 2), // Convert to GB
            'installed' => $models
        ],
        'endpoints' => [
            'base' => 'http://localhost:11434',
            'generate' => '/api/generate',
            'tags' => '/api/tags',
            'show' => '/api/show'
        ],
        'capabilities' => [
            'gpu_acceleration' => $gpuInfo['available'],
            'streaming' => true,
            'concurrent_requests' => true
        ]
    ];
    
    // Handle specific model details request
    if (isset($_GET['model'])) {
        $modelInfo = getModelInfo($_GET['model']);
        if ($modelInfo) {
            $response['model_details'] = $modelInfo;
        } else {
            $response['model_details'] = ['error' => 'Model not found'];
        }
    }
    
    // Add performance metrics if available
    if ($ollamaStatus) {
        $response['performance'] = [
            'memory_usage' => [
                'total' => $systemMemory ? $systemMemory['total'] : 'N/A',
                'available' => $systemMemory ? $systemMemory['available'] : 'N/A',
                'used_percentage' => $systemMemory ? 
                    round(($systemMemory['total'] - $systemMemory['available']) / $systemMemory['total'] * 100, 2) : 'N/A'
            ],
            'gpu_usage' => $gpuInfo['available'] ? [
                'memory_used' => $gpuInfo['used_memory'],
                'memory_total' => $gpuInfo['total_memory'],
                'temperature' => $gpuInfo['temperature']
            ] : null
        ];
    }
    
    echo json_encode($response, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    sendError('Internal server error: ' . $e->getMessage());
}
