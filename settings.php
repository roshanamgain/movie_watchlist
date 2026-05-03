<?php
require_once 'includes/config.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$success = '';
$error = '';

// Get current user data
$stmt = $pdo->prepare("SELECT * FROM tbluser WHERE UserID = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        $fullname = trim($_POST['fullname']);
        $email = trim($_POST['email']);
        
        if (empty($fullname) || empty($email)) {
            $error = 'All fields are required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email format';
        } else {
            $update = $pdo->prepare("UPDATE tbluser SET FullName = ?, Email = ? WHERE UserID = ?");
            if ($update->execute([$fullname, $email, $userId])) {
                $_SESSION['fullname'] = $fullname;
                $_SESSION['email'] = $email;
                $success = 'Profile updated successfully!';
                // Refresh user data
                $stmt = $pdo->prepare("SELECT * FROM tbluser WHERE UserID = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch();
            } else {
                $error = 'Update failed';
            }
        }
    }
    
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error = 'All password fields are required';
        } elseif ($new_password != $confirm_password) {
            $error = 'New passwords do not match';
        } elseif (strlen($new_password) < 6) {
            $error = 'Password must be at least 6 characters';
        } elseif (!password_verify($current_password, $user['PasswordHash'])) {
            $error = 'Current password is incorrect';
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE tbluser SET PasswordHash = ? WHERE UserID = ?");
            if ($update->execute([$hashed_password, $userId])) {
                $success = 'Password changed successfully!';
            } else {
                $error = 'Password change failed';
            }
        }
    }
    
    // Handle forgot password
    if (isset($_POST['forgot_password'])) {
        $email = $user['Email'];
        
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $pdo->exec("CREATE TABLE IF NOT EXISTS password_resets (
            id INT PRIMARY KEY AUTO_INCREMENT,
            email VARCHAR(100) NOT NULL,
            token VARCHAR(255) NOT NULL,
            expires_at DATETIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        $delete = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
        $delete->execute([$email]);
        
        $insert = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $insert->execute([$email, $token, $expires]);
        
        $resetLink = "http://localhost/movie_watchlist/reset_password.php?token=" . $token;
        
        $success = 'Password reset link generated. <a href="' . $resetLink . '" target="_blank" style="color: #00e054;">Click here to reset your password</a>';
    }
}
?>

<div class="settings-container">
    <div class="settings-header">
        <h1>Settings</h1>
        <p>Manage your account settings and preferences</p>
    </div>
    
    <?php if ($success): ?>
        <div class="alert-success">✅ <?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert-error">❌ <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <!-- Profile Section -->
    <div class="settings-card">
        <div class="card-header">
            <span class="card-icon">👤</span>
            <h2>Profile Information</h2>
        </div>
        <form method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="fullname" value="<?php echo htmlspecialchars($user['FullName']); ?>" required>
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['Email']); ?>" required>
            </div>
            <button type="submit" name="update_profile" class="btn-save">Save Changes</button>
        </form>
    </div>
    
    <!-- Security Section -->
    <div class="settings-card" id="change-password">
        <div class="card-header">
            <span class="card-icon">🔒</span>
            <h2>Security</h2>
        </div>
        
        <!-- Change Password Sub-section -->
        <div class="sub-section">
            <h3>Change Password</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" required>
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" required>
                    </div>
                </div>
                <button type="submit" name="change_password" class="btn-secondary">Update Password</button>
            </form>
        </div>
        
        <!-- Forgot Password Sub-section -->
        <div class="sub-section">
            <h3>Forgot Password?</h3>
            <p class="section-desc">Get a password reset link sent to your email address.</p>
            <form method="POST">
                <button type="submit" name="forgot_password" class="btn-outline">Send Reset Link</button>
            </form>
        </div>
    </div>
    
  <!-- Danger Zone -->
