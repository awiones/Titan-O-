<?php
session_start();
require_once 'config/config.php';

// CSRF Token Generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Initialize error message
$error = '';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF Token Validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token validation failed');
    }

    // Sanitize and validate input
    $login_input = filter_input(INPUT_POST, 'login_identifier', FILTER_SANITIZE_STRING);
    $password = $_POST['password'];

    // Determine if input is email or username
    $is_email = filter_var($login_input, FILTER_VALIDATE_EMAIL);

    try {
        // Prepare SQL to prevent SQL injection
        $stmt = $is_email 
            ? $pdo->prepare("SELECT id, username, email, password, last_login FROM users WHERE email = ?")
            : $pdo->prepare("SELECT id, username, email, password, last_login FROM users WHERE username = ?");
        $stmt->execute([$login_input]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify password
        if ($user && password_verify($password, $user['password'])) {
            // Check for multiple failed login attempts (optional advanced security)
            if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] > 5) {
                $error = "Too many failed attempts. Please try again later.";
            } else {
                // Regenerate session ID for security
                session_regenerate_id(true);
                
                // Store user info in session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                
                // Update last login time
                $updateStmt = $pdo->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
                $updateStmt->execute([$user['id']]);
                
                // Reset login attempts
                unset($_SESSION['login_attempts']);
                
                // Redirect to dashboard or chat page
                header("Location: titano.php");
                exit();
            }
        } else {
            // Increment login attempts
            $_SESSION['login_attempts'] = isset($_SESSION['login_attempts']) ? 
                $_SESSION['login_attempts'] + 1 : 1;
            
            $error = "Invalid login credentials";
        }
    } catch (PDOException $e) {
        $error = "Database error. Please try again.";
        // Log the actual error for admin review
        error_log($e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Titan 'O' - Titano.ai Login</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
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

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            line-height: 1.6;
        }

        .login-container {
            background-color: var(--background-color);
            border: 2px solid var(--text-color);
            border-radius: 10px;
            width: 100%;
            max-width: 450px;
            padding: 40px;
            text-align: center;
            box-shadow: 8px 8px 0 var(--text-color);
            position: relative;
        }

        .login-logo {
            margin-bottom: 30px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .login-logo img {
            max-width: 100px;
            margin-bottom: 15px;
            filter: grayscale(100%);
        }

        .login-header h2 {
            color: var(--text-color);
            margin-bottom: 10px;
            font-size: 2em;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .login-header p {
            color: var(--text-color);
            opacity: 0.7;
            margin-bottom: 20px;
        }

        .error-message {
            background-color: #f0f0f0;
            color: var(--text-color);
            border: 1px solid var(--text-color);
            padding: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-form input {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 2px solid var(--input-border);
            background-color: var(--input-background);
            color: var(--text-color);
            border-radius: 0;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .login-form input:focus {
            outline: none;
            box-shadow: 4px 4px 0 var(--text-color);
        }

        .login-form input[type="submit"] {
            background-color: var(--button-background);
            color: var(--button-text);
            border: 2px solid var(--text-color);
            cursor: pointer;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .login-form input[type="submit"]:hover {
            background-color: var(--hover-background);
        }

        .login-extras {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
        }

        .login-extras a {
            color: var(--text-color);
            text-decoration: none;
            font-size: 0.9em;
            position: relative;
        }

        .login-extras a::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 1px;
            bottom: -2px;
            left: 0;
            background-color: var(--text-color);
            visibility: hidden;
            transform: scaleX(0);
            transition: all 0.3s ease-in-out;
        }

        .login-extras a:hover::after {
            visibility: visible;
            transform: scaleX(1);
        }

        .or-divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 20px 0;
        }

        .or-divider::before,
        .or-divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid var(--text-color);
        }

        .or-divider span {
            padding: 0 10px;
            color: var(--text-color);
            opacity: 0.7;
        }

        .social-login {
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .social-btn {
            width: 50px;
            height: 50px;
            border: 2px solid var(--text-color);
            border-radius: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-color);
            transition: all 0.3s ease;
        }

        .social-btn:hover {
            background-color: var(--text-color);
            color: var(--background-color);
        }

        @media (max-width: 480px) {
            .login-container {
                width: 95%;
                padding: 20px;
                box-shadow: 4px 4px 0 var(--text-color);
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-logo">
            <h2>Titano.ai</h2>
        </div>

        <div class="login-header">
            <p>Welcome to Titan 'O' - Your Intelligent AI Companion</p>
        </div>

        <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="login-form" action="">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <input type="text" name="login_identifier" placeholder="Username or Email" required 
                   title="Enter your username or email">
            
            <input type="password" name="password" placeholder="Password" required 
                   minlength="8" 
                   title="Enter your password">
            
            <input type="submit" value="Sign In">

            <div class="login-extras">
                <a href="forgot-password.php">Forgot Password?</a>
                <a href="register.php">Create Account</a>
            </div>
        </form>

        <div class="or-divider">
            <span>OR</span>
        </div>

        <div class="social-login">
            <a href="#" class="social-btn" title="Login with Google">
                <i class="fab fa-google"></i>
            </a>
            <a href="#" class="social-btn" title="Login with GitHub">
                <i class="fab fa-github"></i>
            </a>
            <a href="#" class="social-btn" title="Login with Microsoft">
                <i class="fab fa-microsoft"></i>
            </a>
        </div>
    </div>
</body>
</html>