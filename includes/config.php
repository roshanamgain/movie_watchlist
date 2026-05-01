<?php
// Database settings
define('DB_HOST', 'localhost');
define('DB_NAME', 'movie_watchlist');  // Your database name
define('DB_USER', 'root');
define('DB_PASS', '');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Helper functions
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function formatNumber($num) {
    if ($num >= 1000000) {
        return round($num / 1000000, 2) . 'M';
    } elseif ($num >= 1000) {
        return round($num / 1000, 2) . 'K';
    }
    return $num;
}

function renderStars($rating) {
    if (!$rating) return '☆☆☆☆☆';
    $fullStars = floor($rating / 2);
    $html = '';
    for ($i = 0; $i < $fullStars; $i++) {
        $html .= '★';
    }
    for ($i = $fullStars; $i < 5; $i++) {
        $html .= '☆';
    }
    return $html;
}
?>