<div class="settings-card danger-zone">
    <div class="card-header">
          <span class="card-icon">⚠️</span>
          <h2>Delete Account</h2>
    </div>
    <div class="danger-content">
        <p>Once you delete your account, there is no going back. All your data will be permanently removed.</p>
        <button onclick="openDeleteModal()" class="btn-danger">Delete My Account</button>
    </div>
</div>
    
    <div class="settings-footer">
        <a href="profile.php" class="back-link">← Back to Profile</a>
    </div>
</div>

<!-- Delete Account Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content delete-modal">
        <span class="close-modal" id="closeDelete">&times;</span>
        <div class="modal-header">
            <span class="modal-icon">⚠️</span>
            <h2>Delete Account</h2>
        </div>
        <p class="warning-text">This action cannot be undone!</p>
        <p class="confirm-text">Are you sure you want to permanently delete your account? All your watchlist, reviews, and personal data will be lost forever.</p>
        
        <div id="deleteError" class="modal-error" style="display: none;">
            <span class="error-text" id="deleteErrorText"></span>
            <button class="error-close" onclick="document.getElementById('deleteError').style.display='none';">&times;</button>
        </div>
        
        <form id="deleteAccountForm">
            <div class="form-group">
                <label>Enter your password to confirm:</label>
                <input type="password" id="confirmPassword" placeholder="Your password" required>
            </div>
            <div class="modal-buttons">
                <button type="submit" class="btn-confirm-delete">Yes, Delete My Account</button>
                <button type="button" class="btn-cancel" id="cancelDelete">Cancel</button>
            </div>
        </form>
    </div>
</div>

<style>
.settings-container {
    max-width: 700px;
    margin: 100px auto 60px;
    padding: 0 24px;
}

.settings-header {
    text-align: center;
    margin-bottom: 32px;
}

.settings-header h1 {
    font-size: 2rem;
    margin-bottom: 8px;
}

.settings-header p {
    color: #99aabb;
}

.alert-success {
    background: rgba(0, 224, 84, 0.12);
    border: 1px solid #00e054;
    color: #00e054;
    padding: 14px 18px;
    border-radius: 12px;
    margin-bottom: 24px;
}

.alert-error {
    background: rgba(196, 30, 58, 0.12);
    border: 1px solid #c41e3a;
    color: #ff6666;
    padding: 14px 18px;
    border-radius: 12px;
    margin-bottom: 24px;
}

.settings-card {
    background: #1c2228;
    border-radius: 20px;
    margin-bottom: 24px;
    border: 1px solid #2c3440;
    overflow: hidden;
}

.card-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 24px;
    border-bottom: 1px solid #2c3440;
    background: #14181c;
}

.card-icon {
    font-size: 1.3rem;
}

.card-header h2 {
    font-size: 1.2rem;
    font-weight: 600;
    margin: 0;
}

.settings-card form {
    padding: 24px;
}

.form-group {
    margin-bottom: 18px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #99aabb;
    font-weight: 500;
    font-size: 0.85rem;
}

.form-group input {
    width: 100%;
    padding: 12px 16px;
    background: #2c3440;
    border: 1px solid #3a454d;
    border-radius: 10px;
    color: white;
    font-size: 0.95rem;
    transition: all 0.2s;
}

.form-group input:focus {
    outline: none;
    border-color: #c41e3a;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.btn-save, .btn-secondary, .btn-outline {
    padding: 10px 24px;
    border-radius: 30px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
    font-size: 0.85rem;
}

.btn-save {
    background: #c41e3a;
    color: white;
}

.btn-save:hover {
    background: #a01830;
}

.btn-secondary {
    background: #2c3440;
    color: white;
}

.btn-secondary:hover {
    background: #3a454d;
}

.btn-outline {
    background: transparent;
    border: 1px solid #c41e3a;
    color: #c41e3a;
}

.btn-outline:hover {
    background: rgba(196, 30, 58, 0.1);
}

.sub-section {
    padding: 20px 24px;
    border-bottom: 1px solid #2c3440;
}

.sub-section:last-child {
    border-bottom: none;
}

