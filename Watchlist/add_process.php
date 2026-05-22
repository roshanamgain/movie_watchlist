<?php
/**
 * Process Add to Watchlist (AJAX/Form Handler)
 * Author: Bishnu - Watchlist Component
 * Date: May 2026
 */

session_start();
require_once '../includes/config.php';
require_once '../includes/validation.php';

// Check if user is logged in (FIXED: was checking wrong condition)
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Please login first']);
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $movieId = validateMovieId($_POST['movie_id'] ?? 0);
    $status = validateStatus($_POST['status'] ?? 'To Watch');
    $priority = validatePriority($_POST['priority'] ?? 'Medium');
    $rating = !empty($_POST['rating']) ? validateRating($_POST['rating']) : null;
    $notes = sanitizeText($_POST['notes'] ?? '');
    
    if (!$movieId) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Invalid movie ID']);
        exit();
    }
    
    try {
        // Check if already in watchlist
        $check = $conn->prepare("SELECT WatchlistID FROM tblwatchlist WHERE UserID = :uid AND MovieID = :mid");
        $check->execute([':uid' => $user_id, ':mid' => $movieId]);
        
        if ($check->fetch()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Movie already in watchlist']);
            exit();
        }
        
        // Add to watchlist
        $stmt = $conn->prepare("
            INSERT INTO tblwatchlist (UserID, MovieID, Status, Priority, PersonalRating, PersonalNotes, AddedDate)
            VALUES (:uid, :mid, :status, :priority, :rating, :notes, CURDATE())
        ");
        
        $result = $stmt->execute([
            ':uid' => $user_id,
            ':mid' => $movieId,
            ':status' => $status,
            ':priority' => $priority,
            ':rating' => $rating,
            ':notes' => $notes
        ]);
        
        if ($result) {
            // Log activity
            logActivity($conn, $user_id, 'ADDED_TO_WATCHLIST', $movieId, "Added movie to watchlist");
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Movie added to watchlist']);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Database error']);
        }
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit();
}
?>