<?php
session_start();
include 'includes/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    
    $query = "SELECT * FROM tblUser WHERE Email='$email' AND IsActive=1";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['PasswordHash'])) {
            $_SESSION['user_id'] = $user['UserID'];
            $_SESSION['fullname'] = $user['FullName'];
            $_SESSION['email'] = $user['Email'];
            
            // Update last login date
            $update = "UPDATE tblUser SET LastLoginDate='" . date('Y-m-d') . "' WHERE UserID=" . $user['UserID'];
            mysqli_query($conn, $update);
            
            header('Location: index.php');
            exit();
        } else {
            $error = 'Invalid password';
        }
    } else {
        $error = 'Email not found or account inactive';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Movie Watchlist</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</body>
</html>