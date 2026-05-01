<?php
require_once 'includes/config.php';

echo "Config loaded!<br>";

$stmt = $pdo->query("SELECT COUNT(*) as count FROM tblmovie");
$result = $stmt->fetch();

echo "Number of movies: " . $result['count'];
?>