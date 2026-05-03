<?php
require_once 'includes/config.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = $_POST['password'];
    $userId = $_SESSION['user_id'];
    
    // Get user
    $stmt = $pdo->prepare("SELECT * FROM tbluser WHERE UserID = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo json_encode(['success' => false, 'error' => 'User not found']);
        exit();
    }
    
    // Verify password
    if (!password_verify($password, $user['PasswordHash'])) {
        echo json_encode(['success' => false, 'error' => 'Incorrect password']);
        exit();
    }
    
    // Delete user (watchlist, reviews will be deleted automatically due to foreign key constraints)
    $delete = $pdo->prepare("DELETE FROM tbluser WHERE UserID = ?");
    if ($delete->execute([$userId])) {
        session_destroy();
        echo json_encode(['success' => true]);
        exit();
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to delete account']);
        exit();
    }
}
?>