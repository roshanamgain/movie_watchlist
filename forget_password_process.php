<?php
require_once 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    
    if (empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Email address is required']);
        exit();
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit();
    }
    
    // Check if email exists
    $stmt = $pdo->prepare("SELECT * FROM tbluser WHERE Email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'No account found with this email address']);
        exit();
    }
    
    // Generate reset token
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    // Create password_resets table if not exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS password_resets (
        id INT PRIMARY KEY AUTO_INCREMENT,
        email VARCHAR(100) NOT NULL,
        token VARCHAR(255) NOT NULL,
        expires_at DATETIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Delete old tokens for this email
    $delete = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
    $delete->execute([$email]);
    
    // Insert new token
    $insert = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
    $insert->execute([$email, $token, $expires]);
    
    // Create reset link
    $resetLink = "http://localhost/movie_watchlist/reset_password.php?token=" . $token;
    
    echo json_encode([
        'success' => true, 
        'message' => 'Reset link generated!',
        'reset_link' => $resetLink
    ]);
    exit();
}
?>