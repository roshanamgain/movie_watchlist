<?php
require_once 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = sanitize($_POST['fullname']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    $error = '';
    
    if (empty($fullname) || empty($email) || empty($password)) {
        $error = 'All fields are required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } elseif ($password != $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM tbluser WHERE Email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            $error = 'Email already exists';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $created_date = date('Y-m-d');
            
            $stmt = $pdo->prepare("INSERT INTO tbluser (FullName, Email, PasswordHash, CreatedDate, IsActive) VALUES (?, ?, ?, ?, 1)");
            
            if ($stmt->execute([$fullname, $email, $hashed_password, $created_date])) {
                $_SESSION['user_id'] = $pdo->lastInsertId();
                $_SESSION['fullname'] = $fullname;
                $_SESSION['email'] = $email;
                
                echo json_encode(['success' => true]);
                exit();
            } else {
                $error = 'Registration failed';
            }
        }
    }
    
    echo json_encode(['success' => false, 'error' => $error]);
    exit();
}
?>