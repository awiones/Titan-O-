<?php
// Database configuration
define('DB_HOST', '127.0.0.1');  // Changed from 127.0.0.1
define('DB_PORT', '3307');      // Changed from 3307 to default MySQL port
define('DB_USER', 'root');
define('DB_PASSWORD', 'root');      // Default XAMPP has no password
define('DB_NAME', 'titano_db');    // Make sure this matches your database name

try {
    // Create PDO connection
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASSWORD,
        array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
        )
    );
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
