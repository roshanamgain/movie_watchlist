<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// GET USER
if ($action == 'get_user' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT UserID as id, FullName as fullname, Email as email, Role as role FROM tbluser WHERE UserID = ?");
    $stmt->execute([$_GET['id']]);
    echo json_encode($stmt->fetch());
    exit();
}

// UPDATE USER
if ($action == 'update_user') {
    $id = $_POST['user_id'];
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $role = $_POST['role'] ?: null;
    
    $stmt = $pdo->prepare("UPDATE tbluser SET FullName = ?, Email = ?, Role = ? WHERE UserID = ?");
    $stmt->execute([$fullname, $email, $role, $id]);
    echo json_encode(['success' => true]);
    exit();
}

// FREEZE USER
if ($action == 'freeze_user') {
    $stmt = $pdo->prepare("UPDATE tbluser SET IsActive = 0 WHERE UserID = ?");
    $stmt->execute([$_POST['id']]);
    echo json_encode(['success' => true]);
    exit();
}

// ACTIVATE USER
if ($action == 'activate_user') {
    $stmt = $pdo->prepare("UPDATE tbluser SET IsActive = 1 WHERE UserID = ?");
    $stmt->execute([$_POST['id']]);
    echo json_encode(['success' => true]);
    exit();
}

// DELETE USER
if ($action == 'delete_user') {
    $stmt = $pdo->prepare("DELETE FROM tbluser WHERE UserID = ?");
    $stmt->execute([$_POST['id']]);
    echo json_encode(['success' => true]);
    exit();
}

// GET MOVIE
if ($action == 'get_movie' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT MovieID as id, Title as title, Genre as genre, ReleaseYear as release_year, PosterURL as poster_url, TMDBRating as tmdb_rating, Description as description FROM tblmovie WHERE MovieID = ?");
    $stmt->execute([$_GET['id']]);
    echo json_encode($stmt->fetch());
    exit();
}

// UPDATE MOVIE
if ($action == 'update_movie') {
    $id = $_POST['movie_id'];
    $title = $_POST['title'];
    $genre = $_POST['genre'];
    $release_year = $_POST['release_year'];
    $poster_url = $_POST['poster_url'];
    $tmdb_rating = $_POST['tmdb_rating'];
    $description = $_POST['description'];
    
    $stmt = $pdo->prepare("UPDATE tblmovie SET Title = ?, Genre = ?, ReleaseYear = ?, PosterURL = ?, TMDBRating = ?, Description = ? WHERE MovieID = ?");
    $stmt->execute([$title, $genre, $release_year, $poster_url, $tmdb_rating, $description, $id]);
    echo json_encode(['success' => true]);
    exit();
}

// DELETE MOVIE
if ($action == 'delete_movie') {
    $stmt = $pdo->prepare("DELETE FROM tblmovie WHERE MovieID = ?");
    $stmt->execute([$_POST['id']]);
    echo json_encode(['success' => true]);
    exit();
}

// ADD MOVIE
if ($action == 'add_movie') {
    $title = $_POST['title'];
    $genre = $_POST['genre'];
    $release_year = $_POST['release_year'];
    $poster_url = $_POST['poster_url'];
    $tmdb_rating = $_POST['tmdb_rating'];
    $description = $_POST['description'];
    
    $stmt = $pdo->prepare("INSERT INTO tblmovie (Title, Genre, ReleaseYear, PosterURL, TMDBRating, Description) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$title, $genre, $release_year, $poster_url, $tmdb_rating, $description]);
    echo json_encode(['success' => true]);
    exit();
}

echo json_encode(['success' => false, 'error' => 'Invalid action']);
?>