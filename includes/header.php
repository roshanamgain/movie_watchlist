<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movie Watchlist</title>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:wght@400;700&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/movie_watchlist/css/style.css">
</head>
<body>
    <div class="navbar">
        <div style="display: flex; align-items: center; gap: 10px;">
            <img src="/movie_watchlist/images/logo.jpg" alt="Logo" style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover;">
            <a href="/movie_watchlist/index.php" class="logo">MovieWatchlist</a>
        </div>
        <div class="nav-links">
            <a href="/movie_watchlist/index.php">HOME</a>
            <a href="/movie_watchlist/movies/index.php">MOVIES</a>
            <a href="/movie_watchlist/watchlist/index.php">WATCHLIST</a>
            <a href="/movie_watchlist/members.php">MEMBERS</a>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="user-dropdown">
                    <span class="user-name" onclick="toggleDropdown()">👤 <?php echo htmlspecialchars($_SESSION['fullname']); ?> ▼</span>
                    <div class="dropdown-content" id="userDropdown">
                        <a href="/movie_watchlist/profile.php">📋 My Profile</a>
                        <a href="/movie_watchlist/settings.php">⚙️ Settings</a>
                        <a href="#" onclick="openLogoutModal(); return false;">🚪 Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="#" class="btn-create" id="showLoginBtn">SIGN IN</a>
                <a href="#" class="btn-create" id="showSignupBtn">CREATE ACCOUNT</a>
            <?php endif; ?>
            
            <form class="search-form" action="/movie_watchlist/movies/search.php" method="GET" style="display: inline-block; margin-left: 15px;">
                <input type="text" name="q" placeholder="Search movies..." class="search-input" style="background: #2c3440; border: none; border-radius: 20px; padding: 8px 16px; color: white; width: 160px;">
            </form>
        </div>
    </div>

    <!-- Login Modal with Dropdown -->
    <div id="loginModal" class="modal">
        <div class="modal-content login-modal">
            <span class="close-modal" id="closeLogin">&times;</span>
            <h2 class="modal-title">Sign In</h2>
            
            <!-- Login Type Dropdown -->
            <div class="login-type-dropdown">
                <div class="dropdown-selected" id="loginTypeSelected">
                    <span id="selectedTypeText">👤 User Login</span>
                    <span class="dropdown-arrow">▼</span>
                </div>
                <div class="dropdown-options" id="loginTypeOptions">
                    <div class="dropdown-option" data-type="user">
                        <span>👤</span> User Login
                    </div>
                    <div class="dropdown-option" data-type="admin">
                        <span>🔐</span> Admin Login
                    </div>
                </div>
            </div>
            
            <!-- User Login Form -->
            <div id="userLoginForm" class="login-form-content">
                <div id="loginError" class="modal-error" style="display: none;">
                    <span class="error-text" id="loginErrorText"></span>
                    <button class="error-close" onclick="document.getElementById('loginError').style.display='none';">&times;</button>
                </div>
                
                <form id="loginForm" method="POST" action="/movie_watchlist/login_process.php">
                    <input type="email" name="email" placeholder="Email address" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <div class="modal-options">
                        <label class="checkbox-label">
                            <input type="checkbox" name="remember"> Remember me
                        </label>
                        <a href="#" id="showForgotPasswordBtn">Forgotten?</a>
                    </div>
                    <button type="submit" class="modal-btn">SIGN IN</button>
                </form>
            </div>
            
            <!-- Admin Login Form -->
            <div id="adminLoginForm" class="login-form-content" style="display: none;">
                <div id="adminLoginError" class="modal-error" style="display: none;">
                    <span class="error-text" id="adminLoginErrorText"></span>
                    <button class="error-close" onclick="document.getElementById('adminLoginError').style.display='none';">&times;</button>
                </div>
                
                <form id="adminLoginFormSubmit" method="POST" action="admin_login_process.php">
                    <input type="email" name="email" placeholder="Admin Email" required>
                    <input type="password" name="password" placeholder="Admin Password" required>
                    <div class="modal-options">
                        <label class="checkbox-label">
                            <input type="checkbox" name="remember"> Remember me
                        </label>
                    </div>
                    <button type="submit" class="modal-btn">ADMIN SIGN IN</button>
                </form>
            </div>
            
            <!-- Footer -->
            <div class="modal-footer" id="loginModalFooter">
                Don't have an account? <a href="#" id="switchToSignup">Join MovieWatchlist</a>
            </div>
        </div>
    </div>

    <!-- Signup Modal -->
    <div id="signupModal" class="modal">
        <div class="modal-content signup-modal">
            <span class="close-modal" id="closeSignup">&times;</span>
            <h2 class="modal-title">Join MovieWatchlist</h2>
            
            <div id="signupError" class="modal-error" style="display: none;">
                <span class="error-text" id="signupErrorText"></span>
                <button class="error-close" onclick="document.getElementById('signupError').style.display='none';">&times;</button>
            </div>
            
            <form id="signupForm" method="POST" action="/movie_watchlist/register_process.php">
                <input type="text" name="fullname" placeholder="Full name" required>
                <input type="email" name="email" placeholder="Email address" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="password" name="confirm_password" placeholder="Confirm password" required>
                
                <div class="terms-group">
                    <label class="checkbox-label">
                        <input type="checkbox" required> I accept the 
                        <a href="#">Terms of Use</a> and <a href="#">Privacy Policy</a>
                    </label>
                </div>
                
                <button type="submit" class="modal-btn">SIGN UP</button>
            </form>
            <div class="modal-footer">
                Already have an account? <a href="#" id="switchToLoginFromSignup">Sign In</a>
            </div>
        </div>
    </div>

    <!-- Forgot Password Modal -->
    <div id="forgotPasswordModal" class="modal">
        <div class="modal-content forgot-modal">
            <span class="close-modal" id="closeForgotPassword">&times;</span>
            <h2 class="modal-title">Reset Password</h2>
            <p class="forgot-message">Enter your email address to receive a password reset link.</p>
            
            <div id="forgotError" class="modal-error" style="display: none;">
                <span class="error-text" id="forgotErrorText"></span>
                <button class="error-close" onclick="document.getElementById('forgotError').style.display='none';">&times;</button>
            </div>
            
            <div id="forgotSuccess" class="modal-success" style="display: none;">
                <span class="error-text" id="forgotSuccessText"></span>
                <button class="error-close" onclick="document.getElementById('forgotSuccess').style.display='none';">&times;</button>
            </div>
            
            <form id="forgotPasswordForm">
                <div class="form-group">
                    <input type="email" id="resetEmail" placeholder="Email address" required>
                </div>
                <button type="submit" class="modal-btn">Send Reset Link</button>
            </form>
            <div class="modal-footer">
                <a href="#" id="backToLoginFromForgot">← Back to Sign In</a>
            </div>
        </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div id="logoutModal" class="modal">
        <div class="modal-content logout-modal">
            <span class="close-modal" id="closeLogout">&times;</span>
            <h2 class="modal-title">Are you sure you want to logout?</h2>
            <div class="logout-buttons">
                <button class="btn-logout-yes" onclick="confirmLogout()">Yes, Logout</button>
                <button class="btn-logout-no" id="cancelLogout">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        // Toggle dropdown
        function toggleDropdown() {
            var dropdown = document.getElementById('userDropdown');
            if (dropdown.style.display === 'block') {
                dropdown.style.display = 'none';
            } else {
                dropdown.style.display = 'block';
            }
        }

        // Logout functions
        function openLogoutModal() {
            document.getElementById('logoutModal').style.display = 'flex';
        }

        function closeLogoutModal() {
            document.getElementById('logoutModal').style.display = 'none';
        }

        function confirmLogout() {
            window.location.href = '/movie_watchlist/logout_process.php';
        }

        if (document.getElementById('closeLogout')) {
            document.getElementById('closeLogout').onclick = closeLogoutModal;
        }
        if (document.getElementById('cancelLogout')) {
            document.getElementById('cancelLogout').onclick = closeLogoutModal;
        }

        // Modal elements
        var loginModal = document.getElementById('loginModal');
        var signupModal = document.getElementById('signupModal');
        var forgotPasswordModal = document.getElementById('forgotPasswordModal');

        // Open login modal
        if (document.getElementById('showLoginBtn')) {
            document.getElementById('showLoginBtn').onclick = function(e) {
                e.preventDefault();
                document.getElementById('selectedTypeText').innerHTML = '👤 User Login';
                document.getElementById('userLoginForm').style.display = 'block';
                document.getElementById('adminLoginForm').style.display = 'none';
                document.getElementById('loginModalFooter').style.display = 'block';
                loginModal.style.display = 'flex';
            }
        }

        // Open signup modal
        if (document.getElementById('showSignupBtn')) {
            document.getElementById('showSignupBtn').onclick = function(e) {
                e.preventDefault();
                signupModal.style.display = 'flex';
            }
        }

        // Close modals
        if (document.getElementById('closeLogin')) {
            document.getElementById('closeLogin').onclick = function() { loginModal.style.display = 'none'; }
        }
        if (document.getElementById('closeSignup')) {
            document.getElementById('closeSignup').onclick = function() { signupModal.style.display = 'none'; }
        }
        if (document.getElementById('closeForgotPassword')) {
            document.getElementById('closeForgotPassword').onclick = function() { forgotPasswordModal.style.display = 'none'; }
        }

        // Switch between modals
        if (document.getElementById('switchToSignup')) {
            document.getElementById('switchToSignup').onclick = function(e) {
                e.preventDefault();
                loginModal.style.display = 'none';
                signupModal.style.display = 'flex';
            }
        }
        if (document.getElementById('switchToLoginFromSignup')) {
            document.getElementById('switchToLoginFromSignup').onclick = function(e) {
                e.preventDefault();
                signupModal.style.display = 'none';
                loginModal.style.display = 'flex';
            }
        }

        // Forgot Password triggers
        if (document.getElementById('showForgotPasswordBtn')) {
            document.getElementById('showForgotPasswordBtn').onclick = function(e) {
                e.preventDefault();
                loginModal.style.display = 'none';
                forgotPasswordModal.style.display = 'flex';
            }
        }
        if (document.getElementById('backToLoginFromForgot')) {
            document.getElementById('backToLoginFromForgot').onclick = function(e) {
                e.preventDefault();
                forgotPasswordModal.style.display = 'none';
                loginModal.style.display = 'flex';
            }
        }

        // ============================================
        // LOGIN TYPE DROPDOWN FUNCTIONALITY
        // ============================================
        const dropdownSelected = document.getElementById('loginTypeSelected');
        const dropdownOptions = document.getElementById('loginTypeOptions');
        const userLoginFormDiv = document.getElementById('userLoginForm');
        const adminLoginFormDiv = document.getElementById('adminLoginForm');
        const selectedTypeText = document.getElementById('selectedTypeText');
        const loginModalFooter = document.getElementById('loginModalFooter');

        if (dropdownSelected) {
            dropdownSelected.onclick = function(e) {
                e.stopPropagation();
                dropdownOptions.classList.toggle('show');
                const arrow = document.querySelector('.dropdown-arrow');
                if (dropdownOptions.classList.contains('show')) {
                    arrow.style.transform = 'rotate(180deg)';
                } else {
                    arrow.style.transform = 'rotate(0deg)';
                }
            }
        }

        // Handle dropdown option selection
        const dropdownOptionsList = document.querySelectorAll('.dropdown-option');
        dropdownOptionsList.forEach(option => {
            option.onclick = function(e) {
                e.stopPropagation();
                const type = this.getAttribute('data-type');
                
                if (type === 'user') {
                    selectedTypeText.innerHTML = '👤 User Login';
                    userLoginFormDiv.style.display = 'block';
                    adminLoginFormDiv.style.display = 'none';
                    if (loginModalFooter) loginModalFooter.style.display = 'block';
                    const adminError = document.getElementById('adminLoginError');
                    if (adminError) adminError.style.display = 'none';
                } else if (type === 'admin') {
                    selectedTypeText.innerHTML = '🔐 Admin Login';
                    userLoginFormDiv.style.display = 'none';
                    adminLoginFormDiv.style.display = 'block';
                    if (loginModalFooter) loginModalFooter.style.display = 'none';
                    const loginError = document.getElementById('loginError');
                    if (loginError) loginError.style.display = 'none';
                }
                
                dropdownOptions.classList.remove('show');
                const arrow = document.querySelector('.dropdown-arrow');
                if (arrow) arrow.style.transform = 'rotate(0deg)';
            }
        });

        // Close dropdown when clicking outside
        window.onclick = function(e) {
            if (!e.target.closest('.login-type-dropdown')) {
                if (dropdownOptions) dropdownOptions.classList.remove('show');
                const arrow = document.querySelector('.dropdown-arrow');
                if (arrow) arrow.style.transform = 'rotate(0deg)';
            }
            if (e.target == loginModal) loginModal.style.display = 'none';
            if (e.target == signupModal) signupModal.style.display = 'none';
            if (e.target == forgotPasswordModal) forgotPasswordModal.style.display = 'none';
            if (e.target == document.getElementById('logoutModal')) document.getElementById('logoutModal').style.display = 'none';
        }

        // ============================================
        // FORM SUBMISSION HANDLERS
        // ============================================
        
        function showError(errorElement, errorTextElement, message) {
            errorTextElement.textContent = message;
            errorElement.style.display = 'flex';
            setTimeout(function() { errorElement.style.display = 'none'; }, 5000);
        }

        // Handle User Login
        var loginForm = document.getElementById('loginForm');
        if (loginForm) {
            loginForm.onsubmit = function(e) {
                e.preventDefault();
                var formData = new FormData(loginForm);
                fetch('/movie_watchlist/login_process.php', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = '/movie_watchlist/index.php';
                    } else {
                        showError(document.getElementById('loginError'), document.getElementById('loginErrorText'), data.error);
                    }
                })
                .catch(error => console.error('Error:', error));
            }
        }

        // Handle Admin Login
