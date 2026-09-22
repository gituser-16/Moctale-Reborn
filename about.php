<?php require 'config/db.php'; session_start(); ?>
<!DOCTYPE html>
<html>
<head>
    <title>About Us - Moctale Reborn</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="header">
        <h1><a href="index.php" style="text-decoration:none; color:inherit;">Moctale Reborn</a></h1>
        <a href="index.php" style="color:white;">Back to Home</a>
    </div>
    <div style="padding: 30px; max-width: 700px;">
        <h2>About Moctale Reborn</h2>
        <p style="color: var(--text-muted); line-height: 1.7;">
            Moctale Reborn is a movie discovery and review platform built as a university project, inspired by the concept of moctale.in. 
            It lets users browse trending and popular titles, search the catalog, save favorites to a personal watchlist, 
            and share their honest verdict through our signature Moctale Reborn Meter and Vibe Chart.
        </p>
        <p style="color: var(--text-muted); line-height: 1.7;">
            This project was built using PHP, PostgreSQL (via Supabase), and real movie data from The Movie Database (TMDB) API.
        </p>
    </div>
    <?php include 'includes/footer.php'; ?>
</body>
</html>