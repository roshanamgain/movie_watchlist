<?php
session_start();
require_once '../includes/config.php';

// Optional: restrict to admin only - uncomment and set your admin user ID
// if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 1) {
//     die("Admin access only.");
// }

// List of 10 movies (mapped to your tblmovie columns)
$moviesToAdd = [
    [
        'Title' => 'Dune: Part Two',
        'Genre' => 'Sci-Fi',
        'ReleaseYear' => 2024,
        'PosterURL' => 'https://via.placeholder.com/200x280?text=Dune+Part+Two',
        'TMDBRating' => 8.5,
        'Description' => 'Paul Atreides unites with Chani and the Fremen while seeking revenge against the conspirators who destroyed his family.'
    ],
    [
        'Title' => 'Oppenheimer',
        'Genre' => 'Biography',
        'ReleaseYear' => 2023,
        'PosterURL' => 'https://via.placeholder.com/200x280?text=Oppenheimer',
        'TMDBRating' => 8.4,
        'Description' => 'The story of American scientist J. Robert Oppenheimer and his role in the development of the atomic bomb.'
    ],
    [
        'Title' => 'Barbie',
        'Genre' => 'Comedy',
        'ReleaseYear' => 2023,
        'PosterURL' => 'https://via.placeholder.com/200x280?text=Barbie',
        'TMDBRating' => 7.2,
        'Description' => 'Barbie and Ken discover the joys and perils of living in the real world.'
    ],
    [
        'Title' => 'The Batman',
        'Genre' => 'Action',
        'ReleaseYear' => 2022,
        'PosterURL' => 'https://via.placeholder.com/200x280?text=The+Batman',
        'TMDBRating' => 7.8,
        'Description' => 'When the Riddler targets Gotham\'s elite, Batman uncovers corruption that leads him to his family\'s dark secrets.'
    ],
    [
        'Title' => 'Top Gun: Maverick',
        'Genre' => 'Action',
        'ReleaseYear' => 2022,
        'PosterURL' => 'https://via.placeholder.com/200x280?text=Top+Gun+Maverick',
        'TMDBRating' => 8.2,
        'Description' => 'Maverick confronts his past while training a new generation of Top Gun graduates for a dangerous mission.'
    ],
    [
        'Title' => 'Spider-Man: Across the Spider-Verse',
        'Genre' => 'Animation',
        'ReleaseYear' => 2023,
        'PosterURL' => 'https://via.placeholder.com/200x280?text=Spider-Man+Across+the+Spider-Verse',
        'TMDBRating' => 8.6,
        'Description' => 'Miles Morales encounters a team of Spider-People across dimensions and must redefine what it means to be a hero.'
    ],
    [
        'Title' => 'John Wick: Chapter 4',
        'Genre' => 'Action',
        'ReleaseYear' => 2023,
        'PosterURL' => 'https://via.placeholder.com/200x280?text=John+Wick+Chapter+4',
        'TMDBRating' => 7.9,
        'Description' => 'John Wick discovers a path to defeating the High Table, but before he can earn his freedom, he must face a new enemy.'
    ],
    [
        'Title' => 'Killers of the Flower Moon',
        'Genre' => 'Crime',
        'ReleaseYear' => 2023,
        'PosterURL' => 'https://via.placeholder.com/200x280?text=Killers+of+the+Flower+Moon',
        'TMDBRating' => 8.0,
        'Description' => 'Members of the Osage tribe are murdered under mysterious circumstances in 1920s Oklahoma, sparking an FBI investigation.'
    ],
    [
        'Title' => 'Poor Things',
        'Genre' => 'Comedy',
        'ReleaseYear' => 2023,
        'PosterURL' => 'https://via.placeholder.com/200x280?text=Poor+Things',
        'TMDBRating' => 7.9,
        'Description' => 'The incredible story of Bella Baxter, a young woman brought back to life by an unorthodox scientist.'
    ],
    [
        'Title' => 'The Holdovers',
        'Genre' => 'Drama',
        'ReleaseYear' => 2023,
        'PosterURL' => 'https://via.placeholder.com/200x280?text=The+Holdovers',
        'TMDBRating' => 7.8,
        'Description' => 'A strict teacher at a New England prep school forms an unlikely bond with a troubled student over the Christmas holidays.'
    ]
];

// Begin transaction for atomic inserts
$pdo->beginTransaction();

try {
    $checkStmt = $pdo->prepare("SELECT id FROM tblmovie WHERE Title = ?");
    $insertStmt = $pdo->prepare("INSERT INTO tblmovie (Title, Genre, ReleaseYear, PosterURL, TMDBRating, Description) VALUES (?, ?, ?, ?, ?, ?)");
    
    $inserted = 0;
    $skipped = 0;
    
    foreach ($moviesToAdd as $movie) {
        $checkStmt->execute([$movie['Title']]);
        if ($checkStmt->fetch()) {
            echo "⏭️ Skipped (already exists): " . htmlspecialchars($movie['Title']) . "<br>";
            $skipped++;
        } else {
            $insertStmt->execute([
                $movie['Title'],
                $movie['Genre'],
                $movie['ReleaseYear'],
                $movie['PosterURL'],
                $movie['TMDBRating'],
                $movie['Description']
            ]);
            echo "✅ Added: " . htmlspecialchars($movie['Title']) . "<br>";
            $inserted++;
        }
    }
    
    // Commit all inserts
    $pdo->commit();
    echo "<hr>";
    echo "<strong>Summary:</strong> $inserted movies added, $skipped skipped.<br>";
    echo '<a href="index.php">Go to Movie Library</a>';
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo "<strong>Error:</strong> " . $e->getMessage() . "<br>";
    echo "Transaction rolled back. No movies were added.";
}
?>