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
    
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 10px; background: #f4f4f4; width: 150px;"><strong>Full Name:</strong></td>
            <td style="padding: 10px;"><?php echo $user['FullName']; ?></td>
        </tr>
        <tr>
            <td style="padding: 10px; background: #f4f4f4;"><strong>Email:</strong></td>
            <td style="padding: 10px;"><?php echo $user['Email']; ?></td>
        </tr>
        <tr>
            <td style="padding: 10px; background: #f4f4f4;"><strong>Member Since:</strong></td>
            <td style="padding: 10px;"><?php echo $user['CreatedDate']; ?></td>
        </tr>
        <tr>
            <td style="padding: 10px; background: #f4f4f4;"><strong>Last Login:</strong></td>
            <td style="padding: 10px;"><?php echo $user['LastLoginDate'] ? $user['LastLoginDate'] : 'Never'; ?></td>
        </tr>
        <tr>
            <td style="padding: 10px; background: #f4f4f4;"><strong>Status:</strong></td>
            <td style="padding: 10px;"><?php echo $user['IsActive'] ? 'Active' : 'Inactive'; ?></td>
        </tr>
    </table>
    
    <div class="menu" style="margin-top: 20px;">
        <a href="edit_profile.php">Edit Profile</a>
        <a href="change_password.php">Change Password</a>
        <a href="delete_account.php">Delete Account</a>
        <a href="index.php">Back to Home</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>