<?php
require_once 'includes/config.php';
include 'includes/header.php';

// Get all regular users (exclude admins)
$stmt = $pdo->prepare("SELECT UserID, FullName, Email, CreatedDate FROM tbluser WHERE Role IS NULL OR Role = '' ORDER BY FullName");
$stmt->execute();
$members = $stmt->fetchAll();

// Check if current user is admin
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
?>

<div class="members-container">
    <h1>Members</h1>
    <p class="members-count"><?php echo count($members); ?> film lovers</p>
    
    <div class="members-grid">
        <?php foreach ($members as $member): ?>
        <div class="member-card">
            <div class="member-avatar">👤</div>
            <div class="member-info">
                <h3><?php echo htmlspecialchars($member['FullName']); ?></h3>
                
                <?php if ($isAdmin): ?>
                    <!-- Admin view: shows email -->
                    <p class="member-email"><?php echo htmlspecialchars($member['Email']); ?></p>
                <?php endif; ?>
                
                <p class="member-since">Member since <?php echo date('M Y', strtotime($member['CreatedDate'])); ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <?php if ($isAdmin): ?>
        <div class="admin-note">
            <p>🔐 You are viewing as Admin. Emails are visible.</p>
        </div>
    <?php endif; ?>
</div>

<style>
.members-container {
    max-width: 1000px;
    margin: 100px auto 60px;
    padding: 0 24px;
}

.members-container h1 {
    font-size: 2rem;
    margin-bottom: 10px;
}

.members-count {
    color: #99aabb;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #2c3440;
}

.members-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.member-card {
    display: flex;
    gap: 15px;
    background: #1c2228;
    padding: 20px;
    border-radius: 12px;
    transition: transform 0.2s;
    border: 1px solid #2c3440;
}

.member-card:hover {
    transform: translateY(-3px);
    border-color: #c41e3a;
}

.member-avatar {
    width: 60px;
    height: 60px;
    background: #2c3440;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
}

.member-info {
    flex: 1;
}

.member-info h3 {
    font-size: 1.1rem;
    margin-bottom: 5px;
}

.member-email {
    color: #99aabb;
    font-size: 0.8rem;
    margin-bottom: 5px;
}

.member-since {
    color: #556677;
    font-size: 0.7rem;
}

.admin-note {
    margin-top: 30px;
    padding: 15px;
    background: rgba(196, 30, 58, 0.1);
    border: 1px solid #c41e3a;
    border-radius: 8px;
    text-align: center;
    color: #c41e3a;
    font-size: 0.85rem;
}

@media (max-width: 768px) {
    .members-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include 'includes/footer.php'; ?>