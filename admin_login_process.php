<?php
require_once 'includes/config.php';

// Only start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit();
}

$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'error' => 'Email and password required']);
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM tbluser WHERE Email = ? AND Role IS NOT NULL AND Role != '' AND IsActive = 1");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
    echo json_encode(['success' => false, 'error' => 'Invalid admin credentials']);
    exit();
}

if (!password_verify($password, $user['PasswordHash'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid admin credentials']);
    exit();
}

$_SESSION['admin_id'] = $user['UserID'];
$_SESSION['admin_name'] = $user['FullName'];
$_SESSION['admin_email'] = $user['Email'];
$_SESSION['admin_role'] = $user['Role'];
$_SESSION['is_admin'] = true;

echo json_encode(['success' => true]);
exit();