<?php
session_start();
require_once '../includes/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action == 'check_email') {
    $email = isset($_GET['email']) ? trim($_GET['email']) : '';
    $userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
    
    $stmt = $pdo->prepare("SELECT * FROM tbluser WHERE Email = ? AND UserID != ?");
    $stmt->execute([$email, $userId]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['valid' => false, 'message' => 'Email already exists']);
    } else {
        echo json_encode(['valid' => true]);
    }
    exit();
}

if ($action == 'check_password') {
    $userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
    $password = isset($_GET['password']) ? $_GET['password'] : '';
    
    $stmt = $pdo->prepare("SELECT PasswordHash FROM tbluser WHERE UserID = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['PasswordHash'])) {
        echo json_encode(['valid' => true]);
    } else {
        echo json_encode(['valid' => false, 'message' => 'Current password is incorrect']);
    }
    exit();
}

echo json_encode(['success' => false, 'error' => 'Invalid action']);
?>