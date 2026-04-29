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

// Fetch user data from database
$query = "SELECT * FROM tblUser WHERE UserID = $user_id";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);
?>

<div class="container">
    <h1>My Profile</h1>
    
    <div class="profile-card">
        <div class="profile-stats">
            <div class="stat">
                <div class="stat-number"><?php echo $user['UserID']; ?></div>
                <div class="stat-label">User ID</div>
            </div>
            <div class="stat">
                <div class="stat-number"><?php echo $user['CreatedDate']; ?></div>
                <div class="stat-label">Member Since</div>
            </div>
            <div class="stat">
                <div class="stat-number"><?php echo $user['LastLoginDate'] ? $user['LastLoginDate'] : 'Never'; ?></div>
                <div class="stat-label">Last Login</div>
            </div>
        </div>
    </div>
    
    <table style="width: 100%; border-collapse: collapse; background: #1a2024; border-radius: 12px; overflow: hidden;">
        <tr>
            <td style="padding: 15px; background: #2c353d; width: 150px;"><strong>Full Name:</strong></td>
            <td style="padding: 15px;"><?php echo $user['FullName']; ?></td>
        </tr>
        <tr>
            <td style="padding: 15px; background: #2c353d;"><strong>Email:</strong></td>
            <td style="padding: 15px;"><?php echo $user['Email']; ?></td>
        </tr>
        <tr>
            <td style="padding: 15px; background: #2c353d;"><strong>Status:</strong></td>
            <td style="padding: 15px;"><?php echo $user['IsActive'] ? '<span style="color: #28a745;">✅ Active</span>' : '<span style="color: #dc3545;">❌ Inactive</span>'; ?></td>
        </tr>
    </table>
    
    <div class="menu" style="margin-top: 30px; display: flex; gap: 15px;">
        <a href="edit_profile.php" class="btn btn-primary">✏️ Edit Profile</a>
        <a href="change_password.php" class="btn btn-secondary">🔒 Change Password</a>
        <a href="delete_account.php" class="btn btn-danger">🗑️ Delete Account</a>
        <a href="index.php" class="btn btn-secondary">← Back to Home</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>