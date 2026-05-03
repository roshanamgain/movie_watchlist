<?php
session_start();

// Check if confirmation is received
if (isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
    session_destroy();
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - Movie Watchlist</title>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:wght@400;700&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DM Sans', sans-serif;
            background: #14181c;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .logout-container {
            background: #1c2228;
            padding: 40px;
            border-radius: 16px;
            text-align: center;
            max-width: 400px;
            width: 90%;
        }
        .logout-container h2 {
            color: #ffffff;
            margin-bottom: 15px;
        }
        .logout-container p {
            color: #99aabb;
            margin-bottom: 25px;
        }
        .logout-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
        }
        .btn-yes {
            background: #c41e3a;
            color: white;
            padding: 10px 25px;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }
        .btn-no {
            background: #2c3440;
            color: white;
            padding: 10px 25px;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }
        .btn-yes:hover {
            background: #a01830;
        }
        .btn-no:hover {
            background: #3a454d;
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <h2>Logout Confirmation</h2>
        <p>Are you sure you want to logout?</p>
        <div class="logout-buttons">
            <a href="logout.php?confirm=yes" class="btn-yes">Yes, Logout</a>
            <a href="javascript:history.back()" class="btn-no">Cancel</a>
        </div>
    </div>
</body>
</html>