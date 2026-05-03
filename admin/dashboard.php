<?php
session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ../admin_login.php');
    exit();
}

require_once '../includes/config.php';

// Get statistics
$stmt = $pdo->query("SELECT COUNT(*) FROM tbluser");
$totalUsers = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM tbluser WHERE Role IS NOT NULL AND Role != ''");
$adminCount = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM tblmovie");
$totalMovies = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM tblwatchlist");
$watchlistCount = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM tblreview");
$reviewCount = $stmt->fetchColumn();

// Get ALL users for All Users page
$stmt = $pdo->query("SELECT UserID, FullName, Email, Role, IsActive, CreatedDate, LastLoginDate FROM tbluser ORDER BY CreatedDate DESC");
$allUsers = $stmt->fetchAll();

// Get REGULAR users only (no admins) for Users tab
$stmt = $pdo->query("SELECT UserID, FullName, Email, IsActive, CreatedDate, LastLoginDate FROM tbluser WHERE Role IS NULL OR Role = '' ORDER BY CreatedDate DESC");
$regularUsers = $stmt->fetchAll();

// Get all movies
$stmt = $pdo->query("SELECT MovieID, Title, Genre, ReleaseYear, TMDBRating FROM tblmovie ORDER BY Title");
$allMovies = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - MovieWatchlist</title>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:wght@400;700&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DM Sans', sans-serif; background: #14181c; color: #99aabb; }
        
        /* Navigation */
        .admin-nav { background: #1c2228; border-bottom: 1px solid #2c3440; padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; position: sticky; top: 0; z-index: 100; }
        .admin-nav h1 { color: #c41e3a; font-size: 1.3rem; display: flex; align-items: center; gap: 10px; }
        .admin-nav .admin-info { display: flex; align-items: center; gap: 20px; }
        .admin-nav .admin-info span { color: #ffffff; }
        .admin-nav .admin-info a { color: #c41e3a; text-decoration: none; padding: 8px 16px; border-radius: 30px; transition: all 0.2s; background: rgba(196,30,58,0.1); }
        .admin-nav .admin-info a:hover { background: #c41e3a; color: white; }
        
        /* Container */
        .dashboard-container { max-width: 1400px; margin: 0 auto; padding: 40px; }
        
        /* Stats Grid - No underline on links */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .stat-card { 
            background: #1c2228; 
            padding: 25px 20px; 
            border-radius: 16px; 
            border: 1px solid #2c3440; 
            text-align: center; 
            transition: all 0.2s; 
            text-decoration: none; 
            display: block; 
            cursor: pointer;
        }
        .stat-card:hover { transform: translateY(-3px); border-color: #c41e3a; background: #242c34; }
        .stat-card .stat-icon { font-size: 2rem; margin-bottom: 10px; }
        .stat-card .stat-number { font-size: 2rem; font-weight: 700; color: #00e054; font-family: 'Libre Baskerville', serif; }
        .stat-card .stat-label { color: #99aabb; margin-top: 8px; font-size: 0.85rem; }
        
        /* Tabs */
        .admin-tabs { display: flex; gap: 10px; margin-bottom: 30px; border-bottom: 1px solid #2c3440; padding-bottom: 15px; flex-wrap: wrap; }
        .tab-btn { background: transparent; border: none; padding: 10px 24px; font-size: 0.9rem; font-weight: 600; cursor: pointer; color: #99aabb; border-radius: 30px; transition: all 0.2s; }
        .tab-btn.active { background: #c41e3a; color: white; }
        .tab-btn:hover:not(.active) { background: rgba(196,30,58,0.2); color: white; }
        
        /* Tables */
        .admin-table { width: 100%; border-collapse: collapse; background: #1c2228; border-radius: 16px; overflow: hidden; }
        .admin-table th, .admin-table td { padding: 14px 16px; text-align: left; border-bottom: 1px solid #2c3440; }
        .admin-table th { background: #14181c; color: white; font-weight: 600; font-size: 0.85rem; }
        .admin-table tr:hover { background: #242c34; }
        
        /* Badges */
        .status-active { color: #00e054; font-weight: 600; }
        .status-inactive { color: #c41e3a; font-weight: 600; }
        
        /* Buttons */
        .action-btn { background: #2c3440; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 0.7rem; font-weight: 600; margin: 0 3px; transition: all 0.2s; }
        .action-btn.edit { color: #00c2ff; }
        .action-btn.edit:hover { background: #00c2ff; color: #14181c; }
        .action-btn.delete { color: #c41e3a; }
        .action-btn.delete:hover { background: #c41e3a; color: white; }
        .action-btn.freeze { color: #f5c518; }
        .action-btn.freeze:hover { background: #f5c518; color: #14181c; }
        .action-btn.activate { color: #00e054; }
        .action-btn.activate:hover { background: #00e054; color: #14181c; }
        
        /* Modal */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 2000; align-items: center; justify-content: center; }
        .modal-content { background: #1c2228; border-radius: 16px; padding: 30px; max-width: 500px; width: 90%; border: 1px solid #2c3440; }
        .modal-content h3 { color: white; margin-bottom: 20px; }
        .modal-content input, .modal-content select, .modal-content textarea { width: 100%; padding: 10px; margin-bottom: 15px; background: #2c3440; border: 1px solid #3a454d; border-radius: 8px; color: white; }
        .modal-buttons { display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px; }
        .modal-buttons button { padding: 8px 20px; border-radius: 30px; border: none; cursor: pointer; font-weight: 600; }
        .btn-save { background: #c41e3a; color: white; }
        .btn-cancel { background: #2c3440; color: white; }
        
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        
        @media (max-width: 768px) {
            .dashboard-container { padding: 20px; }
            .admin-table th, .admin-table td { padding: 8px 10px; font-size: 0.75rem; }
            .stats-grid { gap: 12px; }
            .stat-card { padding: 15px; }
        }
    </style>
</head>
<body>
    <div class="admin-nav">
        <h1>🔐 Admin Dashboard</h1>
        <div class="admin-info">
            <span>👤 <?php echo htmlspecialchars($_SESSION['admin_name']); ?> (<?php echo htmlspecialchars($_SESSION['admin_role']); ?>)</span>
            <a href="../logout_process.php">Logout</a>
        </div>
    </div>

    <div class="dashboard-container">
        <!-- Stats Grid - CLICKABLE CARDS (No underline) -->
        <div class="stats-grid">
            <a href="users.php" class="stat-card" style="text-decoration: none;">
                <div class="stat-icon">👥</div>
                <div class="stat-number"><?php echo $totalUsers; ?></div>
                <div class="stat-label">Total Users</div>
            </a>
            
            <a href="admins.php" class="stat-card" style="text-decoration: none;">
                <div class="stat-icon">🔐</div>
                <div class="stat-number"><?php echo $adminCount; ?></div>
                <div class="stat-label">Admin Users</div>
            </a>
            
            <a href="../movies/index.php" class="stat-card" style="text-decoration: none;">
                <div class="stat-icon">🎬</div>
                <div class="stat-number"><?php echo $totalMovies; ?></div>
                <div class="stat-label">Movies</div>
            </a>
            
            <a href="watchlist_items.php" class="stat-card" style="text-decoration: none;">
                <div class="stat-icon">📋</div>
                <div class="stat-number"><?php echo $watchlistCount; ?></div>
                <div class="stat-label">Watchlist Items</div>
            </a>
            
            <a href="reviews.php" class="stat-card" style="text-decoration: none;">
                <div class="stat-icon">⭐</div>
                <div class="stat-number"><?php echo $reviewCount; ?></div>
                <div class="stat-label">Reviews</div>
            </a>
        </div>

        <!-- Tabs -->
        <div class="admin-tabs">
            <button class="tab-btn active" data-tab="users">👥 User Management</button>
            <button class="tab-btn" data-tab="movies">🎬 Movie Management</button>
            <button class="tab-btn" data-tab="add-movie">➕ Add New Movie</button>
        </div>

        <!-- Users Tab - Regular Users Only (No Admins) with Last Login -->
        <div id="users-tab" class="tab-content active">
            <div style="overflow-x: auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Last Login</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($regularUsers as $user): ?>
                        <tr>
                            <td><?php echo $user['UserID']; ?></td>
                            <td><?php echo htmlspecialchars($user['FullName']); ?></td>
                            <td><?php echo htmlspecialchars($user['Email']); ?></td>
                            <td><?php echo $user['LastLoginDate'] ? date('M d, Y', strtotime($user['LastLoginDate'])) : 'Never'; ?></td>
                            <td class="<?php echo $user['IsActive'] ? 'status-active' : 'status-inactive'; ?>"><?php echo $user['IsActive'] ? 'Active' : 'Frozen'; ?></td>
                            <td><?php echo date('M d, Y', strtotime($user['CreatedDate'])); ?></td>
                            <td>
                                <button class="action-btn edit" onclick="editUser(<?php echo $user['UserID']; ?>)">✏️ Edit</button>
                                <?php if ($user['IsActive']): ?>
                                    <button class="action-btn freeze" onclick="freezeUser(<?php echo $user['UserID']; ?>)">❄️ Freeze</button>
                                <?php else: ?>
                                    <button class="action-btn activate" onclick="activateUser(<?php echo $user['UserID']; ?>)">▶️ Activate</button>
                                <?php endif; ?>
                                <button class="action-btn delete" onclick="deleteUser(<?php echo $user['UserID']; ?>)">🗑️ Delete</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Movies Tab -->
        <div id="movies-tab" class="tab-content">
            <div style="overflow-x: auto;">
                <table class="admin-table">
                    <thead>
                        <tr><th>ID</th><th>Title</th><th>Genre</th><th>Year</th><th>Rating</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allMovies as $movie): ?>
                        <tr>
                            <td><?php echo $movie['MovieID']; ?></td>
                            <td><?php echo htmlspecialchars($movie['Title']); ?></td>
                            <td><?php echo htmlspecialchars($movie['Genre']); ?></td>
                            <td><?php echo $movie['ReleaseYear']; ?></td>
                            <td>⭐ <?php echo $movie['TMDBRating']; ?>/10</td>
                            <td>
                                <button class="action-btn edit" onclick="editMovie(<?php echo $movie['MovieID']; ?>)">✏️ Edit</button>
                                <button class="action-btn delete" onclick="deleteMovie(<?php echo $movie['MovieID']; ?>)">🗑️ Delete</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add Movie Tab -->
        <div id="add-movie-tab" class="tab-content">
            <div class="admin-card" style="background: #1c2228; padding: 30px; border-radius: 16px;">
                <h3 style="color: white; margin-bottom: 20px;">Add New Movie</h3>
                <form id="addMovieForm">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <input type="text" name="title" placeholder="Movie Title" required>
                        <input type="text" name="genre" placeholder="Genre" required>
                        <input type="number" name="release_year" placeholder="Release Year" required>
                        <input type="text" name="poster_url" placeholder="Poster URL (optional)">
                        <input type="text" name="tmdb_rating" placeholder="TMDB Rating (0-10)" step="0.1">
                        <textarea name="description" placeholder="Movie Description" rows="3" style="grid-column: span 2;"></textarea>
                    </div>
                    <button type="submit" class="action-btn activate" style="margin-top: 20px; padding: 10px 20px;">➕ Add Movie</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editUserModal" class="modal">
        <div class="modal-content">
            <h3>Edit User</h3>
            <form id="editUserForm">
                <input type="hidden" name="user_id" id="edit_user_id">
                <input type="text" name="fullname" id="edit_fullname" placeholder="Full Name" required>
                <input type="email" name="email" id="edit_email" placeholder="Email" required>
                <select name="role" id="edit_role">
                    <option value="">User</option>
                    <option value="admin">Admin</option>
                    <option value="staff">Staff</option>
                </select>
                <div class="modal-buttons">
                    <button type="submit" class="btn-save">Save Changes</button>
                    <button type="button" class="btn-cancel" onclick="closeModal('editUserModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Movie Modal -->
    <div id="editMovieModal" class="modal">
        <div class="modal-content">
            <h3>Edit Movie</h3>
            <form id="editMovieForm">
                <input type="hidden" name="movie_id" id="edit_movie_id">
                <input type="text" name="title" id="edit_title" placeholder="Movie Title" required>
                <input type="text" name="genre" id="edit_genre" placeholder="Genre" required>
                <input type="number" name="release_year" id="edit_release_year" placeholder="Release Year" required>
                <input type="text" name="poster_url" id="edit_poster_url" placeholder="Poster URL">
                <input type="text" name="tmdb_rating" id="edit_tmdb_rating" placeholder="TMDB Rating" step="0.1">
                <textarea name="description" id="edit_description" placeholder="Description" rows="3"></textarea>
                <div class="modal-buttons">
                    <button type="submit" class="btn-save">Save Changes</button>
                    <button type="button" class="btn-cancel" onclick="closeModal('editMovieModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Tab switching
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.onclick = () => {
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                const tab = btn.getAttribute('data-tab');
                document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
                document.getElementById(`${tab}-tab`).classList.add('active');
            };
        });

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Edit User
        function editUser(id) {
            fetch(`admin_ajax.php?action=get_user&id=${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit_user_id').value = data.id;
                    document.getElementById('edit_fullname').value = data.fullname;
                    document.getElementById('edit_email').value = data.email;
                    document.getElementById('edit_role').value = data.role || '';
                    document.getElementById('editUserModal').style.display = 'flex';
                });
        }

        document.getElementById('editUserForm').onsubmit = function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'update_user');
            fetch('admin_ajax.php', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.success) location.reload();
                    else alert(data.error);
                });
        };

        // Freeze User
        function freezeUser(id) {
            if (confirm('Freeze this user? They will not be able to login.')) {
                fetch('admin_ajax.php', { method: 'POST', body: new URLSearchParams({ action: 'freeze_user', id: id }) })
                    .then(response => response.json())
                    .then(data => { if (data.success) location.reload(); });
            }
        }

        // Activate User
        function activateUser(id) {
            fetch('admin_ajax.php', { method: 'POST', body: new URLSearchParams({ action: 'activate_user', id: id }) })
                .then(response => response.json())
                .then(data => { if (data.success) location.reload(); });
        }

        // Delete User
        function deleteUser(id) {
            if (confirm('⚠️ Delete this user? This action cannot be undone!')) {
                fetch('admin_ajax.php', { method: 'POST', body: new URLSearchParams({ action: 'delete_user', id: id }) })
                    .then(response => response.json())
                    .then(data => { if (data.success) location.reload(); });
            }
        }

        // Edit Movie
        function editMovie(id) {
            fetch(`admin_ajax.php?action=get_movie&id=${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit_movie_id').value = data.id;
                    document.getElementById('edit_title').value = data.title;
                    document.getElementById('edit_genre').value = data.genre;
                    document.getElementById('edit_release_year').value = data.release_year;
                    document.getElementById('edit_poster_url').value = data.poster_url || '';
                    document.getElementById('edit_tmdb_rating').value = data.tmdb_rating;
                    document.getElementById('edit_description').value = data.description || '';
                    document.getElementById('editMovieModal').style.display = 'flex';
                });
        }

        document.getElementById('editMovieForm').onsubmit = function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'update_movie');
            fetch('admin_ajax.php', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.success) location.reload();
                    else alert(data.error);
                });
        };

        // Delete Movie
        function deleteMovie(id) {
            if (confirm('Delete this movie? This will remove it from all watchlists.')) {
                fetch('admin_ajax.php', { method: 'POST', body: new URLSearchParams({ action: 'delete_movie', id: id }) })
                    .then(response => response.json())
                    .then(data => { if (data.success) location.reload(); });
            }
        }

        // Add Movie
        document.getElementById('addMovieForm').onsubmit = function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'add_movie');
            fetch('admin_ajax.php', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.success) location.reload();
                    else alert(data.error);
                });
        };
    </script>
</body>
</html>