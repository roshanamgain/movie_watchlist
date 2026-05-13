<?php
/**
 * Validation Functions for Movie Watchlist
 * Author: Bishnu
 */

function validateMovieId($id) {
    $id = filter_var($id, FILTER_VALIDATE_INT);
    if ($id === false || $id <= 0) {
        return false;
    }
    return $id;
}

function validateWatchlistId($id) {
    $id = filter_var($id, FILTER_VALIDATE_INT);
    if ($id === false || $id <= 0) {
        return false;
    }
    return $id;
}

function validateRating($rating) {
    $rating = filter_var($rating, FILTER_VALIDATE_INT);
    if ($rating === false || $rating < 1 || $rating > 10) {
        return false;
    }
    return $rating;
}

function validateStatus($status) {
    $allowed = ['To Watch', 'Watching Now', 'Watched'];
    if (in_array($status, $allowed)) {
        return $status;
    }
    return false;
}

function validatePriority($priority) {
    $allowed = ['High', 'Medium', 'Low'];
    if (in_array($priority, $allowed)) {
        return $priority;
    }
    return false;
}

function sanitizeText($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function logActivity($conn, $userId, $activityType, $movieId = null, $details = null) {
    try {
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
        // Silent fail
    }
}
?>