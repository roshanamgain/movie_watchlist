<?php
require_once 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'error' => 'Email and password are required']);
        exit();
    }
    
    // Check for regular user (Role IS NULL or Role = '')
    $stmt = $pdo->prepare("SELECT * FROM tbluser WHERE Email = ? AND (Role IS NULL OR Role = '') AND IsActive = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo json_encode(['success' => false, 'error' => 'Invalid email or password']);
        exit();
    }
    
    // Verify password
    if (!password_verify($password, $user['PasswordHash'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid email or password']);
        exit();
    }
    
    // Set user session
    $_SESSION['user_id'] = $user['UserID'];
    $_SESSION['fullname'] = $user['FullName'];
    $_SESSION['email'] = $user['Email'];
    
    // Update last login date
    $update = $pdo->prepare("UPDATE tbluser SET LastLoginDate = CURDATE() WHERE UserID = ?");
    $update->execute([$user['UserID']]);
    
    echo json_encode(['success' => true]);
    exit();
}
?>