.sub-section h3 {
    font-size: 1rem;
    margin-bottom: 8px;
    color: #ffffff;
}

.section-desc {
    color: #99aabb;
    font-size: 0.8rem;
    margin-bottom: 16px;
}

.danger-zone {
    border: 1px solid rgba(196, 30, 58, 0.5);
}

.danger-content {
    padding: 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
}

.danger-info h3 {
    font-size: 1rem;
    color: #c41e3a;
    margin-bottom: 4px;
}

.danger-info p {
    color: #99aabb;
    font-size: 0.8rem;
}

.btn-danger {
    background: transparent;
    border: 1px solid #c41e3a;
    color: #c41e3a;
    padding: 10px 24px;
    border-radius: 30px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-danger:hover {
    background: #c41e3a;
    color: white;
}

.settings-footer {
    text-align: center;
    margin-top: 16px;
}

.back-link {
    color: #99aabb;
    text-decoration: none;
    font-size: 0.85rem;
}

.back-link:hover {
    color: #c41e3a;
}

/* Modal Styles for Delete */
.modal-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
}

.modal-icon {
    font-size: 1.8rem;
}

.warning-text {
    color: #c41e3a;
    text-align: center;
    margin-bottom: 8px;
}

.confirm-text {
    color: #99aabb;
    text-align: center;
    margin-bottom: 20px;
    font-size: 0.85rem;
}

.modal-buttons {
    display: flex;
    gap: 12px;
    justify-content: center;
    margin-top: 20px;
}

.btn-confirm-delete {
    background: #c41e3a;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 30px;
    cursor: pointer;
    font-weight: 600;
}

.btn-cancel {
    background: #2c3440;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 30px;
    cursor: pointer;
    font-weight: 600;
}

.delete-modal {
    max-width: 450px;
}

#change-password {
    scroll-margin-top: 100px;
}

@media (max-width: 768px) {
    .settings-container {
        margin-top: 80px;
        padding: 0 16px;
    }
    
    .form-row {
        grid-template-columns: 1fr;
        gap: 0;
    }
    
    .danger-content {
        flex-direction: column;
        text-align: center;
    }
    
    .modal-buttons {
        flex-direction: column;
    }
}
</style>

<script>
    function openDeleteModal() {
        var deleteModal = document.getElementById('deleteModal');
        deleteModal.style.display = 'flex';
    }
    
    function closeDeleteModal() {
        var deleteModal = document.getElementById('deleteModal');
        deleteModal.style.display = 'none';
    }
    
    var closeDelete = document.getElementById('closeDelete');
    if (closeDelete) {
        closeDelete.onclick = function() {
            closeDeleteModal();
        }
    }
    
    var cancelDelete = document.getElementById('cancelDelete');
    if (cancelDelete) {
        cancelDelete.onclick = function() {
            closeDeleteModal();
        }
    }
    
    var deleteAccountForm = document.getElementById('deleteAccountForm');
    if (deleteAccountForm) {
        deleteAccountForm.onsubmit = function(e) {
            e.preventDefault();
            var password = document.getElementById('confirmPassword').value;
            
            fetch('/movie_watchlist/delete_account_process.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'password=' + encodeURIComponent(password)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '/movie_watchlist/index.php';
                } else {
                    document.getElementById('deleteErrorText').textContent = data.error;
                    document.getElementById('deleteError').style.display = 'flex';
                    setTimeout(function() {
                        document.getElementById('deleteError').style.display = 'none';
                    }, 5000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
    }
    
    window.onclick = function(e) {
        var deleteModal = document.getElementById('deleteModal');
        if (e.target == deleteModal) {
            deleteModal.style.display = 'none';
        }
    }
    
    if (window.location.hash === '#change-password') {
        var element = document.getElementById('change-password');
        if (element) {
            setTimeout(function() {
                element.scrollIntoView({ behavior: 'smooth' });
            }, 100);
        }
    }
</script>

<?php include 'includes/footer.php'; ?>