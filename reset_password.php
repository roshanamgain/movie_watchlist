<?php
require_once 'includes/config.php';
include 'includes/header.php';

$token = isset($_GET['token']) ? $_GET['token'] : '';
$error = '';
$success = '';
$email = '';

// Verify token
if (!empty($token)) {
    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([$token]);
    $reset = $stmt->fetch();
    
    if (!$reset) {
        $error = 'Invalid or expired reset link. Please request a new one.';
    } else {
        $email = $reset['email'];
    }
}

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reset_password'])) {
    $token = $_POST['token'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Verify token again
    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([$token]);
    $reset = $stmt->fetch();
    
    if (!$reset) {
        $error = 'Invalid or expired reset link. Please request a new one.';
    } elseif (strlen($new_password) < 6) {
        $error = 'Password must be at least 6 characters';
    } elseif ($new_password != $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update = $pdo->prepare("UPDATE tbluser SET PasswordHash = ? WHERE Email = ?");
        if ($update->execute([$hashed_password, $reset['email']])) {
            // Delete used token
            $delete = $pdo->prepare("DELETE FROM password_resets WHERE token = ?");
            $delete->execute([$token]);
            $success = 'Password reset successfully! You can now login with your new password.';
        } else {
            $error = 'Password reset failed. Please try again.';
        }
    }
}
?>

<div class="reset-container" style="max-width: 450px; margin: 100px auto; padding: 0 20px;">
    <div style="background: #1c2228; padding: 40px; border-radius: 16px;">
        <h1 style="color: white; text-align: center; margin-bottom: 25px;">Reset Password</h1>
        
        <?php if ($success): ?>
            <div style="background: rgba(0,224,84,0.15); border: 1px solid #00e054; color: #00e054; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
                ✅ <?php echo htmlspecialchars($success); ?>
            </div>
            <div style="text-align: center; margin-top: 20px;">
                <a href="login.php" style="color: #c41e3a; text-decoration: none;">← Back to Login</a>
            </div>
        <?php elseif ($error): ?>
            <div style="background: rgba(196,30,58,0.15); border: 1px solid #c41e3a; color: #ff6666; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
                ❌ <?php echo htmlspecialchars($error); ?>
            </div>
            <div style="text-align: center;">
                <a href="index.php" style="color: #c41e3a; text-decoration: none;">← Back to Home</a>
            </div>
        <?php elseif (!empty($token) && !$error): ?>
            <p style="color: #99aabb; text-align: center; margin-bottom: 25px;">
                Reset password for: <strong style="color: white;"><?php echo htmlspecialchars($email); ?></strong>
            </p>
            <form method="POST">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; color: #99aabb;">New Password</label>
                    <input type="password" name="new_password" placeholder="Enter new password" style="width: 100%; padding: 12px; background: #2c3440; border: 1px solid #3a454d; border-radius: 8px; color: white;" required>
                </div>
                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 5px; color: #99aabb;">Confirm New Password</label>
                    <input type="password" name="confirm_password" placeholder="Confirm new password" style="width: 100%; padding: 12px; background: #2c3440; border: 1px solid #3a454d; border-radius: 8px; color: white;" required>
                </div>
                <button type="submit" name="reset_password" style="width: 100%; padding: 12px; background: #c41e3a; color: white; border: none; border-radius: 30px; cursor: pointer; font-weight: 600;">Reset Password</button>
            </form>
            <div style="text-align: center; margin-top: 20px;">
                <a href="login.php" style="color: #99aabb; text-decoration: none;">← Back to Login</a>
            </div>
        <?php else: ?>
            <p style="color: #99aabb; text-align: center;">No reset token provided. Please <a href="index.php" style="color: #c41e3a;">go back</a> and request a new reset link.</p>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>