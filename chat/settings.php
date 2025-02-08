<?php
session_start();
require_once 'config/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

try {
    // Using the existing $pdo connection from config.php
    // Fetch user data
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $userData = $stmt->fetch();

    // Verify user exists
    if (!$userData) {
        header('Location: login.php');
        exit;
    }

    // Fetch user settings from database
    $stmtSettings = $pdo->prepare("
        SELECT theme, language, notifications, font_size, message_spacing 
        FROM users 
        WHERE id = ?
    ");
    $stmtSettings->execute([$_SESSION['user_id']]);
    $userSettings = $stmtSettings->fetch();

    // Set default settings if none found
    if (!$userSettings) {
        $userSettings = [
            'theme' => 'light',
            'language' => 'en',
            'notifications' => true,
            'font_size' => 'medium',
            'message_spacing' => 'comfortable'
        ];
    }

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $updates = [];
        $params = [];

        // Update profile information
        if (isset($_POST['username'])) {
            $updates[] = "username = ?";
            $params[] = $_POST['username'];
        }
        if (isset($_POST['email'])) {
            $updates[] = "email = ?";
            $params[] = $_POST['email'];
        }
        if (isset($_POST['first_name'])) {
            $updates[] = "first_name = ?";
            $params[] = $_POST['first_name'];
        }
        if (isset($_POST['last_name'])) {
            $updates[] = "last_name = ?";
            $params[] = $_POST['last_name'];
        }
        
        // Handle theme and appearance settings
        if (isset($_POST['theme'])) {
            $updates[] = "theme = ?";
            $params[] = $_POST['theme'];
        }
        if (isset($_POST['font_size'])) {
            $updates[] = "font_size = ?";
            $params[] = $_POST['font_size'];
        }

        // Add user ID to params array
        $params[] = $_SESSION['user_id'];

        // Construct and execute update query
        if (!empty($updates)) {
            $sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute($params)) {
                $success = "Settings updated successfully!";
                
                // Refresh user data
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $userData = $stmt->fetch();
            } else {
                $error = "Failed to update settings";
            }
        }

        // Handle password change
        if (!empty($_POST['new_password']) && !empty($_POST['current_password'])) {
            if (password_verify($_POST['current_password'], $userData['password'])) {
                $hashedPassword = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                if ($stmt->execute([$hashedPassword, $_SESSION['user_id']])) {
                    $success = "Password updated successfully!";
                } else {
                    $error = "Failed to update password";
                }
            } else {
                $error = "Current password is incorrect";
            }
        }
    }

} catch(PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Titano AI Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --background-color: #ffffff;
            --text-color: #000000;
            --input-border: #000000;
            --input-background: #ffffff;
            --button-background: #000000;
            --button-text: #ffffff;
            --hover-background: #333333;
        }

        body {
            background-color: var(--background-color);
            color: var(--text-color);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .settings-container {
            background-color: var(--background-color);
            border: 2px solid var(--text-color);
            border-radius: 10px;
            padding: 30px;
            width: 95%;  /* Changed from 100% */
            max-width: 1400px;  /* Reduced from 1800px */
            box-shadow: 8px 8px 0 var(--text-color);
            display: flex;
            gap: 30px;
            margin: 20px auto;  /* Added margin */
            overflow-x: hidden;  /* Added overflow control */
        }

        .settings-sidebar {
            width: 250px;
            border-right: 2px solid var(--text-color);
            padding-right: 20px;
        }

        .settings-content {
            flex: 1;
        }

        .settings-nav {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .settings-nav-item {
            margin-bottom: 10px;
        }

        .settings-nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 15px;
            border: 2px solid var(--text-color);
            border-radius: 5px;
            color: var(--text-color);
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 4px 4px 0 var(--text-color);
        }

        .settings-nav-link:hover {
            transform: translate(-2px, -2px);
            box-shadow: 6px 6px 0 var(--text-color);
        }

        .settings-nav-link.active {
            background-color: var(--button-background);
            color: var(--button-text);
        }

        .settings-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--text-color);
        }

        .settings-title {
            font-size: 24px;
            font-weight: bold;
            margin: 0;
        }

        .back-button {
            background-color: var(--button-background);
            color: var(--button-text);
            border: 2px solid var(--text-color);
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 4px 4px 0 var(--text-color);
        }

        .back-button:hover {
            transform: translate(-2px, -2px);
            box-shadow: 6px 6px 0 var(--text-color);
            color: var(--button-text);
        }

        .settings-section {
            margin-bottom: 30px;
            padding: 20px;
            border: 2px solid var(--text-color);
            border-radius: 8px;
            box-shadow: 4px 4px 0 var(--text-color);
        }

        .settings-section h3 {
            margin-bottom: 20px;
            font-size: 18px;
            font-weight: bold;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            font-weight: 600;
            margin-bottom: 8px;
        }

        .form-select, .form-control {
            border: 2px solid var(--text-color);
            border-radius: 5px;
            padding: 8px;
            width: 100%;
            background-color: var(--input-background);
            color: var(--text-color);
            transition: all 0.3s ease;
        }

        .form-select:focus, .form-control:focus {
            outline: none;
            box-shadow: 4px 4px 0 var(--text-color);
            transform: translate(-2px, -2px);
        }

        .save-button {
            background-color: var(--button-background);
            color: var(--button-text);
            border: 2px solid var(--text-color);
            padding: 10px 20px;
            border-radius: 5px;
            width: 100%;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 4px 4px 0 var(--text-color);
        }

        .save-button:hover {
            transform: translate(-2px, -2px);
            box-shadow: 6px 6px 0 var(--text-color);
        }

        .form-check-input {
            border: 2px solid var(--text-color);
        }

        .form-check-input:checked {
            background-color: var(--button-background);
            border-color: var(--text-color);
        }

        .custom-range {
            height: 6px;
            padding: 0;
            background: #ddd;
            border: 2px solid var(--text-color);
            border-radius: 3px;
            cursor: pointer;
        }
        
        .custom-range::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 20px;
            height: 20px;
            background: var(--button-background);
            border: 2px solid var(--text-color);
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .custom-range::-webkit-slider-thumb:hover {
            transform: scale(1.1);
        }
        
        .temperature-value {
            min-width: 40px;
            padding: 2px 8px;
            background: var(--button-background);
            color: var(--button-text);
            border: 2px solid var(--text-color);
            border-radius: 4px;
            text-align: center;
        }

        #ai_settings .card {
            border-color: var(--text-color);
            box-shadow: 4px 4px 0 var(--text-color);
        }

        .text-muted {
            color: #666 !important;
        }

        /* Update grid layout for models */
        .models-list .row {
            margin: 0 -10px;  /* Adjust negative margin */
            display: flex;
            flex-wrap: wrap;
        }

        .models-list .col-md-4 {
            padding: 10px;
            flex: 0 0 33.333%;
            max-width: 33.333%;
        }

        @media (max-width: 992px) {
            .models-list .col-md-4 {
                flex: 0 0 50%;
                max-width: 50%;
            }
        }

        @media (max-width: 768px) { 
            .models-list .col-md-4 {
                flex: 0 0 100%;
                max-width: 100%;
            }

            .settings-container {
                flex-direction: column;
            }

            .settings-sidebar {
                width: 100%;
                border-right: none;
                border-bottom: 2px solid var(--text-color);
                padding-bottom: 20px;
                margin-bottom: 20px;
            }
        }

        /* Update card styles */
        .card {
            margin-bottom: 15px;
            height: 100%;  /* Make cards equal height */
        }

        #modelDetailCard {
            max-width: 100%;
            overflow-x: auto;
        }

        #modelfile {
            white-space: pre-wrap;
            word-break: break-word;
            max-width: 100%;
            overflow-x: auto;
        }

        /* Improve form layout */
        .form-group {
            max-width: 100%;
        }

        .form-select, .form-control {
            max-width: 100%;
        }

        /* Update model selector container */
        .model-details {
            max-width: 100%;
        }

        .model-info {
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="settings-container">
        <!-- Settings Sidebar -->
        <div class="settings-sidebar">
            <ul class="settings-nav">
                <li class="settings-nav-item">
                    <a href="#profile" class="settings-nav-link active">
                        <i class="fas fa-user"></i>
                        <span>Profile</span>
                    </a>
                </li>
                <li class="settings-nav-item">
                    <a href="#security" class="settings-nav-link">
                        <i class="fas fa-shield-alt"></i>
                        <span>Security</span>
                    </a>
                </li>
                <li class="settings-nav-item">
                    <a href="#appearance" class="settings-nav-link">
                        <i class="fas fa-paint-brush"></i>
                        <span>Appearance</span>
                    </a>
                </li>
                <li class="settings-nav-item">
                    <a href="#notifications" class="settings-nav-link">
                        <i class="fas fa-bell"></i>
                        <span>Notifications</span>
                    </a>
                </li>
                <li class="settings-nav-item">
                    <a href="#others" class="settings-nav-link">
                        <i class="fas fa-ellipsis-h"></i>
                        <span>Others</span>
                    </a>
                </li>
                <!-- Change the AI settings nav item -->
                <li class="settings-nav-item">
                    <a href="#my_ai" class="settings-nav-link">
                        <i class="fas fa-microchip"></i>
                        <span>My AI</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Settings Content -->
        <div class="settings-content">
            <div class="settings-header">
                <h2 class="settings-title">Settings</h2>
                <a href="titano.php" class="back-button">
                    <i class="fas fa-arrow-left"></i> Back to Chat
                </a>
            </div>

            <div id="settings-sections">
                <!-- Profile Section -->
                <div id="profile" class="settings-section">
                    <h3><i class="fas fa-user"></i> Profile Settings</h3>
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" 
                                value="<?php echo htmlspecialchars($userData['username']); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" 
                                value="<?php echo htmlspecialchars($userData['email']); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">First Name</label>
                            <input type="text" name="first_name" class="form-control" 
                                value="<?php echo htmlspecialchars($userData['first_name'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-control" 
                                value="<?php echo htmlspecialchars($userData['last_name'] ?? ''); ?>">
                        </div>
                        <button type="submit" class="save-button">Save Changes</button>
                    </form>
                </div>

                <!-- Security Section -->
                <div id="security" class="settings-section" style="display: none;">
                    <h3><i class="fas fa-shield-alt"></i> Security Settings</h3>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label class="form-label">Change Password</label>
                            <input type="password" name="current_password" class="form-control" placeholder="Current Password">
                            <input type="password" name="new_password" class="form-control mt-2" placeholder="New Password" minlength="8">
                            <input type="password" name="confirm_password" class="form-control mt-2" placeholder="Confirm New Password" minlength="8">
                        </div>
                        <button type="submit" class="save-button">Update Password</button>
                    </form>
                </div>

                <!-- Appearance Section -->
                <div id="appearance" class="settings-section" style="display: none;">
                    <h3><i class="fas fa-paint-brush"></i> Appearance</h3>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label class="form-label">Theme</label>
                            <select class="form-select" name="theme">
                                <option value="light" <?php echo ($userData['theme'] ?? 'light') === 'light' ? 'selected' : ''; ?>>Light</option>
                                <option value="dark" <?php echo ($userData['theme'] ?? '') === 'dark' ? 'selected' : ''; ?>>Dark</option>
                                <option value="system" <?php echo ($userData['theme'] ?? '') === 'system' ? 'selected' : ''; ?>>System Default</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Font Size</label>
                            <select class="form-select" name="font_size">
                                <option value="small" <?php echo ($userData['font_size'] ?? 'medium') === 'small' ? 'selected' : ''; ?>>Small</option>
                                <option value="medium" <?php echo ($userData['font_size'] ?? 'medium') === 'medium' ? 'selected' : ''; ?>>Medium</option>
                                <option value="large" <?php echo ($userData['font_size'] ?? 'medium') === 'large' ? 'selected' : ''; ?>>Large</option>
                            </select>
                        </div>
                        <button type="submit" class="save-button">Save Appearance</button>
                    </form>
                </div>

                <!-- Notifications Section -->
                <div id="notifications" class="settings-section" style="display: none;">
                    <h3><i class="fas fa-bell"></i> Notifications</h3>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="notifications" id="notifications" <?php echo $userSettings['notifications'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="notifications">
                            Enable Desktop Notifications
                        </label>
                    </div>
                </div>

                <!-- Others Section -->
                <div id="others" class="settings-section" style="display: none;">
                    <h3><i class="fas fa-ellipsis-h"></i> Others</h3>
                    <div class="form-group">
                        <label class="form-label">Language</label>
                        <select class="form-select" name="language">
                            <option value="en" <?php echo $userSettings['language'] === 'en' ? 'selected' : ''; ?>>English</option>
                            <option value="es" <?php echo $userSettings['language'] === 'es' ? 'selected' : ''; ?>>Spanish</option>
                            <option value="fr" <?php echo $userSettings['language'] === 'fr' ? 'selected' : ''; ?>>French</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Message Spacing</label>
                        <select class="form-select" name="message_spacing">
                            <option value="comfortable" <?php echo $userSettings['message_spacing'] === 'comfortable' ? 'selected' : ''; ?>>Comfortable</option>
                            <option value="compact" <?php echo $userSettings['message_spacing'] === 'compact' ? 'selected' : ''; ?>>Compact</option>
                        </select>
                    </div>
                </div>

                <!-- Replace the my_ai section with this enhanced version -->
                <div id="my_ai" class="settings-section" style="display: none;">
                    <h3><i class="fas fa-microchip"></i> My AI Information</h3>
                    
                    <!-- System Status Card -->
                    <div class="ai-info mb-4">
                        <div class="card border-2">
                            <div class="card-body">
                                <h5 class="card-title mb-3">System Information</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Status:</strong> <span id="systemStatus" class="text-success">Connected</span></p>
                                        <p><strong>API Endpoint:</strong> localhost:11434</p>
                                        <p><strong>Version:</strong> <span id="ollamaVersion">Checking...</span></p>
                                        <p><strong>Last Check:</strong> <span id="lastCheck">Just now</span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Total Models:</strong> <span id="modelCount">Loading...</span></p>
                                        <p><strong>Total Size:</strong> <span id="totalSize">Calculating...</span></p>
                                        <p><strong>System Memory:</strong> <span id="systemMemory">Checking...</span></p>
                                        <p><strong>GPU Support:</strong> <span id="gpuStatus">Checking...</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Model Selection and Details -->
                    <div class="model-details mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="mb-0">Model Details</h4>
                            <button class="btn btn-sm btn-outline-secondary refresh-models">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Select Model to View Details</label>
                            <select class="form-select mb-3" id="modelDetailSelector">
                                <option value="">Loading models...</option>
                            </select>
                        </div>
                        
                        <div class="card border-2" id="modelDetailCard" style="display: none;">
                            <div class="card-body">
                                <h5 class="card-title mb-3">Model Information</h5>
                                <div class="model-info">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Name:</strong> <span id="modelName"></span></p>
                                            <p><strong>Base Model:</strong> <span id="baseModel"></span></p>
                                            <p><strong>Architecture:</strong> <span id="modelArch"></span></p>
                                            <p><strong>Size:</strong> <span id="modelSize"></span></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Modified:</strong> <span id="modelModified"></span></p>
                                            <p><strong>Parameters:</strong> <span id="modelParams"></span></p>
                                            <p><strong>Quantization:</strong> <span id="modelQuant"></span></p>
                                            <p><strong>Status:</strong> <span id="modelStatus"></span></p>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-4">
                                        <h6>Model Configuration</h6>
                                        <pre id="modelfile" class="bg-light p-3 rounded" style="max-height: 200px; overflow-y: auto;"></pre>
                                    </div>

                                    <div class="mt-4">
                                        <h6>Model Stats</h6>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="card bg-light">
                                                    <div class="card-body text-center">
                                                        <h3 id="modelLoadTime">-</h3>
                                                        <small>Load Time</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="card bg-light">
                                                    <div class="card-body text-center">
                                                        <h3 id="modelMemory">-</h3>
                                                        <small>Memory Usage</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="card bg-light">
                                                    <div class="card-body text-center">
                                                        <h3 id="modelSpeed">-</h3>
                                                        <small>Tokens/sec</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Available Models Grid -->
                    <div class="mb-4">
                        <h4 class="mb-3">Installed Models</h4>
                        <div class="models-list">
                            <!-- Models will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add this before the existing script tag -->
    <script>
        // Function to fetch available models
        async function fetchAvailableModels() {
            try {
                const response = await fetch('api/get_models.php');
                const data = await response.json();
                
                // Get the models list container
                const modelsList = document.querySelector('.models-list');
                const modelCount = document.getElementById('modelCount');
                const modelSelector = document.getElementById('modelDetailSelector');
                
                if (data.models && data.models.length > 0) {
                    // Update model count
                    modelCount.textContent = data.models.length;
                    
                    // Clear loading state and create grid
                    modelsList.innerHTML = '<div class="row">';
                    modelSelector.innerHTML = '<option value="">Select a model...</option>';
                    
                    // Add models to grid
                    data.models.forEach(model => {
                        modelsList.innerHTML += `
                            <div class="col-md-4 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">${model.name}</h5>
                                        <p class="card-text">
                                            <small class="text-muted">
                                                Size: ${(model.size / (1024 * 1024 * 1024)).toFixed(1)} GB<br>
                                                Modified: ${new Date(model.modified * 1000).toLocaleDateString()}
                                            </small>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        `;
                        
                        // Add to selector
                        modelSelector.innerHTML += `
                            <option value="${model.name}">${model.name}</option>
                        `;
                    });
                    
                    modelsList.innerHTML += '</div>';
                    
                    // Add event listener for model selection
                    modelSelector.addEventListener('change', async function() {
                        const selectedModel = this.value;
                        if (selectedModel) {
                            await fetchModelDetails(selectedModel);
                        } else {
                            document.getElementById('modelDetailCard').style.display = 'none';
                        }
                    });
                } else {
                    modelsList.innerHTML = '<div class="alert alert-warning">No models found. Please install models through Ollama.</div>';
                    modelCount.textContent = '0';
                    modelSelector.innerHTML = '<option value="">No models available</option>';
                }
            } catch (error) {
                const modelsList = document.querySelector('.models-list');
                modelsList.innerHTML = '<div class="alert alert-danger">Error loading models. Please check if Ollama is running.</div>';
                document.getElementById('modelCount').textContent = 'Error';
                console.error('Error fetching models:', error);
            }
        }

        // Add new function to fetch model details
        async function fetchModelDetails(modelName) {
            try {
                const response = await fetch(`api/get_model_info.php?model=${modelName}`);
                const data = await response.json();
                
                if (data.error) {
                    throw new Error(data.error);
                }

                // Update basic info
                document.getElementById('modelName').textContent = data.name;
                document.getElementById('modelSize').textContent = `${(data.size / (1024 * 1024 * 1024)).toFixed(1)} GB`;
                document.getElementById('modelModified').textContent = new Date(data.modified * 1000).toLocaleDateString();
                
                // Parse and display model details
                const modelConfig = data.modelfile || '';
                document.getElementById('modelfile').textContent = modelConfig;
                
                // Extract model information from modelfile
                const baseModel = modelConfig.match(/FROM\s+([^\n]+)/)?.[1] || 'N/A';
                const architecture = modelConfig.includes('transformer') ? 'Transformer' : 
                                   modelConfig.includes('llama') ? 'LLaMA' : 'Unknown';
                const quantization = modelConfig.match(/QUANTIZE\s+([^\n]+)/)?.[1] || 'None';
                
                // Update additional fields
                document.getElementById('baseModel').textContent = baseModel;
                document.getElementById('modelArch').textContent = architecture;
                document.getElementById('modelQuant').textContent = quantization;
                document.getElementById('modelParams').textContent = data.parameters || 'Not available';
                document.getElementById('modelStatus').textContent = 'Ready';
                
                // Show the detail card
                document.getElementById('modelDetailCard').style.display = 'block';
                
                // Fetch and update model stats
                await updateModelStats(modelName);
            } catch (error) {
                console.error('Error fetching model details:', error);
                alert('Error loading model details. Please try again.');
            }
        }

        // Add new function to fetch model stats
        async function updateModelStats(modelName) {
            try {
                // Simulate fetching stats (replace with actual API call)
                const stats = {
                    loadTime: Math.random() * 2 + 0.5,
                    memory: Math.random() * 8 + 2,
                    speed: Math.floor(Math.random() * 50 + 20)
                };
                
                document.getElementById('modelLoadTime').textContent = `${stats.loadTime.toFixed(1)}s`;
                document.getElementById('modelMemory').textContent = `${stats.memory.toFixed(1)}GB`;
                document.getElementById('modelSpeed').textContent = `${stats.speed}`;
            } catch (error) {
                console.error('Error fetching model stats:', error);
            }
        }

        // Add new function to fetch system information
        async function fetchSystemInfo() {
            try {
                const response = await fetch('api/get_system_info.php');
                const data = await response.json();
                
                // Update system information
                document.getElementById('ollamaVersion').textContent = data.version;
                document.getElementById('totalSize').textContent = `${data.total_size} GB`;
                document.getElementById('systemMemory').textContent = 
                    `${data.system_memory.total} GB Total / ${data.system_memory.free} GB Free`;
                
                // Update GPU status with detailed information
                const gpuStatus = document.getElementById('gpuStatus');
                if (data.gpu.available) {
                    gpuStatus.textContent = data.gpu.info;
                    gpuStatus.className = 'text-success';
                } else {
                    gpuStatus.textContent = 'Not Available';
                    gpuStatus.className = 'text-warning';
                }
                
                // Update last check time
                document.getElementById('lastCheck').textContent = data.timestamp;
            } catch (error) {
                console.error('Error fetching system info:', error);
                document.getElementById('systemStatus').textContent = 'Error';
                document.getElementById('systemStatus').className = 'text-danger';
            }
        }

        // Update the refresh button handler
        document.querySelector('.refresh-models')?.addEventListener('click', function() {
            this.querySelector('i').classList.add('fa-spin');
            Promise.all([
                fetchAvailableModels(),
                fetchSystemInfo()
            ]).finally(() => {
                this.querySelector('i').classList.remove('fa-spin');
            });
        });

        // Update the AI section click handler
        document.querySelector('a[href="#my_ai"]').addEventListener('click', function() {
            fetchAvailableModels();
            fetchSystemInfo();
        });

        // Add this to your existing DOMContentLoaded event
        document.addEventListener('DOMContentLoaded', function() {
            // Get all nav links and sections
            const navLinks = document.querySelectorAll('.settings-nav-link');
            const sections = document.querySelectorAll('.settings-section');

            // Function to show selected section and hide others
            function showSection(sectionId) {
                sections.forEach(section => {
                    if (section.id === sectionId) {
                        section.style.display = 'block';
                    } else {
                        section.style.display = 'none';
                    }
                });

                // Update active class on nav links
                navLinks.forEach(link => {
                    if (link.getAttribute('href') === '#' + sectionId) {
                        link.classList.add('active');
                    } else {
                        link.classList.remove('active');
                    }
                });
            }

            // Add click event listeners to all nav links
            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const sectionId = this.getAttribute('href').substring(1);
                    showSection(sectionId);

                    // Update URL hash without scrolling
                    history.pushState(null, null, '#' + sectionId);
                });
            });

            // Handle initial load and browser back/forward
            function handleHashChange() {
                const hash = window.location.hash.substring(1) || 'profile';
                showSection(hash);
            }

            // Listen for hash changes
            window.addEventListener('hashchange', handleHashChange);

            // Handle initial page load
            handleHashChange();

            // Fetch available models when AI settings section is shown
            const aiNavLink = document.querySelector('a[href="#my_ai"]');
            aiNavLink.addEventListener('click', function() {
                fetchAvailableModels();
                fetchSystemInfo();
            });

            // Temperature slider handling
            const temperatureSlider = document.getElementById('temperatureSlider');
            const temperatureValue = document.getElementById('temperatureValue');
            
            temperatureSlider.addEventListener('input', function() {
                const value = (this.value / 100).toFixed(2);
                temperatureValue.textContent = value;
            });

            // Top P slider visual feedback
            const topPSlider = document.getElementById('topPSlider');
            topPSlider.addEventListener('input', function() {
                const value = (this.value / 100).toFixed(2);
                this.title = `Top P: ${value}`;
            });

            // Penalty sliders visual feedback
            const presencePenaltySlider = document.getElementById('presencePenaltySlider');
            const frequencyPenaltySlider = document.getElementById('frequencyPenaltySlider');

            presencePenaltySlider.addEventListener('input', function() {
                const value = (this.value / 10).toFixed(1);
                this.title = `Presence Penalty: ${value}`;
            });

            frequencyPenaltySlider.addEventListener('input', function() {
                const value = (this.value / 10).toFixed(1);
                this.title = `Frequency Penalty: ${value}`;
            });
        });
    </script>
</body>
</html>