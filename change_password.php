<?php
session_start();
include 'includes/config.php';
include 'includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Get current user's hashed password
    $query = "SELECT PasswordHash FROM tblUser WHERE UserID = $user_id";
    $result = mysqli_query($conn, $query);
    $user = mysqli_fetch_assoc($result);
    
    // Validation
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'All fields are required';
    } elseif (!password_verify($current_password, $user['PasswordHash'])) {
        $error = 'Current password is incorrect';
    } elseif ($new_password != $confirm_password) {
        $error = 'New passwords do not match';
    } elseif (strlen($new_password) < 6) {
        $error = 'New password must be at least 6 characters';
    } else {
        // Update password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update = "UPDATE tblUser SET PasswordHash='$hashed_password' WHERE UserID=$user_id";
        
        if (mysqli_query($conn, $update)) {
            $success = 'Password changed successfully!';
        } else {
            $error = 'Password update failed';
        }
    }
}
?>

<div class="container">
    <h1>Change Password</h1>
    
    <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <label>Current Password:</label>
        <input type="password" name="current_password" required>
        
        <label>New Password:</label>
        <input type="password" name="new_password" required>
        
        <label>Confirm New Password:</label>
        <input type="password" name="confirm_password" required>
        
        <button type="submit">Change Password</button>
        <a href="profile.php" style="margin-left: 10px;">Cancel</a>
    </form>
</div>

<?php include 'includes/footer.php'; ?>