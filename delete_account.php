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
    $password = $_POST['password'];
    
    // Get current user's hashed password
    $query = "SELECT PasswordHash FROM tblUser WHERE UserID = $user_id";
    $result = mysqli_query($conn, $query);
    $user = mysqli_fetch_assoc($result);
    
    // Validation
    if (empty($password)) {
        $error = 'Please enter your password to confirm deletion';
    } elseif (!password_verify($password, $user['PasswordHash'])) {
        $error = 'Password is incorrect';
    } else {
        // Delete user account (this will also delete watchlist entries due to CASCADE)
        $delete = "DELETE FROM tblUser WHERE UserID = $user_id";
        
        if (mysqli_query($conn, $delete)) {
            session_destroy();
            $success = 'Your account has been deleted. You will be redirected to home page.';
            header('refresh:3;url=index.php');
        } else {
            $error = 'Account deletion failed: ' . mysqli_error($conn);
        }
    }
}
?>

<div class="container">
    <h1>Delete Account</h1>
    
    <div class="error" style="background: #ffcccc; padding: 15px; margin-bottom: 20px;">
        <strong>Warning!</strong> This action cannot be undone. All your data will be permanently deleted.
    </div>
    
    <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php else: ?>
        <form method="POST">
            <label>Enter your password to confirm account deletion:</label>
            <input type="password" name="password" required>
            
            <button type="submit" style="background: #dc3545;">Permanently Delete My Account</button>
            <a href="profile.php" style="margin-left: 10px;">Cancel</a>
        </form>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>