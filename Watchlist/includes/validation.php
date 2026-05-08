<?php
/**
 * Validation Functions for Movie Watchlist
 * Author: Bishnu - Watchlist Component
 * Date: May 2026
 */

/**
 * Validate Movie ID - ensures it's a positive integer
 * @param mixed $id - Input to validate
 * @return int|false - Validated ID or false
 */
function validateMovieId($id) {
    $id = filter_var($id, FILTER_VALIDATE_INT);
    if ($id === false || $id <= 0) {
        return false;
    }
    return $id;
}

/**
 * Validate Watchlist ID
 * @param mixed $id - Input to validate
 * @return int|false - Validated ID or false
 */
function validateWatchlistId($id) {
    return validateMovieId($id);
}

/**
 * Validate Rating (1-10)
 * @param mixed $rating - Input to validate
 * @return int|false - Validated rating or false
 */
function validateRating($rating) {
    $rating = filter_var($rating, FILTER_VALIDATE_INT);
    if ($rating === false || $rating < 1 || $rating > 10) {
        return false;
    }
    return $rating;
}

/**
 * Validate Watch Status
 * @param string $status - Status to validate
 * @return string|false - Validated status or false
 */
function validateStatus($status) {
    $allowed = ['To Watch', 'Watching Now', 'Watched'];
    if (in_array($status, $allowed)) {
        return $status;
    }
    return false;
}

/**
 * Validate Priority
 * @param string $priority - Priority to validate
 * @return string|false - Validated priority or false
 */
function validatePriority($priority) {
    $allowed = ['High', 'Medium', 'Low'];
    if (in_array($priority, $allowed)) {
        return $priority;
    }
    return false;
}

/**
 * Sanitize text input
 * @param string $input - Raw input
 * @return string - Sanitized string
 */
function sanitizeText($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Check if user is logged in
 * @return bool - True if logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Require login - redirects if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: ../login.php");
        exit();
    }
}

/**
 * Log user activity
 * @param PDO $conn - Database connection
 * @param int $userId - User ID
 * @param string $activityType - Type of activity
 * @param int|null $movieId - Movie ID (optional)
 * @param string|null $details - Additional details
 */
function logActivity($conn, $userId, $activityType, $movieId = null, $details = null) {
    try {
        // Check if activity table exists
        $checkTable = $conn->query("SHOW TABLES LIKE 'tbluser_activity'");
        if ($checkTable->rowCount() == 0) {
            return; // Table doesn't exist yet
        }
        
        $stmt = $conn->prepare("
            INSERT INTO tbluser_activity (UserID, ActivityType, MovieID, Details, Timestamp)
            VALUES (:uid, :type, :mid, :details, NOW())
        ");
        $stmt->execute([
            ':uid' => $userId,
            ':type' => $activityType,
            ':mid' => $movieId,
            ':details' => $details
        ]);
    } catch (PDOException $e) {
        // Silently fail - activity logging shouldn't break main functionality
        error_log("Activity logging failed: " . $e->getMessage());
    }
}
?>