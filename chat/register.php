<?php
session_start();
require_once 'config/config.php';

// CSRF Token Generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Initialize error messages
$errors = [];

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF Token Validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token validation failed');
    }

    // Sanitize and validate inputs
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation checks
    if (empty($username)) {
        $errors[] = "Username is required";
    } elseif (strlen($username) < 3 || strlen($username) > 50) {
        $errors[] = "Username must be between 3 and 50 characters";
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required";
    }

    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    } elseif ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }

    // Check if username or email already exists
    if (empty($errors)) {
        try {
            // Check username existence
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetchColumn() > 0) {
                $errors[] = "Username already exists";
            }

            // Check email existence
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                $errors[] = "Email already registered";
            }

            // If no errors, proceed with registration
            if (empty($errors)) {
                // Hash password (using a simpler but still secure method)
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Prepare insert statement
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                $stmt->execute([$username, $email, $hashed_password]);

                // Redirect to login or dashboard
                header("Location: login.php?registration=success");
                exit();
            }
        } catch (PDOException $e) {
            $errors[] = "Registration failed. Please try again.";
            error_log($e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Titan 'O' - Create Account</title>
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
            --error-color: #ff0000;
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
            padding: 20px;
        }

        .register-container {
            background-color: var(--background-color);
            border: 2px solid var(--text-color);
            border-radius: 10px;
            width: 100%;
            max-width: 450px;
            padding: 40px;
            text-align: center;
            box-shadow: 8px 8px 0 var(--text-color);
        }

        .register-logo {
            margin-bottom: 30px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .register-logo img {
            max-width: 100px;
            margin-bottom: 15px;
            filter: grayscale(100%);
        }

        .register-header h2 {
            color: var(--text-color);
            margin-bottom: 10px;
            font-size: 2em;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .error-list {
            background-color: #f0f0f0;
            border: 1px solid var(--error-color);
            color: var(--error-color);
            padding: 15px;
            margin-bottom: 20px;
            text-align: left;
        }

        .error-list ul {
            margin-left: 20px;
        }

        .register-form input {
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

        .register-form input:focus {
            outline: none;
            box-shadow: 4px 4px 0 var(--text-color);
        }

        .register-form input[type="submit"] {
            background-color: var(--button-background);
            color: var(--button-text);
            border: 2px solid var(--text-color);
            cursor: pointer;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .register-form input[type="submit"]:hover {
            background-color: var(--hover-background);
        }

        .login-link {
            margin-top: 20px;
            font-size: 0.9em;
        }

        .login-link a {
            color: var(--text-color);
            text-decoration: none;
            position: relative;
        }

        .login-link a::after {
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

        .login-link a:hover::after {
            visibility: visible;
            transform: scaleX(1);
        }

        @media (max-width: 480px) {
            .register-container {
                width: 95%;
                padding: 20px;
                box-shadow: 4px 4px 0 var(--text-color);
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-logo">
            <h2>Titano.ai</h2>
        </div>

        <div class="register-header">
            <p>Create Your Titan 'O' Account</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="error-list">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" class="register-form" action="">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <input type="text" name="username" placeholder="Username" required 
                   minlength="3" maxlength="50"
                   pattern="[A-Za-z0-9_]+"
                   title="Username can contain letters, numbers, and underscores"
                   value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>">
            
            <input type="email" name="email" placeholder="Email" required
                   title="Enter a valid email address"
                   value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
            
            <input type="password" name="password" placeholder="Password" required 
                   minlength="8"
                   title="Password must be at least 8 characters long">
            
            <input type="password" name="confirm_password" placeholder="Confirm Password" required 
                   minlength="8"
                   title="Repeat the password">
            
            <input type="submit" value="Create Account">
        </form>

        <div class="login-link">
            Already have an account? <a href="login.php">Sign In</a>
        </div>
    </div>

    <script>
        // Client-side password validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.querySelector('input[name="password"]');
            const confirmPassword = document.querySelector('input[name="confirm_password"]');

            if (password.value.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters long');
                return;
            }

            if (password.value !== confirmPassword.value) {
                e.preventDefault();
                alert('Passwords do not match');
            }
        });
    </script>
</body>
</html>