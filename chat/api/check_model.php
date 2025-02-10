<?php
header('Content-Type: application/json');

if (!isset($_GET['model'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Model name is required']);
    exit;
}

$model = $_GET['model'];

function checkHuggingFaceModel($model) {
    // Check if model follows user/model format
    if (preg_match('/^[a-zA-Z0-9-]+\/[a-zA-Z0-9-]+$/', $model)) {
        $url = "https://huggingface.co/api/models/" . urlencode($model);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_USERAGENT => 'Mozilla/5.0',
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $modelData = json_decode($response, true);
            return [
                'exists' => true,
                'source' => 'Hugging Face',
                'author' => $modelData['author'] ?? explode('/', $model)[0],
                'name' => $modelData['name'] ?? explode('/', $model)[1],
                'url' => "https://huggingface.co/$model"
            ];
        }
    }
    
    return ['exists' => false];
}

function checkOllamaLibrary($model) {
    // Remove any version/tag part for the check
    $modelName = explode(':', $model)[0];
    
    // First try the new library endpoint
    $libraryUrl = "https://ollama.com/library/" . urlencode($modelName);
    $ch = curl_init($libraryUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_USERAGENT => 'Mozilla/5.0',
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        return [
            'exists' => true,
            'source' => 'Ollama Library',
            'name' => $modelName,
            'url' => $libraryUrl
        ];
    }
    
    // If not found in library, try the API registry
    $registryUrl = "https://ollama.ai/api/registry/" . urlencode($modelName);
    $ch = curl_init($registryUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_USERAGENT => 'Mozilla/5.0',
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        return [
            'exists' => true,
            'source' => 'Ollama Registry',
            'name' => $modelName,
            'url' => "https://ollama.com/library/$modelName"
        ];
    }
    
    return ['exists' => false];
}

// Check if model exists locally
$localEndpoint = 'http://localhost:11434/api/tags';
$ch = curl_init($localEndpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$localResponse = curl_exec($ch);
curl_close($ch);

$localModels = json_decode($localResponse, true);
$isLocallyAvailable = false;

if ($localModels && isset($localModels['models'])) {
    foreach ($localModels['models'] as $localModel) {
        if ($localModel['name'] === $model) {
            $isLocallyAvailable = true;
            break;
        }
    }
}

// Determine if it's an Ollama model or Hugging Face model
if (strpos($model, '/') === false) {
    // Ollama model
    $ollamaCheck = checkOllamaLibrary($model);
    echo json_encode([
        'exists' => $ollamaCheck['exists'],
        'isLocal' => $isLocallyAvailable,
        'source' => $ollamaCheck['source'] ?? '',
        'name' => $ollamaCheck['name'] ?? $model,
        'url' => $ollamaCheck['url'] ?? '',
        'type' => 'ollama',
        'message' => $isLocallyAvailable 
            ? "Model is already installed locally" 
            : ($ollamaCheck['exists'] 
                ? "Model is available from Ollama. You can pull it using: ollama pull $model" 
                : "Model not found in Ollama library or registry")
    ]);
} else {
    // Hugging Face model
    $huggingFaceCheck = checkHuggingFaceModel($model);
    echo json_encode([
        'exists' => $huggingFaceCheck['exists'],
        'isLocal' => $isLocallyAvailable,
        'source' => $huggingFaceCheck['source'] ?? '',
        'author' => $huggingFaceCheck['author'] ?? '',
        'name' => $huggingFaceCheck['name'] ?? '',
        'url' => $huggingFaceCheck['url'] ?? '',
        'type' => 'huggingface',
        'message' => $isLocallyAvailable 
            ? "Model is already installed locally" 
            : ($huggingFaceCheck['exists'] 
                ? "Model is available from Hugging Face. You can import it using the model path: $model"
                : "Model not found. Please check the format: username/modelname (e.g., Jovie/Midjourney)")
    ]);
}
