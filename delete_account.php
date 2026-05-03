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
            $success = 'Your account has been deleted. You will be redirected to the home page.';
            header('refresh:3;url=index.php');
        } else {
            $error = 'Account deletion failed: ' . mysqli_error($conn);
        }
    }
}
?>

<div class="container">
    <h1>Delete Account</h1>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php else: ?>
        <div class="warning-box">
            <strong>⚠️ Warning!</strong> This action cannot be undone. All your data will be permanently deleted.
        </div>
        
        <form method="POST">
            <div class="form-group">
                <label>Enter your password to confirm account deletion:</label>
                <input type="password" name="password" required>
            </div>
            
            <div class="button-group">
                <button type="submit" class="btn-danger">Permanently Delete My Account</button>
                <a href="profile.php" class="btn-cancel">Cancel</a>
            </div>
        </form>
    <?php endif; ?>
</div>

<style>
.container {
    max-width: 500px;
    margin: 100px auto;
    padding: 0 20px;
}

h1 {
    color: #c41e3a;
    margin-bottom: 20px;
}

.warning-box {
    background: rgba(196, 30, 58, 0.15);
    border: 1px solid #c41e3a;
    color: #ff6666;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 25px;
}

.alert {
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.alert-error {
    background: rgba(196, 30, 58, 0.15);
    border: 1px solid #c41e3a;
    color: #ff6666;
}

.alert-success {
    background: rgba(0, 224, 84, 0.15);
    border: 1px solid #00e054;
    color: #00e054;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #99aabb;
}

.form-group input {
    width: 100%;
    padding: 12px;
    background: #2c3440;
    border: 1px solid #3a454d;
    border-radius: 8px;
    color: white;
}

.button-group {
    display: flex;
    gap: 15px;
    align-items: center;
}

.btn-danger {
    background: #c41e3a;
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: 30px;
    cursor: pointer;
    font-weight: 600;
}

.btn-danger:hover {
    background: #a01830;
}

.btn-cancel {
    color: #99aabb;
    text-decoration: none;
}

.btn-cancel:hover {
    color: #c41e3a;
}
</style>

<?php include 'includes/footer.php'; ?>