var adminLoginFormSubmit = document.getElementById('adminLoginFormSubmit');
if (adminLoginFormSubmit) {
    adminLoginFormSubmit.onsubmit = function(e) {
        e.preventDefault();
        
        var formData = new FormData(adminLoginFormSubmit);
        
        // CORRECTED URL - absolute path
        fetch('/movie_watchlist/admin_login_process.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            console.log('Admin response:', data);
            if (data.success === true) {
                window.location.href = '/movie_watchlist/admin/dashboard.php';
            } else {
                var adminError = document.getElementById('adminLoginError');
                var adminErrorText = document.getElementById('adminLoginErrorText');
                adminErrorText.textContent = data.error || 'Login failed';
                adminError.style.display = 'flex';
                setTimeout(function() {
                    adminError.style.display = 'none';
                }, 5000);
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            var adminError = document.getElementById('adminLoginError');
            var adminErrorText = document.getElementById('adminLoginErrorText');
            adminErrorText.textContent = 'Connection error. Please try again.';
            adminError.style.display = 'flex';
        });
    }
}

        // Handle Signup
        var signupForm = document.getElementById('signupForm');
        if (signupForm) {
            signupForm.onsubmit = function(e) {
                e.preventDefault();
                var formData = new FormData(signupForm);
                fetch('/movie_watchlist/register_process.php', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = '/movie_watchlist/index.php';
                    } else {
                        showError(document.getElementById('signupError'), document.getElementById('signupErrorText'), data.error);
                    }
                })
                .catch(error => console.error('Error:', error));
            }
        }

        // Handle Forgot Password
        var forgotPasswordForm = document.getElementById('forgotPasswordForm');
        if (forgotPasswordForm) {
            forgotPasswordForm.onsubmit = function(e) {
                e.preventDefault();
                var email = document.getElementById('resetEmail').value;
                fetch('/movie_watchlist/forgot_password_process.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'email=' + encodeURIComponent(email)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        var successDiv = document.getElementById('forgotSuccess');
                        document.getElementById('forgotSuccessText').innerHTML = data.message;
                        if (data.reset_link) {
                            document.getElementById('forgotSuccessText').innerHTML += '<br><a href="' + data.reset_link + '" target="_blank" style="color: #00e054;">Click here to reset password</a>';
                        }
                        successDiv.style.display = 'flex';
                        document.getElementById('forgotError').style.display = 'none';
                        document.getElementById('resetEmail').value = '';
                        setTimeout(function() {
                            successDiv.style.display = 'none';
                            forgotPasswordModal.style.display = 'none';
                            loginModal.style.display = 'flex';
                        }, 5000);
                    } else {
                        var errorDiv = document.getElementById('forgotError');
                        document.getElementById('forgotErrorText').textContent = data.message;
                        errorDiv.style.display = 'flex';
                        setTimeout(function() { errorDiv.style.display = 'none'; }, 5000);
                    }
                })
                .catch(error => console.error('Error:', error));
            }
        }
    </script>