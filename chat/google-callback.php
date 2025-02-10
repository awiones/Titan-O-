<?php
session_start();
require_once 'config/config.php';
require_once 'config/google_config.php';
require_once 'vendor/autoload.php';

$google_client = new Google_Client();
$google_client->setClientId(GOOGLE_CLIENT_ID);
$google_client->setClientSecret(GOOGLE_CLIENT_SECRET);
$google_client->setRedirectUri(GOOGLE_REDIRECT_URI);
$google_client->addScope("email");
$google_client->addScope("profile");

try {
    if (!isset($_GET['code'])) {
        throw new Exception('No authorization code received');
    }

    $token = $google_client->fetchAccessTokenWithAuthCode($_GET['code']);
    
    if (isset($token['error'])) {
        throw new Exception('Token error: ' . $token['error']);
    }

    $google_client->setAccessToken($token['access_token']);
    
    // Get user profile data
    $google_service = new Google_Service_Oauth2($google_client);
    $google_account_info = $google_service->getUserInfo();
    
    if (!$google_account_info) {
        throw new Exception('Failed to get user info');
    }

    $email = $google_account_info->getEmail();
    $name = $google_account_info->getName();
    
    // Check if user exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user) {
        // User exists - log them in
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        
        // Update last login
        $updateStmt = $pdo->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
        $updateStmt->execute([$user['id']]);
    } else {
        // Create new user
        $username = strtolower(str_replace(' ', '_', $name)) . rand(100, 999);
        $random_password = bin2hex(random_bytes(16));
        
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, created_at) VALUES (?, ?, ?, CURRENT_TIMESTAMP)");
        $stmt->execute([$username, $email, password_hash($random_password, PASSWORD_DEFAULT)]);
        
        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
    }
    
    // Redirect to the intended destination
    $redirect_to = isset($_SESSION['oauth_redirect']) ? $_SESSION['oauth_redirect'] : 'titano.php';
    unset($_SESSION['oauth_redirect']); // Clear the stored redirect
    
    header("Location: $redirect_to");
    exit();

} catch (Exception $e) {
    error_log('Google OAuth error: ' . $e->getMessage());
    $_SESSION['error'] = "Authentication failed: " . $e->getMessage();
    header('Location: login.php');
    exit();
}
