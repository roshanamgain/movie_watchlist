<?php
session_start();
require_once '../includes/config.php';

header('Content-Type: application/json');

// Only logged-in users can add movies
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Please login to add movies']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $genre = trim($_POST['genre']);
    $release_year = (int)$_POST['release_year'];
    $poster_url = !empty($_POST['poster_url']) ? $_POST['poster_url'] : null;
    $tmdb_rating = !empty($_POST['tmdb_rating']) ? (float)$_POST['tmdb_rating'] : null;
    $description = !empty($_POST['description']) ? $_POST['description'] : null;
    
    // Validation
    if (empty($title) || empty($genre) || empty($release_year)) {
        echo json_encode(['success' => false, 'error' => 'Title, Genre, and Release Year are required']);
        exit();
    }
    
    // Check if movie already exists
    $stmt = $pdo->prepare("SELECT * FROM tblmovie WHERE Title = ?");
    $stmt->execute([$title]);
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'error' => 'Movie already exists in the catalog']);
        exit();
    }
    
    // Add movie to database
    $stmt = $pdo->prepare("INSERT INTO tblmovie (Title, Genre, ReleaseYear, PosterURL, TMDBRating, Description) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$title, $genre, $release_year, $poster_url, $tmdb_rating, $description])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Database error']);
    }
    exit();
}
?>