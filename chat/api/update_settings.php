<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['setting']) || !isset($data['value'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing parameters']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE users SET {$data['setting']} = ? WHERE id = ?");
    $stmt->execute([$data['value'], $_SESSION['user_id']]);
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
