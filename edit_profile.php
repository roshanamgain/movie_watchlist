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
$success = '';
$error = '';

// Get current user data
$query = "SELECT * FROM tblUser WHERE UserID = $user_id";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    // Validation
    if (empty($fullname) || empty($email)) {
        $error = 'All fields are required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } else {
        // Check if email already exists for another user
        $check = mysqli_query($conn, "SELECT * FROM tblUser WHERE Email='$email' AND UserID != $user_id");
        if (mysqli_num_rows($check) > 0) {
            $error = 'Email already used by another account';
        } else {
            // Update user data
            $update = "UPDATE tblUser SET FullName='$fullname', Email='$email' WHERE UserID=$user_id";
            if (mysqli_query($conn, $update)) {
                $_SESSION['fullname'] = $fullname;
                $_SESSION['email'] = $email;
                $success = 'Profile updated successfully!';
                // Refresh user data
                $result = mysqli_query($conn, "SELECT * FROM tblUser WHERE UserID = $user_id");
                $user = mysqli_fetch_assoc($result);
            } else {
                $error = 'Update failed: ' . mysqli_error($conn);
            }
        }
    }
}
?>

<div class="container">
    <h1>Edit Profile</h1>
    
    <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <label>Full Name:</label>
        <input type="text" name="fullname" value="<?php echo $user['FullName']; ?>" required>
        
        <label>Email:</label>
        <input type="email" name="email" value="<?php echo $user['Email']; ?>" required>
        
        <button type="submit">Save Changes</button>
        <a href="profile.php" style="margin-left: 10px;">Cancel</a>
    </form>
</div>

<?php include 'includes/footer.php'; ?>